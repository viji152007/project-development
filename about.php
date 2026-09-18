<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config/db.php";


/* =========================================================
   LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['user']) &&
    !isset($_SESSION['username'])
) {
    header("Location: login.php");
    exit();
}


/* =========================================================
   GET LOGGED-IN USER ID
========================================================= */

$user_id = null;


/* user_id session */

if (isset($_SESSION['user_id'])) {

    $user_id = (int) $_SESSION['user_id'];

}


/* user session */

elseif (isset($_SESSION['user'])) {

    if (is_numeric($_SESSION['user'])) {

        $user_id = (int) $_SESSION['user'];

    } else {

        $username = $_SESSION['user'];

        $stmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        if (!$stmt) {
            die("Database Error: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();

        if ($row) {
            $user_id = (int) $row['id'];
        }
    }
}


/* username session */

elseif (isset($_SESSION['username'])) {

    $username = $_SESSION['username'];

    $stmt = $conn->prepare("
        SELECT id
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Database Error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    if ($row) {
        $user_id = (int) $row['id'];
    }
}


/* =========================================================
   GET USER DETAILS INCLUDING DB PHOTO
========================================================= */

if ($user_id !== null) {

    $stmt = $conn->prepare("
        SELECT
            id,
            fullname,
            education,
            career_objective,
            username,
            email,
            phone,
            profile_photo
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Database Error: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

} else {

    $user = null;
}


/* =========================================================
   USER NOT FOUND
========================================================= */

if (!$user) {

    die("User profile not found.");

}


/* =========================================================
   DB BLOB PHOTO → BASE64 IMAGE
========================================================= */

$profile_image = "";


if (
    isset($user['profile_photo']) &&
    $user['profile_photo'] !== null &&
    strlen($user['profile_photo']) > 0
) {

    $image_info = @getimagesizefromstring(
        $user['profile_photo']
    );


    if (
        $image_info !== false &&
        isset($image_info['mime'])
    ) {

        $profile_image =
            "data:" .
            $image_info['mime'] .
            ";base64," .
            base64_encode(
                $user['profile_photo']
            );
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>About - Personal Portfolio</title>


    <!-- EXISTING CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<?php include 'sidebar.php'; ?>


<!-- =========================================================
     ABOUT CONTENT
========================================================= -->

<div class="content">


    <div class="dashboard-card">


        <!-- =================================================
             TITLE
        ================================================= -->

        <h1>

            <i class="fa-solid fa-user"></i>

            About Me

        </h1>


        <!-- =================================================
             PROFILE PHOTO
        ================================================= -->

        <?php if ($profile_image !== ""): ?>


            <!-- DB PHOTO -->

            <img
                src="<?php echo htmlspecialchars($profile_image); ?>"
                width="150"
                height="150"
                class="profile-img"
                alt="Profile Photo"
            >


        <?php else: ?>


            <!-- NO PHOTO -->

            <div
                class="profile-img"
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
                "
            >

                <i class="fa-solid fa-user"></i>

            </div>


        <?php endif; ?>


        <!-- =================================================
             FULL NAME
        ================================================= -->

        <p>

            <strong>Full Name:</strong>

            <?php
            echo htmlspecialchars(
                $user['fullname'] ?? ''
            );
            ?>

        </p>


        <!-- =================================================
             EDUCATION
        ================================================= -->

        <p>

            <strong>Education:</strong>

            <?php
            echo htmlspecialchars(
                $user['education'] ?? ''
            );
            ?>

        </p>


        <!-- =================================================
             CAREER OBJECTIVE
        ================================================= -->

        <p>

            <strong>Career Objective:</strong>

            <?php
            echo htmlspecialchars(
                $user['career_objective'] ?? ''
            );
            ?>

        </p>


        <!-- =================================================
             EMAIL
        ================================================= -->

        <p>

            <strong>Email:</strong>

            <?php
            echo htmlspecialchars(
                $user['email'] ?? ''
            );
            ?>

        </p>


        <!-- =================================================
             PHONE
        ================================================= -->

        <p>

            <strong>Phone:</strong>

            <?php
            echo htmlspecialchars(
                $user['phone'] ?? ''
            );
            ?>

        </p>


        <!-- =================================================
             EDIT PROFILE
        ================================================= -->

        <a
            href="edit_profile.php"
            class="edit-btn"
        >

            <i class="fa-solid fa-pen"></i>

            Edit Profile

        </a>


    </div>


</div>


</body>

</html>