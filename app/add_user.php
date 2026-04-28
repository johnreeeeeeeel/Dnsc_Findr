<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    session_start();

    // Informations
    $firstname = trim($_POST['firstname']);
    $lastname  = trim($_POST['lastname']);
    $middlename = !empty($_POST['middlename']) ? $_POST['middlename'] : null;
    $sex        = !empty($_POST['sex']) ? $_POST['sex'] : null;
    $dob        = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $usertype_id  = $_POST['userType'];
    $userrole_id  = !empty($_POST['userRole']) ? $_POST['userRole'] : null;
    $institute_id = !empty($_POST['institute']) ? $_POST['institute'] : null;
    $program_id   = !empty($_POST['program']) ? $_POST['program'] : null;

    // Formatted for username
    $firstname_formated  = strtolower($firstname);
    $lastname_formated   = strtolower($lastname);

    // Username
    $username = strtolower(str_replace(' ', '', $firstname_formated . $lastname_formated));

    // Password
    $plainPassword = strtoupper(bin2hex(random_bytes(4)));
    $password = password_hash($plainPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("CALL addUser(?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param(
        "ssssssssssss",
        $firstname,
        $lastname,
        $middlename,
        $sex,
        $dob,
        $username,
        $_POST['email'],
        $password,
        $usertype_id,
        $userrole_id,
        $institute_id,
        $program_id
    );

    if ($stmt->execute()) {

        $_SESSION['created_user_admin'] = [
            'username' => $username,
            'email' => $_POST['email'],
            'password' => $plainPassword
        ];

        require 'emails/success_creation_email.php';
        sendUserEmail($_POST['email'], $username, $plainPassword);

        header('Location: ../admin/admin.php');
        exit();
    } else {
        echo 'Error inserting user: ' . $stmt->error;
    }
}
?>