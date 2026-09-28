<?php
//-    // function customerMSGTemplate($name){

    //     return"
    // <html>
    // <body>

    // <h2>Thank you for contacting Paradigm</h2>

    // <p>Hello " . $name . ",</p>

    // <p>
    // We have received your message and our team will get back to you as soon as possible.
    // </p>

    // <p>
    // Thank you for contacting Paradigm.
    // </p>

    // <p>
    // Best regards,<br>
    // Paradigm Team
    // </p>

    // </body>
    // </html>
    // ";
    // }

    // function adminMSGTemplate($name,$email,$phone,$service,$subject,$message){

    //     return "
    // <html>
    // <body>

    // <h2>New Contact Form Submission</h2>

    // <table cellpadding='8' cellspacing='0' border='1'>

    // <tr>
    //     <td><strong>Name</strong></td>
    //     <td>" . $name. "</td>
    // </tr>

    // <tr>
    //     <td><strong>Email</strong></td>
    //     <td>" . $email. "</td>
    // </tr>

    // <tr>
    //     <td><strong>Phone</strong></td>
    //     <td>" . $phone. "</td>
    // </tr>

    // <tr>
    //     <td><strong>Service</strong></td>
    //     <td>" . $service. "</td>
    // </tr>

    // <tr>
    //     <td><strong>Subject</strong></td>
    //     <td>" . $subject. "</td>
    // </tr>

    // <tr>
    //     <td><strong>Message</strong></td>
    //     <td>" . nl2br($message) . "</td>
    // </tr>

    // </table>

    // </body>
    // </html>
    // ";
//-    // }



function emailEscape($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


/*
|--------------------------------------------------------------------------
| Company Information
|--------------------------------------------------------------------------
*/

function getCompanyEmailData()
{
    return [
        'name'  => 'Paradigm',
        'phone' => '+201020799277',
        'email' => 'info@paradigm-eg.com',
        'website' => 'https://paradigmcapitalgroup.com',

        // IMPORTANT:
        // This must be a publicly accessible HTTPS URL.
        'logo' => 'https://paradigmcapitalgroup.com/wp-content/uploads/2024/08/logo.png',

        'address' => '8, 10 Ahmed Nessim Street, 9th floor, Dokki, Giza, Egypt',
    ];
}


/*
|--------------------------------------------------------------------------
| Email Header
|--------------------------------------------------------------------------
*/

function emailHeader()
{
    $company = getCompanyEmailData();

    return '
    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color:#f4f6f8; margin:0; padding:30px 0;">

        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0" border="0"
                    style="
                        max-width:600px;
                        width:100%;
                        background:#ffffff;
                        border-radius:8px;
                        overflow:hidden;
                        font-family:Arial,Helvetica,sans-serif;
                    ">

                    <!-- Header -->
                    <tr>
                        <td align="center"
                            style="
                                background:#042a37;
                                padding:25px;
                            ">

                            <img
                                src="' . emailEscape($company['logo']) . '"
                                alt="' . emailEscape($company['name']) . '"
                                width="160"
                                style="
                                    display:block;
                                    max-width:160px;
                                    height:auto;
                                    border:0;
                                "
                            >

                        </td>
                    </tr>

                    <!-- Content starts -->
                    <tr>
                        <td style="padding:30px;">
    ';
}


/*
|--------------------------------------------------------------------------
| Email Footer
|--------------------------------------------------------------------------
*/

function emailFooter()
{
    $company = getCompanyEmailData();

    return '
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="
                                background:#042a37;
                                color:#ffffff;
                                padding:25px 30px;
                                text-align:center;
                                font-family:Arial,Helvetica,sans-serif;
                                font-size:13px;
                                line-height:1.6;
                            "
                        >

                            <strong style="font-size:16px;">
                                ' . emailEscape($company['name']) . '
                            </strong>

                            <br>

                            <a
                                href="tel:' . emailEscape($company['phone']) . '"
                                style="color:#60daef; text-decoration:none;"
                            >
                                ' . emailEscape($company['phone']) . '
                            </a>

                            &nbsp; | &nbsp;

                            <a
                                href="mailto:' . emailEscape($company['email']) . '"
                                style="color:#60daef; text-decoration:none;"
                            >
                                ' . emailEscape($company['email']) . '
                            </a>

                            <br>

                            <a
                                href="' . emailEscape($company['website']) . '"
                                style="color:#60daef; text-decoration:none;"
                            >
                                ' . emailEscape($company['website']) . '
                            </a>

                            <br>

                            ' . emailEscape($company['address']) . '

                            <br><br>

                            <span style="color:#b8c4c8;">
                                © ' . date('Y') . ' ' .
                                emailEscape($company['name']) .
                                '. All rights reserved.
                            </span>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>

    </table>
    ';
}


/*
|--------------------------------------------------------------------------
| Customer Confirmation Email
|--------------------------------------------------------------------------
*/

function customerMSGTemplate($name)
{
    $company = getCompanyEmailData();

    $name = emailEscape($name);

    return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Contacting ' . emailEscape($company['name']) . '</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8;">

' . emailHeader() . '

<h2 style="
    margin:0 0 20px;
    color:#042a37;
    font-size:24px;
">
    Thank you for contacting ' . emailEscape($company['name']) . '
</h2>

<p style="
    margin:0 0 15px;
    color:#333333;
    font-size:16px;
    line-height:1.6;
">
    Hello ' . $name . ',
</p>

<p style="
    margin:0 0 15px;
    color:#555555;
    font-size:15px;
    line-height:1.7;
">
    We have successfully received your message.
    Thank you for taking the time to contact us.
</p>

<p style="
    margin:0 0 25px;
    color:#555555;
    font-size:15px;
    line-height:1.7;
">
    Our team will review your inquiry and get back to you
    as soon as possible.
</p>

<table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="
        background:#f0f8fa;
        border-left:4px solid #60daef;
        margin-bottom:25px;
    ">
    <tr>
        <td style="
            padding:15px;
            color:#042a37;
            font-size:14px;
            line-height:1.6;
        ">
            <strong>Need immediate assistance?</strong><br>
            You can contact us directly at
            <a
                href="tel:' . emailEscape($company['phone']) . '"
                style="color:#042a37;"
            >
                ' . emailEscape($company['phone']) . '
            </a>.
        </td>
    </tr>
</table>

<p style="
    margin:0;
    color:#555555;
    font-size:15px;
    line-height:1.7;
">
    Best regards,<br>
    <strong>' . emailEscape($company['name']) . ' Team</strong>
</p>

' . emailFooter() . '

</body>
</html>
';
}


/*
|--------------------------------------------------------------------------
| Admin Notification Email
|--------------------------------------------------------------------------
*/

function adminMSGTemplate(
    $name,
    $email,
    $phone,
    $service,
    $subject,
    $message
) {
    $company = getCompanyEmailData();

    $name    = emailEscape($name);
    $email   = emailEscape($email);
    $phone   = emailEscape($phone);
    $service = emailEscape($service);
    $subject = emailEscape($subject);
    $message = nl2br(emailEscape($message));

    $submissionDate = date('F j, Y - g:i A');

    return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission</title>
</head>

<body style="margin:0; padding:0; background:#f4f6f8;">

' . emailHeader() . '

<h2 style="
    margin:0 0 8px;
    color:#042a37;
    font-size:24px;
">
    New Contact Form Submission
</h2>

<p style="
    margin:0 0 25px;
    color:#777777;
    font-size:14px;
">
    A new inquiry has been submitted through the website.
</p>


<!-- Submission Information -->

<table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="
        border-collapse:collapse;
        font-family:Arial,Helvetica,sans-serif;
    ">

    <tr>
        <td colspan="2"
            style="
                background:#042a37;
                color:#ffffff;
                padding:12px 15px;
                font-size:15px;
                font-weight:bold;
            ">
            Customer Information
        </td>
    </tr>


    <tr>
        <td width="35%"
            style="
                padding:12px 15px;
                background:#f5f7f8;
                border-bottom:1px solid #dddddd;
                font-weight:bold;
                color:#333333;
            ">
            Name
        </td>

        <td
            style="
                padding:12px 15px;
                border-bottom:1px solid #dddddd;
                color:#333333;
            ">
            ' . $name . '
        </td>
    </tr>


    <tr>
        <td
            style="
                padding:12px 15px;
                background:#f5f7f8;
                border-bottom:1px solid #dddddd;
                font-weight:bold;
                color:#333333;
            ">
            Email
        </td>

        <td
            style="
                padding:12px 15px;
                border-bottom:1px solid #dddddd;
            ">

            <a
                href="mailto:' . $email . '"
                style="color:#042a37;"
            >
                ' . $email . '
            </a>

        </td>
    </tr>


    <tr>
        <td
            style="
                padding:12px 15px;
                background:#f5f7f8;
                border-bottom:1px solid #dddddd;
                font-weight:bold;
                color:#333333;
            ">
            Phone
        </td>

        <td
            style="
                padding:12px 15px;
                border-bottom:1px solid #dddddd;
            ">

            <a
                href="tel:' . $phone . '"
                style="color:#042a37;"
            >
                ' . $phone . '
            </a>

        </td>
    </tr>


    <tr>
        <td
            style="
                padding:12px 15px;
                background:#f5f7f8;
                border-bottom:1px solid #dddddd;
                font-weight:bold;
                color:#333333;
            ">
            Service
        </td>

        <td
            style="
                padding:12px 15px;
                border-bottom:1px solid #dddddd;
                color:#333333;
            ">
            ' . $service . '
        </td>
    </tr>


    <tr>
        <td
            style="
                padding:12px 15px;
                background:#f5f7f8;
                border-bottom:1px solid #dddddd;
                font-weight:bold;
                color:#333333;
            ">
            Subject
        </td>

        <td
            style="
                padding:12px 15px;
                border-bottom:1px solid #dddddd;
                color:#333333;
            ">
            ' . $subject . '
        </td>
    </tr>


    <tr>
        <td
            style="
                padding:12px 15px;
                background:#f5f7f8;
                font-weight:bold;
                color:#333333;
            ">
            Submitted
        </td>

        <td
            style="
                padding:12px 15px;
                color:#333333;
            ">
            ' . $submissionDate . '
        </td>
    </tr>

</table>


<!-- Message -->

<table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin-top:25px;">

    <tr>
        <td
            style="
                background:#042a37;
                color:#ffffff;
                padding:12px 15px;
                font-size:15px;
                font-weight:bold;
            ">
            Customer Message
        </td>
    </tr>

    <tr>
        <td
            style="
                padding:20px;
                background:#f8f9fa;
                border:1px solid #dddddd;
                color:#333333;
                font-size:15px;
                line-height:1.7;
            ">
            ' . $message . '
        </td>
    </tr>

</table>


<!-- Quick Actions -->

<table width="100%" cellpadding="0" cellspacing="0" border="0"
    style="margin-top:25px;">

    <tr>

        <td align="center">

            <a
                href="mailto:' . $email . '?subject=Re: ' . rawurlencode($subject) . '"
                style="
                    display:inline-block;
                    background:#042a37;
                    color:#ffffff;
                    text-decoration:none;
                    padding:12px 22px;
                    border-radius:5px;
                    font-size:14px;
                    font-weight:bold;
                "
            >
                Reply to Customer
            </a>

        </td>

    </tr>

</table>


<p style="
    margin-top:30px;
    color:#888888;
    font-size:12px;
    line-height:1.5;
">
    This email was automatically generated by the
    ' . emailEscape($company['name']) . ' website contact form.
</p>


' . emailFooter() . '

</body>
</html>
';
}
echo adminMSGTemplate(
    "cust1",
    "sads@dsad.com",
    "01015454546",
    "sadsads sadsa",
    "asdasdsa",
    "asdasdasdasdas"
);

echo "------------------";

echo customerMSGTemplate("cust1");