<?php
require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    session_start();

    $id         = $_POST['id'];
    $firstname  = trim($_POST['firstname']);
    $lastname   = trim($_POST['lastname']);
    $middlename = !empty($_POST['middlename']) ? $_POST['middlename'] : null;
    $sex        = !empty($_POST['sex']) ? $_POST['sex'] : null;
    $dob        = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $usertype_id  = $_POST['userType'];
    $userrole_id  = !empty($_POST['userRole']) ? $_POST['userRole'] : null;
    $institute_id = !empty($_POST['institute']) ? $_POST['institute'] : null;
    $program_id   = !empty($_POST['program']) ? $_POST['program'] : null;

    $stmt = $conn->prepare("CALL updateUser(?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param(
        "isssssssss",
        $id,
        $firstname,
        $lastname,
        $middlename,
        $sex,
        $dob,
        $usertype_id,
        $userrole_id,
        $institute_id,
        $program_id
    );

    if ($stmt->execute()) {
        // Alert message
        $_SESSION['alert_message'] = [
            'type' => 'success',
            'text' => 'User updated successfully!'
        ];
    } else {
        // Alert message
        $_SESSION['alert_message'] = [
            'type' => 'danger',
            'text' => 'Error updating user: ' . $stmt->error
        ];
    }

    header('Location: ../admin/admin.php');
    exit();
}
?>