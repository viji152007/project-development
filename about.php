<?php
session_start();

include 'config/db.php';

/* =========================================
   LOGIN CHECK
========================================= */

// Different session names support
if (
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['user']) &&
    !isset($_SESSION['username'])
) {
    header("Location: login.php");
    exit();
}


/* =========================================
   GET LOGGED-IN USER ID
========================================= */

$user_id = null;

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

} elseif (isset($_SESSION['user'])) {

    // If user session contains ID
    if (is_numeric($_SESSION['user'])) {
        $user_id = $_SESSION['user'];
    }

} elseif (isset($_SESSION['username'])) {

    $username = $_SESSION['username'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $user_id = $row['id'];
    }
}


/* =========================================
   GET USER DETAILS
========================================= */

if ($user_id) {

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

} else {

    $user = null;
}


/* =========================================
   USER NOT FOUND
========================================= */

if (!$user) {
    die("User profile not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>About - Personal Portfolio</title>

    <link rel="stylesheet"
          href="css/style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>


<!-- =========================================
     SIDEBAR
========================================= -->

<?php include 'sidebar.php'; ?>


<!-- =========================================
     ABOUT CONTENT
========================================= -->

<div class="content">

    <div class="dashboard-card">

        <h1>👤 About Me</h1>


        <!-- PROFILE PHOTO -->

        <?php if (!empty($user['profile_photo'])) { ?>

            <img
                src="uploads/profile/<?php echo htmlspecialchars($user['profile_photo']); ?>"
                width="150"
                height="150"
                class="profile-img"
                alt="Profile Photo"
            >

        <?php } else { ?>

            <div class="profile-img"
                 style="
                    width:150px;
                    height:150px;
                    border-radius:50%;
                    background:#1e293b;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    margin:20px auto;
                    color:#94a3b8;
                    font-size:50px;
                 ">

                <i class="fa-solid fa-user"></i>

            </div>

        <?php } ?>


        <!-- FULL NAME -->

        <p>
            <strong>Full Name:</strong>

            <?php
            echo htmlspecialchars($user['full_name'] ?? '');
            ?>

        </p>


        <!-- EDUCATION -->

        <p>
            <strong>Education:</strong>

            <?php
            echo htmlspecialchars($user['education'] ?? '');
            ?>

        </p>


        <!-- CAREER OBJECTIVE -->

        <p>
            <strong>Career Objective:</strong>

            <?php
            echo htmlspecialchars($user['career_objective'] ?? '');
            ?>

        </p>


        <!-- EMAIL -->

        <p>
            <strong>Email:</strong>

            <?php
            echo htmlspecialchars($user['email'] ?? '');
            ?>

        </p>


        <!-- PHONE -->

        <p>
            <strong>Phone:</strong>

            <?php
            echo htmlspecialchars($user['phone'] ?? '');
            ?>

        </p>


        <!-- EDIT PROFILE -->

        <a href="edit_profile.php" class="edit-btn">

            <i class="fa-solid fa-pen"></i>

            Edit Profile

        </a>

    </div>

</div>


</body>
</html>