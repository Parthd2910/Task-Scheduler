<?php
require_once 'functions.php';

$message = '';

if (isset($_GET['email'], $_GET['code'])) {
    $email = trim($_GET['email']);
    $code = trim($_GET['code']);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (verifySubscription($email, $code)) {
            $message = "✅ Your subscription has been verified successfully! You will start receiving reminders.";
        } else {
            $message = "❌ Verification failed. The code or email is invalid, or it may have already been verified.";
        }
    } else {
        $message = "❌ Invalid email address.";
    }
} else {
    $message = "❌ Missing verification parameters.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subscription Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 40px;
        }
        #verification-heading {
            margin-bottom: 20px;
        }
        .message {
            font-size: 18px;
            color: #333;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>
    <h2 id="verification-heading">Subscription Verification</h2>
    <p class="message <?= strpos($message, 'successfully') !== false ? 'success' : 'error' ?>">
        <?= htmlspecialchars($message) ?>
    </p>
</body>
</html>
