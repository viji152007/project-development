<?php

session_start();

/* =====================================================
   CACHE CONTROL
===================================================== */

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


/* =====================================================
   LOGIN CHECK
===================================================== */

if (
    !isset($_SESSION['username']) &&
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['user'])
) {
    header("Location: login.php");
    exit();
}


/* =====================================================
   DATABASE CONNECTION
===================================================== */

include 'config/db.php';


/* =====================================================
   GET USER INFORMATION
===================================================== */

$user = null;


/* Prefer user_id if available */

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE id = ? LIMIT 1"
    );

    if ($stmt) {

        $stmt->bind_param("i", $user_id);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();
    }
}


/* If user_id is not available, use username */

if (!$user && isset($_SESSION['username'])) {

    $username = $_SESSION['username'];

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE username = ? LIMIT 1"
    );

    if ($stmt) {

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $result = $stmt->get_result();

        $user = $result->fetch_assoc();

        $stmt->close();
    }
}


/* =====================================================
   USER DATA
===================================================== */

$fullname = $user['fullname'] ?? "User";

$username = $user['username'] ?? ($_SESSION['username'] ?? "");

$email = $user['email'] ?? "";

$profile_photo = $user['profile_photo'] ?? "";


/* =====================================================
   SUCCESS MESSAGE
===================================================== */

$successMsg = "";

if (isset($_SESSION['successMsg'])) {

    $successMsg = $_SESSION['successMsg'];

    unset($_SESSION['successMsg']);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Personal Portfolio</title>


    <!-- FONT AWESOME -->

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


    <!-- MAIN CSS -->

    <link rel="stylesheet"
          href="css/style.css">

</head>


<body>


<!-- =====================================================
     SUCCESS MESSAGE
===================================================== -->

<?php if ($successMsg != "") { ?>

    <div class="success-message">

        <i class="fa-solid fa-circle-check"></i>

        <?php echo htmlspecialchars($successMsg); ?>

    </div>

<?php } ?>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">


    <!-- PORTFOLIO TITLE -->

    <h2>

        <i class="fa-solid fa-user"></i>

        My Portfolio

    </h2>


    <!-- HOME -->

    <a href="dashboard.php" class="active">

        <i class="fa-solid fa-house"></i>

        <span>Home</span>

    </a>


    <!-- ABOUT -->

    <a href="about.php">

        <i class="fa-solid fa-user"></i>

        <span>About</span>

    </a>
     <!-- CERTIFICATE -->

    <a href="education.php">

        <i class="fa-solid fa-user"></i>

        <span>Education</span>

    </a>


    <!-- SKILLS -->

    <a href="skills.php">

        <i class="fa-solid fa-code"></i>

        <span>Skills</span>

    </a>


    <!-- PROJECTS -->

    <a href="projects.php">

        <i class="fa-solid fa-folder"></i>

        <span>Projects</span>

    </a>


    <!-- CERTIFICATES -->

    <a href="certificates.php">

        <i class="fa-solid fa-certificate"></i>

        <span>Certificates</span>

    </a>


    <!-- RESUME -->

    <a href="resume.php">

        <i class="fa-solid fa-file-alt"></i>

        <span>Resume</span>

    </a>


    <!-- CONTACT -->

    <a href="contact.php">

        <i class="fa-solid fa-envelope"></i>

        <span>Contact</span>

    </a>


    <!-- CHANGE PASSWORD -->

    <a href="change_password.php">

        <i class="fa-solid fa-lock"></i>

        <span>Change Password</span>

    </a>




        <!-- =====================================================
     SHARE PORTFOLIO
===================================================== -->

<a href="shareportfolio.php">

    <i class="fa-solid fa-link"></i>

    <span>Share Portfolio</span>

</a>


    <!-- LOGOUT -->

    <a href="logout.php">

        <i class="fa-solid fa-right-from-bracket"></i>

        <span>Logout</span>

    </a>


</div>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="content">


    <div class="dashboard-card">


        <!-- PROFILE PHOTO -->

        <div class="dashboard-profile">

            <?php if (!empty($profile_photo)) { ?>

                <img
                    src="uploads/profile/<?php
                    echo htmlspecialchars($profile_photo);
                    ?>"
                    alt="Profile Photo"
                    class="dashboard-profile-img"
                >

            <?php } else { ?>

                <div class="dashboard-default-profile">

                    <i class="fa-solid fa-user"></i>

                </div>

            <?php } ?>

        </div>


        <!-- WELCOME -->

        <h1>

            👋 Welcome,

            <?php echo htmlspecialchars($fullname); ?>!

        </h1>


        <!-- DESCRIPTION -->

        <p>

            Welcome to your Personal Portfolio Dashboard.

        </p>


        <p>

            From here you can manage your profile,
            skills, projects, certificates, resume,
            and contact information.

        </p>


        <!-- EMAIL -->

        <?php if (!empty($email)) { ?>

            <div class="dashboard-email">

                <i class="fa-solid fa-envelope"></i>

                <?php echo htmlspecialchars($email); ?>

            </div>

        <?php } ?>


    </div>


</div>


<!-- =====================================================
     SUCCESS MESSAGE AUTO HIDE
===================================================== -->

<script>

setTimeout(function () {

    const message =
        document.querySelector(".success-message");

    if (message) {

        message.style.display = "none";

    }

}, 3000);

</script>


</body>

</html>