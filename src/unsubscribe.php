<?php
require_once 'functions.php';

$message = '';

if (isset($_GET['email'])) {
    $email = trim($_GET['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result = unsubscribeEmail($email);
        if ($result) {
            $message = "You have been unsubscribed successfully.";
        } else {
            $message = "Email not found or already unsubscribed.";
        }
    } else {
        $message = "Invalid email address.";
    }
} else {
    $message = "No email specified.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
</head>
<body>
    <h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
    <p><?= htmlspecialchars($message) ?></p>
</body>
</html>
