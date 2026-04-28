<?php
session_start();
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usertype_id = $_POST['userType'];

    $username = $_POST['username'];
    $email    = $_POST['email'];

    $plainPassword = $_POST['password'];
    $password = password_hash($plainPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('CALL addAdmin(?,?,?,?)');
    $stmt->bind_param('isss', $usertype_id, $username, $email, $password);

    if ($stmt->execute()) {

        $_SESSION['created_user_admin'] = [
            'username' => $username,
            'email' => $email,
            'password' => $plainPassword
        ];

        header('Location: ../admin/admin.php');
        exit();
    } else {
        echo 'Error inserting user: ' . $stmt->error;
    }
}
?>