<?php
session_start();
require '../app/db_connection.php';

if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] != 1) {
    header('Location: ../index.php');
    exit;
}

// Get user details
$id = $_SESSION['id'] ?? '';

$lastname = $_SESSION['lastname'] ?? '';
$firstname = $_SESSION['firstname'] ?? '';
$middlename = $_SESSION['middlename'] ?? '';

$sex = $_SESSION['sex'] ?? '';
$dob = $_SESSION['dob'] ?? '';

$usertype = $_SESSION['usertype'] ?? '';
$userrole = $_SESSION['userrole'] ?? '';
$institute = $_SESSION['institute'] ?? '';
$program = $_SESSION['program'] ?? '';

$username = $_SESSION['username'] ?? '';

// Add user
$institute = $conn->query("SELECT id, description FROM institute");
$program = $conn->query("SELECT id, description FROM program");

// Update user
$instituteUpdate = $conn->query("SELECT id, description FROM institute");
$programUpdate = $conn->query("SELECT id, description FROM program");
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
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>

<body class="container-fluid">
    <!-- Loading Screen -->
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
    
    <!-- Mobile Sidebar -->
    <div class="offcanvas offcanvas-start" id="sidebarMobile">

        <div class="offcanvas-body">
            <ul class="nav nav-tabs flex-column">

                <a href="admin.php">
                    <img class="logo" src="../assets/images/dnscfindr-logo.png" alt="logo">
                </a>

                <div class="top-nav">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#dashboard" data-title="Dashboard"
                            onclick="setTitle(this)">
                            <i class="fa-solid fa-chart-simple"></i>
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#posts" data-title="Posts"
                            onclick="setTitle(this)">
                            <i class="fa-solid fa-file"></i>
                            Posts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#users" data-title="Users"
                            onclick="setTitle(this)">
                            <i class="fa-solid fa-users"></i>
                            Users
                        </a>
                    </li>
                </div>

                <div class="bottom-nav">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#create-post">
                            <i class="fa-solid fa-pen-clip"></i>
                            Create Post
                        </a>
                    </li>

                    <li class="user-profile dropup">
                        <a href="" class="profile" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-circle-user"></i>
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fa-solid fa-circle-user"></i>
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fa-solid fa-gear"></i>
                                    Settings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="../app/logout.php">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                    logout
                                </a>
                            </li>
                        </ul>
                        <small class="name">
                            <?php
                                echo $username;
                            ?>
                    </small>
                    </li>
                </div>
            </ul>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <div id="sidebarDesktop">
        <ul class="nav nav-tabs flex-column">

            <a href="admin.php">
                <img class="logo" src="../assets/images/dnscfindr-logo.png" alt="logo">
            </a>

            <div class="top-nav">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#dashboard" data-title="Dashboard"
                        onclick="setTitle(this)">
                        <i class="fa-solid fa-chart-simple"></i>
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#posts" data-title="Posts"
                        onclick="setTitle(this)">
                        <i class="fa-solid fa-file"></i>
                        Posts
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#users" data-title="Users"
                        onclick="setTitle(this)">
                        <i class="fa-solid fa-users"></i>
                        Users
                    </a>
                </li>
            </div>

            <div class="bottom-nav">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#create-post">
                        <i class="fa-solid fa-pen-clip"></i>
                        Create Post
                    </a>
                </li>

                <li class="user-profile dropup">
                    <a href="" class="profile" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-circle-user"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fa-solid fa-circle-user"></i>
                                Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fa-solid fa-gear"></i>
                                Settings
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="../app/logout.php">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                logout
                            </a>
                        </li>
                    </ul>
                    <small class="name">
                        <?php
                            echo $username;
                        ?>
                    </small>
                </li>
            </div>
        </ul>
    </div>

    <section id="section">
        <header>
            <div class="left-actions">
                <div class="menuToggleButtonContainer">
                    <i class="fa-solid fa-bars menuToggleButton" data-bs-toggle="offcanvas"
                        data-bs-target="#sidebarMobile"></i>
                </div>
                <h1 class="page-title">Dashboard</h1>
            </div>

            <div class="right-actions">
                <a href="">
                    <i class="fa-regular fa-message"></i>
                </a>
                <a href="">
                    <i class="fa-regular fa-bell"></i>
                </a>
                <a href="">
                    <i class="fa-regular fa-calendar"></i>
                </a>
            </div>
        </header>

        <div class="tab-content">

            <div class="tab-pane fade show active" id="dashboard">
                <h1>Dashboard</h1>
            </div>

            <div class="tab-pane fade" id="posts">
                <h1>Posts</h1>
            </div>

            <div class="tab-pane fade" id="users">

                <div class="header">
                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="search" id="searchUser">
                    </div>
                </div>

                <!-- Users table -->
                <div class="table-container">
                    <h2>
                        <span>Users</span>
                        <button class="sm-btn secondary-btn" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="fa-solid fa-user-plus"></i>Add User</button>
                    </h2>
                    
                    <table class="table table-borderless">
                        <thead>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Fullname</th>
                            <th>Email</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <?php
                                $result = $conn->query("SELECT * FROM view_users");
                            ?>

                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td data-label="ID"><?= $row['id']; ?></td>
                                    <td data-label="Username"><?= $row['username']; ?></td>
                                    <td data-label="Fullname"><?= $row['fullname']; ?></td>
                                    <td data-label="Email"><?= $row['email']; ?></td>
                                    <td data-label="Action">
                                        <div class="action-buttons">
                                            <button type="button" class="sm-btn primary-btn" data-bs-toggle="modal" data-bs-target="#viewUserModal" 
                                                onclick="viewUserDetails(
                                                    '<?= $row['id'] ?>', 
                                                    '<?= $row['username'] ?>', 
                                                    '<?= $row['fullname'] ?>', 
                                                    '<?= $row['sex'] ?>', 
                                                    '<?= $row['dob'] ?>', 
                                                    '<?= $row['userrole'] ?>', 
                                                    '<?= $row['institute'] ?>', 
                                                    '<?= $row['program'] ?>', 
                                                    '<?= $row['email'] ?>'
                                                )">
                                                <i class="fa-solid fa-eye"></i>
                                                View
                                            </button>

                                            <button 
                                                type="button" 
                                                class="sm-btn primary-btn"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateUserModal"
                                                onclick="updateUserDetails(
                                                    '<?= $row['id'] ?>',
                                                    '<?= $row['firstname'] ?>',
                                                    '<?= $row['lastname'] ?>',
                                                    '<?= $row['middlename'] ?>',
                                                    '<?= $row['sex'] ?>',
                                                    '<?= $row['dob'] ?>',
                                                    '<?= $row['usertype_id'] ?>',
                                                    '<?= $row['userrole_id'] ?>',
                                                    '<?= $row['institute_id'] ?>',
                                                    '<?= $row['program_id'] ?>',
                                                    '<?= $row['email'] ?>'
                                                )"
                                            >
                                                <i class="fa-solid fa-user-pen"></i>
                                                Update
                                            </button>

                                            <button 
                                                type="button" 
                                                class="sm-btn primary-btn"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal"
                                                onclick="setDeleteUser('<?= $row['id'] ?>')"
                                            >
                                                <i class="fa-solid fa-trash"></i>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

             <!-- Add User Modal -->
            <div class="modal fade" id="addUserModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">
                                <i class="fa-solid fa-user"></i>
                                Add User
                            </h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <form method="POST" action="../app/add_user.php">
                                <div class="field-group">
                                    <label>User Type</label>
                                    <select name="userType" class="form-control" required>
                                        <option value="2" selected>User</option>
                                    </select>
                                </div>
                                
                                <div class="field-group">
                                    <label>User Role</label>
                                    <select name="userRole" class="form-control" required>
                                        <option value="">Select User Role</option>
                                        <option value="1">Instructor</option>
                                        <option value="2">Staff</option>
                                        <option value="3">Student</option>
                                    </select>
                                </div>
                        
                                <div class="field-group">
                                    <label>Personal Information</label>
                                    <input type="text" name="firstname" class="form-control" placeholder="First Name" required>
                                    <input type="text" name="middlename" class="form-control" placeholder="Middle Name">
                                    <input type="text" name="lastname" class="form-control" placeholder="Last Name" required>

                                    <select name="sex" class="form-control" required>
                                        <option value="">Select Sex</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <input type="date" name="dob" class="form-control" required>
                                </div>

                                <div class="field-group">
                                    <label>Academic Information</label>
                                    <select name="institute" class="form-control">
                                        <option value="">Select Institute</option>
                                        <?php while ($row = $institute->fetch_assoc()) { ?>
                                            <option value="<?= $row['id'] ?>">
                                                <?= $row['description'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    
                                    <select name="program" class="form-control">
                                        <option value="">Select Program</option>
                                        <?php while ($row = $program->fetch_assoc()) { ?>
                                            <option value="<?= $row['id'] ?>">
                                                <?= $row['description'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="field-group">
                                    <label>Account Information</label>
                                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                                </div>

                                <div class="action-buttons">
                                    <button type="submit" class="btn primary-btn">
                                        Save
                                    </button>
                                    <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Update user details modal -->
            <div class="modal fade" id="updateUserModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h4 class="modal-title">
                                <i class="fa-solid fa-user-pen"></i>
                                Update User
                            </h4>
                        </div>

                        <div class="modal-body">
                            <form method="POST" action="../app/update_user.php">
                                <input type="hidden" name="id" id="eu_id">

                                <div class="field-group">
                                    <label>User Type</label>
                                    <select name="userType" id="eu_usertype" class="form-control">
                                        <option value="2">User</option>
                                    </select>
                                </div>
                                
                                <div class="field-group">
                                    <label>User Role</label>
                                    <select name="userRole" id="eu_userrole" class="form-control">
                                        <option value="">Select Role</option>
                                        <option value="1">Instructor</option>
                                        <option value="2">Staff</option>
                                        <option value="3">Student</option>
                                    </select>
                                </div>

                                <div class="field-group">
                                    <label>Personal Information</label>
                                    <input type="text" name="firstname" id="eu_firstname" class="form-control" placeholder="First Name" required>
                                    <input type="text" name="middlename" id="eu_middlename" class="form-control" placeholder="Middle Name">
                                    <input type="text" name="lastname" id="eu_lastname" class="form-control" placeholder="Last Name" required>

                                    <select name="sex" id="eu_sex" class="form-control">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <input type="date" name="dob" id="eu_dob" class="form-control">
                                </div>

                                <div class="field-group">
                                    <label>Academic Information</label>
                                    <select name="institute" id="eu_institute" class="form-control">
                                        <option value="">Select Institute</option>
                                        <?php while ($row = $instituteUpdate->fetch_assoc()) { ?>
                                            <option value="<?= $row['id'] ?>">
                                                <?= $row['description'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>

                                    <select name="program" id="eu_program" class="form-control">
                                        <option value="">Select Program</option>
                                        <?php while ($row = $programUpdate->fetch_assoc()) { ?>
                                            <option value="<?= $row['id'] ?>">
                                                <?= $row['description'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="field-group">
                                    <label>Account Information</label>
                                    <input type="email" name="email" id="eu_email" class="form-control" required>
                                </div>

                                <div class="action-buttons">
                                    <button type="submit" class="btn primary-btn">
                                        Update
                                    </button>
                                    <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View user details modal -->
            <div class="modal fade" id="viewUserModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="profile-container">
                                <div class="profile">
                                    <div class="profile-icon">
                                        <i class="fa-solid fa-circle-user"></i>
                                        <span class="badge rounded-pill" id="vu_userrole"></span>
                                    </div>
                                    <h5><span id="vu_fullname"></span></h5>
                                </div>
                                
                                
                                <div class="profile-section">
                                    <h6>Personal Information</h6>
                                    <p>
                                        <small><b>User ID: </b><span id="vu_id"></span></small>
                                        <small><b>Username: </b><span id="vu_username"></span></small>
                                    </p>
                                    <p>
                                        <small><b>Sex: </b><span id="vu_sex"></span></small>
                                        <small><b>Data of Birth: </b><span id="vu_dob"></span></small>
                                    </p>
                                </div>

                                <div class="profile-section">
                                    <h6>Academic Information</h6>
                                    <p>
                                        <small><b>Institute: </b><span id="vu_institute"></span></small>
                                        <small><b>Program: </b><span id="vu_program"></span></small>
                                    </p>
                                </div>

                                <div class="profile-section">
                                    <h6>Account Information</h6>
                                    <p>
                                        <small><b>Email: </b><span id="vu_email"></span></small>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete user modal -->
            <div class="modal fade confirmation-modal" id="deleteUserModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h4 class="modal-title">
                                <i class="fa-solid fa-trash"></i>
                                Delete User
                            </h4>
                        </div>

                        <div class="modal-body">
                            <form method="POST" action="../app/delete_user.php">
                                <input type="hidden" name="id" id="du_id">

                                <p>Are you sure you want to delete this user?</p>

                                <div class="action-buttons">
                                    <button type="submit" class="btn primary-btn">
                                        Yes, Delete
                                    </button>
                                    <button type="button" class="btn secondary-btn" data-bs-dismiss="modal">
                                        No
                                    </button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

<!-- Js -->
<script src="../assets/js/script.js"></script>
