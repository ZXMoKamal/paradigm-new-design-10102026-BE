<?php
$config = require __DIR__ . "/../../../../../configs/paradigm-config.php";
const ENCRYPTION_METHOD = 'aes-256-gcm';

function getEncryptionKey()
{
global $config;
    
    // IMPORTANT:
    // Store this in an environment variable in production.
    $key = $config["encryption"]["key"];//getenv('CONTACT_ENCRYPTION_KEY');

    if (!$key) {
        throw new Exception("Encryption key is not configured.");
    }

    // The key must be exactly 32 bytes for AES-256.
    $key = base64_decode($key, true);

    if ($key === false || strlen($key) !== 32) {
        throw new Exception("Invalid encryption key.");
    }

    return $key;
}


function encryptData($value)
{
    if ($value === null || $value === '') {
        return null;
    }

    $key = getEncryptionKey();

    // AES-GCM recommended IV length
    $iv = random_bytes(12);

    $tag = '';

    $encrypted = openssl_encrypt(
        $value,
        ENCRYPTION_METHOD,
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        '',
        16
    );

    if ($encrypted === false) {
        throw new Exception("Encryption failed.");
    }

    /*
     * Store everything needed for decryption:
     *
     * IV + authentication tag + ciphertext
     */
    return base64_encode(
        $iv . $tag . $encrypted
    );
}


function decryptData($encryptedValue)
{
    if ($encryptedValue === null || $encryptedValue === '') {
        return null;
    }

    $key = getEncryptionKey();

    $data = base64_decode($encryptedValue, true);

    if ($data === false) {
        throw new Exception("Invalid encrypted data.");
    }

    // 12 bytes IV + 16 bytes GCM tag
    if (strlen($data) < 28) {
        throw new Exception("Invalid encrypted data.");
    }

    $iv = substr($data, 0, 12);
    $tag = substr($data, 12, 16);
    $ciphertext = substr($data, 28);

    $decrypted = openssl_decrypt(
        $ciphertext,
        ENCRYPTION_METHOD,
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($decrypted === false) {
        throw new Exception("Decryption failed.");
    }

    return $decrypted;
}