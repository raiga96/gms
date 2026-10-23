<?php
require 'connect.php';
$Email = $_POST['uname'];

if(empty($Email)){
	$response = array(
        "status" => "error",
        "message" => "Please provide your email address. Thank you"
    );
}else{
// Check for matching email.
$sql = "SELECT * FROM users WHERE user_email LIKE '".$Email."'";
$exc = mysqli_query($conn, $sql);

if ($row = mysqli_num_rows($exc) > 0) {
    $ada = true;
    $result = $exc->fetch_assoc();
    $EMAIL = $result['user_email'];
    $UserID = $result['user_id'];
    $uid = $result['uid'];
} else {
	$ada = false;
	$response = array(
        "status" => "error",
        "message" => "Your email does not match. Please try again. Thank you."
    );
}

if ($ada) {
    $to = $EMAIL;
    $subject = "QREASy Reset Password";
    $msg = "Dear ".$UserID.",
To initiate the password reset process for your QREASy account, please click the link below: 

https://jts.sarawak.gov.my/qreasyv2/Resetpassword.php?uid=".$uid."

If clicking the link above doesn't work, please copy and paste the URL in a new browser window instead.

If you've received this mail in error, it's likely that another user entered your email address by mistake while trying to reset a password. If you didn't initiate the request, you don't need to take any further action and can safely disregard this email. 

This is a system-generated email. Please do not reply to it.

Thank You
Regards
QREASy
Land and Survey Department";

    $headers = "From: donotreply-QREASy@sarawak.gov.my \r\n";

    if (mail($to, $subject, $msg, $headers)) {
        // Email sent successfully
        $response = array(
            "status" => "success",
            "message" => "Please check your registered email to reset your password. Thank you."
        );
    } else {
        // Failed to send email
        $response = array(
            "status" => "false",
            "message" => "Failed to send email. Please try again later."
        );
    }
} else {
    // No matching email found
    $response = array(
        "status" => "error",
        "message" => "Your email does not match. Please try again. Thank you."
    );
}
}
// Send JSON response back to client
header('Content-Type: application/json');
echo json_encode($response);
?>
