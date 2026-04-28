<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}

$userId = $_SESSION['id'];

$old = $_POST['old_password'];
$new = $_POST['new_password'];
$confirm = $_POST['confirm_password'];

$message = '';

if ($new !== $confirm) {
    $message = 'Passwords do not match';
} else {

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($old, $user['password'])) {

        $hashed = password_hash($new, PASSWORD_DEFAULT);

        $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update->bind_param("si", $hashed, $userId);

        $message = $update->execute()
            ? 'Password updated successfully'
            : 'Update failed';

    } else {
        $message = 'Incorrect current password';
    }
}

$_SESSION['password_message'] = $message;

header("Location: ../user/settings.php");
exit();