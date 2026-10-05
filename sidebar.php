<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header("Location: /vijiii/login.php");
    exit();
}


/* Logged-in user's Primary Key */

$user_id = (int) $_SESSION['user_id'];

?>

<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">

    <!-- PORTFOLIO TITLE -->

    <h2>
        <i class="fa-solid fa-user"></i>
        <span>My Portfolio</span>
    </h2>


    <!-- HOME -->

    <a href="/vijiii/dashboard.php">
        <i class="fa-solid fa-house"></i>
        <span>Home</span>
    </a>


    <!-- ABOUT -->

    <a href="/vijiii/about.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-user"></i>
        <span>About</span>
    </a>


    <!-- EDUCATION -->

    <a href="/vijiii/education.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-graduation-cap"></i>
        <span>Education</span>
    </a>


    <!-- SKILLS -->

    <a href="/vijiii/skills.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-code"></i>
        <span>Skills</span>
    </a>


    <!-- PROJECTS -->

    <a href="/vijiii/projects.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-folder"></i>
        <span>Projects</span>
    </a>


    <!-- CERTIFICATES -->

    <a href="/vijiii/certificates.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-certificate"></i>
        <span>Certificates</span>
    </a>


    <!-- RESUME -->

    <a href="/vijiii/resume.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-file-alt"></i>
        <span>Resume</span>
    </a>


    <!-- CONTACT -->

    <a href="/vijiii/contact.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-envelope"></i>
        <span>Contact</span>
    </a>


    <!-- CHANGE PASSWORD -->

    <a href="/vijiii/change_password.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-lock"></i>
        <span>Change Password</span>
    </a>


    <!-- SHARE PORTFOLIO -->

    <a href="/vijiii/shareportfolio.php?user_id=<?php echo $user_id; ?>">
        <i class="fa-solid fa-link"></i>
        <span>Share Portfolio</span>
    </a>


    <!-- LOGOUT -->

    <a href="/vijiii/logout.php">
        <i class="fa-solid fa-right-from-bracket"></i>
        <span>Logout</span>
    </a>

</div>
