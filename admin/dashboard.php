<?php
session_start();

/* Admin Login Check */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../config/db.php";

$admin_email = $_SESSION['admin_email'] ?? "Administrator";

/* Count Helper */
function getCount($conn, $table)
{
    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");

    if ($result) {
        $row = $result->fetch_assoc();
        return (int)$row['total'];
    }

    return 0;
}

/* Dashboard Statistics */
$total_users = getCount($conn, "users");
$total_projects = getCount($conn, "projects");
$total_certificates = getCount($conn, "certificates");
$total_education = getCount($conn, "education");
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

    <!-- Admin CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

<div class="dashboard-container">

    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar">

        <div class="sidebar-header">

            <h2>
                <i class="fa-solid fa-user-shield"></i>
                ADMIN PANEL
            </h2>

        </div>

        <nav>

            <a href="dashboard.php" class="active">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <a href="users.php">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>

            <a href="projects.php">
                <i class="fa-solid fa-folder"></i>
                <span>Projects</span>
            </a>

            <a href="certificates.php">
                <i class="fa-solid fa-trophy"></i>
                <span>Certificates</span>
            </a>

            <a href="education.php">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Education</span>
            </a>
               <a href="contact.php">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Contact</span>
</a>
                   <a href="skills.php">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Skills</span>
</a>
               <a href="resume.php">
                <i class="fa-solid fa-graduation-cap"></i>
                <span>Resume</span>
            </a>

            <a href="admin_profile.php">
                <i class="fa-solid fa-user"></i>
                <span>Admin Profile</span>
            </a>

            <a href="logout.php">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>

        </nav>

    </aside>


    <!-- ================= MAIN CONTENT ================= -->

    <main class="main-content">

        <!-- Header -->

        <div class="dashboard-header">

            <div>

                <h1>Welcome, Administrator</h1>

            </div>

            <div class="admin-info">

                <i class="fa-solid fa-user-shield"></i>

                <span>
                    <?php echo htmlspecialchars($admin_email); ?>
                </span>

            </div>

        </div>


        <!-- ================= SMALL STATISTICS ================= -->

        <div class="dashboard-stats">

            <!-- Users -->

            <div class="stat-box">

                <div class="stat-icon">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="stat-info">

                    <h3>Total Users</h3>

                    <p>
                        <?php echo $total_users; ?>
                    </p>

                </div>

            </div>


            <!-- Projects -->

            <div class="stat-box">

                <div class="stat-icon">
                    <i class="fa-solid fa-folder"></i>
                </div>

                <div class="stat-info">

                    <h3>Total Projects</h3>

                    <p>
                        <?php echo $total_projects; ?>
                    </p>

                </div>

            </div>


            <!-- Certificates -->

            <div class="stat-box">

                <div class="stat-icon">
                    <i class="fa-solid fa-trophy"></i>
                </div>

                <div class="stat-info">

                    <h3>Certificates</h3>

                    <p>
                        <?php echo $total_certificates; ?>
                    </p>

                </div>

            </div>


            <!-- Education -->

            <div class="stat-box">

                <div class="stat-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>

                <div class="stat-info">

                    <h3>Education</h3>

                    <p>
                        <?php echo $total_education; ?>
                    </p>

                </div>

            </div>

        </div>

    </main>

</div>

</body>
</html>