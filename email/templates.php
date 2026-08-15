<?php
function customerMSGTemplate($name){

    return"
<html>
<body>

<h2>Thank you for contacting Paradigm</h2>

<p>Hello " . $name . ",</p>

<p>
We have received your message and our team will get back to you as soon as possible.
</p>

<p>
Thank you for contacting Paradigm.
</p>

<p>
Best regards,<br>
Paradigm Team
</p>

</body>
</html>
";
}

function adminMSGTemplate($name,$email,$phone,$service,$subject,$message){

    return "
<html>
<body>

<h2>New Contact Form Submission</h2>

<table cellpadding='8' cellspacing='0' border='1'>

<tr>
    <td><strong>Name</strong></td>
    <td>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</td>
</tr>

<tr>
    <td><strong>Email</strong></td>
    <td>" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</td>
</tr>

<tr>
    <td><strong>Phone</strong></td>
    <td>" . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . "</td>
</tr>

<tr>
    <td><strong>Service</strong></td>
    <td>" . htmlspecialchars($service, ENT_QUOTES, 'UTF-8') . "</td>
</tr>

<tr>
    <td><strong>Subject</strong></td>
    <td>" . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . "</td>
</tr>

<tr>
    <td><strong>Message</strong></td>
    <td>" . nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . "</td>
</tr>

</table>

</body>
</html>
";
}