<?php

header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
|
| Keep the Turnstile secret OUTSIDE your public/frontend code.
| Ideally use environment variables.
|
*/

$turnstileSecret = getenv('TURNSTILE_SECRET');

if (!$turnstileSecret) {
    error_log('TURNSTILE_SECRET is not configured');

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server configuration error'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Read JSON body
|--------------------------------------------------------------------------
*/

$rawBody = file_get_contents('php://input');

if ($rawBody === false || strlen($rawBody) > 20000) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit;
}

$data = json_decode($rawBody, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON'
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Get values
|--------------------------------------------------------------------------
*/

$name    = trim((string)($data['name'] ?? ''));
$email   = trim((string)($data['email'] ?? ''));
$phone   = trim((string)($data['phone'] ?? ''));
$subject = trim((string)($data['subject'] ?? ''));
$message = trim((string)($data['message'] ?? ''));
$service = $data['service'] ?? '';
$terms   = $data['terms'] ?? false;
$token   = trim((string)($data['token'] ?? ''));

/*
|--------------------------------------------------------------------------
| Basic validation
|--------------------------------------------------------------------------
*/

$errors = [];

// Name
if ($name === '') {
    $errors['name'] = 'Name is required';
} elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors['name'] = 'Name must be between 2 and 100 characters';
}

// Email
if ($email === '') {
    $errors['email'] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address';
} elseif (strlen($email) > 254) {
    $errors['email'] = 'Email is too long';
}

// Phone
if ($phone === '') {
    $errors['phone'] = 'Phone is required';
} elseif (strlen($phone) > 30) {
    $errors['phone'] = 'Phone number is too long';
} elseif (!preg_match('/^[0-9+\-\s().]+$/', $phone)) {
    $errors['phone'] = 'Invalid phone number';
}

// Subject
if ($subject === '') {
    $errors['subject'] = 'Subject is required';
} elseif (mb_strlen($subject) > 200) {
    $errors['subject'] = 'Subject is too long';
}

// Message
if ($message === '') {
    $errors['message'] = 'Message is required';
} elseif (mb_strlen($message) > 5000) {
    $errors['message'] = 'Message is too long';
}

// Service
if ($service === '' || !ctype_digit((string)$service)) {
    $errors['service'] = 'Please select a valid service';
} else {
    $service = (int)$service;
}

// Terms
if ($terms !== true) {
    $errors['terms'] = 'You must agree to the terms';
}

// Turnstile token
if ($token === '') {
    $errors['token'] = 'Please complete the captcha';
} elseif (strlen($token) > 2048) {
    $errors['token'] = 'Invalid captcha token';
}

/*
|--------------------------------------------------------------------------
| Return validation errors
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {
    http_response_code(422);

    echo json_encode([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $errors
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Validate Turnstile
|--------------------------------------------------------------------------
*/

$turnstileUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

$turnstileData = [
    'secret'   => $turnstileSecret,
    'response' => $token,
];

// If your server is behind Cloudflare, you can use the CF-Connecting-IP.
// Otherwise use REMOTE_ADDR.
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $turnstileData['remoteip'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
} elseif (!empty($_SERVER['REMOTE_ADDR'])) {
    $turnstileData['remoteip'] = $_SERVER['REMOTE_ADDR'];
}

$ch = curl_init($turnstileUrl);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($turnstileData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/x-www-form-urlencoded'
    ],
]);

$turnstileResponse = curl_exec($ch);

if ($turnstileResponse === false) {
    error_log('Turnstile request failed: ' . curl_error($ch));

    curl_close($ch);

    http_response_code(503);

    echo json_encode([
        'success' => false,
        'message' => 'Captcha verification unavailable'
    ]);

    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($httpCode !== 200) {
    error_log('Turnstile HTTP error: ' . $httpCode);

    http_response_code(503);

    echo json_encode([
        'success' => false,
        'message' => 'Captcha verification failed'
    ]);

    exit;
}

$turnstileResult = json_decode($turnstileResponse, true);

if (
    !is_array($turnstileResult) ||
    empty($turnstileResult['success'])
) {
    error_log(
        'Turnstile validation failed: ' .
        json_encode($turnstileResult)
    );

    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Captcha verification failed',
        'errors' => [
            'token' => 'Please complete the captcha again'
        ]
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=YOUR_DATABASE;charset=utf8mb4',
        'YOUR_DB_USER',
        'YOUR_DB_PASSWORD',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | Optional: Verify that the service actually exists
    |--------------------------------------------------------------------------
    */

    $serviceStmt = $pdo->prepare(
        'SELECT id FROM services WHERE id = ? LIMIT 1'
    );

    $serviceStmt->execute([$service]);

    if (!$serviceStmt->fetch()) {
        http_response_code(422);

        echo json_encode([
            'success' => false,
            'message' => 'Invalid service'
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Insert contact
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        'INSERT INTO contacts
        (
            name,
            email,
            phone,
            subject,
            message,
            service_id
        )
        VALUES
        (
            :name,
            :email,
            :phone,
            :subject,
            :message,
            :service_id
        )'
    );

    $stmt->execute([
        ':name'       => $name,
        ':email'      => $email,
        ':phone'      => $phone,
        ':subject'    => $subject,
        ':message'    => $message,
        ':service_id' => $service,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully'
    ]);

} catch (PDOException $e) {

    // Log the real error on the server.
    error_log('Contact form database error: ' . $e->getMessage());

    // Don't expose SQL/database details to the user.
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to process your request'
    ]);

    exit;
}