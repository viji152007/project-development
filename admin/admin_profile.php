<?php
session_start();

/* Admin Login Check */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../config/db.php";

/* Admin Email from Session */
$admin_email = $_SESSION['admin_email'] ?? "Administrator";

/* Admin Details */
$admin_name = "Project Admin";
$admin_role = "Administrator";
$admin_status = "Active";
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Admin - Profile</title>

    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <style>

        /* ================= PAGE HEADER ================= */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0;
        }


        /* ================= PROFILE BOX ================= */

        .profile-box {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            max-width: 750px;
        }


        /* ================= PROFILE HEADER ================= */

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-bottom: 25px;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
        }


        .profile-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #0d1b2a;
            color: #00d9ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
        }


        .profile-header h2 {
            margin: 0 0 5px 0;
            color: #0d1b2a;
        }


        .profile-header p {
            margin: 0;
            color: #777;
        }


        /* ================= PROFILE DETAILS ================= */

        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }


        .profile-row {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }


        .profile-row i {
            width: 35px;
            color: #0077ff;
            font-size: 18px;
        }


        .profile-label {
            width: 150px;
            font-weight: 600;
            color: #333;
        }


        .profile-value {
            color: #555;
        }


        /* ================= STATUS ================= */

        .status-active {
            color: #198754;
            font-weight: 600;
        }


        /* ================= ACTIONS ================= */

        .profile-actions {
            display: flex;
            gap: 12px;
            margin-top: 25px;
        }


        .action-btn {
            display: inline-block;
            padding: 11px 18px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
        }


        .password-btn {
            background: #0077ff;
        }


        .logout-btn {
            background: #dc3545;
        }


        .action-btn:hover {
            opacity: 0.9;
        }

    </style>

</head>


<body>

<div class="dashboard-container">


    <!-- ================= SIDEBAR ================= -->

    <?php include "sidebar.php"; ?>


    <!-- ================= MAIN CONTENT ================= -->

    <main class="main-content">


        <div class="page-header">

            <h1>
                <i class="fa-solid fa-user"></i>
                Admin Profile
            </h1>

        </div>


        <!-- ================= PROFILE BOX ================= -->

        <div class="profile-box">


            <div class="profile-header">

                <div class="profile-icon">

                    <i class="fa-solid fa-user-shield"></i>

                </div>


                <div>

                    <h2>
                        <?php echo htmlspecialchars($admin_name); ?>
                    </h2>

                    <p>
                        Administrator
                    </p>

                </div>

            </div>


            <!-- ================= DETAILS ================= -->

            <div class="profile-details">


                <div class="profile-row">

                    <i class="fa-solid fa-user"></i>

                    <span class="profile-label">
                        Admin Name
                    </span>

                    <span class="profile-value">
                        <?php echo htmlspecialchars($admin_name); ?>
                    </span>

                </div>


                <div class="profile-row">

                    <i class="fa-solid fa-envelope"></i>

                    <span class="profile-label">
                        Email
                    </span>

                    <span class="profile-value">
                        <?php echo htmlspecialchars($admin_email); ?>
                    </span>

                </div>


                <div class="profile-row">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span class="profile-label">
                        Role
                    </span>

                    <span class="profile-value">
                        <?php echo htmlspecialchars($admin_role); ?>
                    </span>

                </div>


                <div class="profile-row">

                    <i class="fa-solid fa-circle-check"></i>

                    <span class="profile-label">
                        Status
                    </span>

                    <span class="profile-value status-active">
                        <?php echo htmlspecialchars($admin_status); ?>
                    </span>

                </div>


            </div>


            <!-- ================= ACTIONS ================= -->

            <div class="profile-actions">

                <a href="change_password.php"
                   class="action-btn password-btn">

                    <i class="fa-solid fa-key"></i>
                    Change Password

                </a>


                <a href="logout.php"
                   class="action-btn logout-btn">

                    <i class="fa-solid fa-right-from-bracket"></i>
                    Logout

                </a>

            </div>


        </div>


    </main>

</div>

</body>

</html>