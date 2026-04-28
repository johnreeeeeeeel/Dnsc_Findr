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
    <link rel="shortcut icon" href="../../assets/images/favicon.ico" type="image/x-icon">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="../../assets/css/components.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <style>
        * {
            font-family: "Outfit", sans-serif;
            font-weight: 500;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            overflow: hidden;
            background-color: #f2f3f7;
        }

        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            width: 300px;
        }

        .form-container form {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-container form h2 {
            color: #48c441; 
        }

        .form-container form h2 a {
            cursor: pointer;
        }
    </style>
</head>

<body class="container-fluid">
    <div id="loadingScreen" class="loading-screen d-none">
        <div class="spinner-grow"></div>
    </div>

    <div class="form-container">
        <form method="POST" action="check_otp.php">
            <h2>
                <a onclick="history.back()">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
                Verify OTP
            </h2>
            <input type="text" name="otp" placeholder="Enter OTP" required class="form-control">

            <button type="submit" class="btn primary-btn">
                Verify OTP
            </button>
        </form> 
    </div>
</body>

<!-- Js -->
<script src="../../assets/js/script.js"></script>