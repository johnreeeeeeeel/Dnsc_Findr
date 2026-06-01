<?php
session_start();
require 'app/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Get user details and login
    $stmt = $conn->prepare("CALL getUserByEmail (?)");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $users = $result->fetch_assoc();

        if (password_verify($password, $users['password'])) {

            // Get user details
            $_SESSION['id'] = $users['id'];
    
            $_SESSION['lastname'] = $users['lastname'];
            $_SESSION['firstname'] = $users['firstname'];
            $_SESSION['middlename'] = $users['middlename'];

            $_SESSION['sex'] = $users['sex'];
            $_SESSION['dob'] = $users['dob'];

            $_SESSION['usertype'] = $users['usertype_id']; 
            $_SESSION['userrole'] = $users['userrole_id']; 
            $_SESSION['institute'] = $users['institute_id']; 
            $_SESSION['program'] = $users['program_id']; 
            
            $_SESSION['username'] = $users['username'];

            if ($users['usertype_id'] == 1) {
                header("Location: admin/admin.php");
            } else {
                header("Location: user/user.php");
            }
            exit;

        } else {
            $_SESSION['alert_message'] = [
                'type' => 'danger',
                'text' => 'Invalid Password'
            ];
        }
    } else {
        $_SESSION['alert_message'] = [
            'type' => 'danger',
            'text' => 'User Not Found'
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DNSC Findr</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Changa+One:ital@0;1&family=Outfit:wght@100..900&display=swap"
        rel="stylesheet">

    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico" type="image/x-icon">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/components.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<body class="container-fluid">
    <!-- Loading Spinner -->
    <div id="loadingScreen" class="loading-screen d-none">
        <div class="spinner-grow"></div>
    </div>

    <!-- Alert messege -->
    <?php if (isset($_SESSION['alert_message'])): ?>
        <div class="alert alert-<?= $_SESSION['alert_message']['type'] ?> alert-dismissible fade show" id="messageAlert">
            <?= $_SESSION['alert_message']['text'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <?php unset($_SESSION['alert_message']); ?>
    <?php endif; ?>

    <div class="row auth-container">

        <div class="col left">
            <img src="assets/images/lost-and-found-illustration.png" alt="reload">
        </div>

        <div class="col right">
            <img src="assets/images/dnscfindr-logo.png" alt="reload">

            <!-- Login Form -->
            <div class="auth-form" id="user-login-form">
                <form method="POST" action="">
                    <input type="email" id="email" class="input" placeholder="Email" name="email" required>
                    <input type="password" id="password" class="input" placeholder="Password" name="password" required>

                    <div class="login-essential">
                        <div>
                            <input type="checkbox" id="user-show-password" v-model="showPassword">
                            <label for="user-show-password">Show Password</label>
                        </div>
                        <div>
                            <a data-bs-toggle="modal" data-bs-target="#lostPasswordModal">Forgot Password?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn primary-btn">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="links">
        <div>
            <p>DNSC FINDR - LOST AND FOUND SYSTEM</p>
        </div>
        <div>
            <a href="/about">ABOUT</a>
            <a href="/how-it-works">HOW IT WORKS?</a>
        </div>
    </div>

    <!-- Reset password modal -->
    <div class="modal fade primary-modal" id="lostPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">
                        <i class="fa-solid fa-key"></i>
                        Forgot Your Password?
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!--Send OTP -->
                    <form method="POST" action="app/OTP/send_otp.php">
                        <input type="email" name="email" placeholder="Enter your email"
                            class="form-control" required>

                        <button type="submit" class="btn primary-btn">
                            Send OTP
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

<!-- Js -->
<script src="assets/js/script.js"></script>

</html>
