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

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/* =====================================================
   DATABASE CONNECTION
===================================================== */

require_once "config/db.php";


/* =====================================================
   GET LOGGED-IN USER
===================================================== */

$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare(
    "SELECT *
     FROM users
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


/* =====================================================
   USER NOT FOUND
===================================================== */

if (!$user) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit();
}


/* =====================================================
   USER DATA
===================================================== */

$fullname = $user['fullname'] ?? "User";
$email = $user['email'] ?? "";
$profile_photo = $user['profile_photo'] ?? "";


/* =====================================================
   PROFILE IMAGE
===================================================== */

$profile_image = "";

if (!empty($profile_photo)) {

    $image_info = @getimagesizefromstring($profile_photo);

    if ($image_info && !empty($image_info['mime'])) {

        $profile_image =
            "data:" .
            $image_info['mime'] .
            ";base64," .
            base64_encode($profile_photo);
    }
}


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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard | Personal Portfolio</title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


    <!-- MAIN CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <style>

        .dashboard-profile-img {

            width: 140px !important;
            height: 140px !important;

            border-radius: 50% !important;

            object-fit: cover !important;

            display: block !important;

            margin: 0 auto 20px auto !important;

            border: 4px solid #ffffff !important;

            box-shadow:
                0 5px 15px rgba(0, 0, 0, 0.15) !important;
        }


        .dashboard-default-profile {

            width: 140px !important;
            height: 140px !important;

            border-radius: 50% !important;

            display: flex !important;

            align-items: center !important;
            justify-content: center !important;

            margin: 0 auto 20px auto !important;

            background: #e5e7eb !important;

            color: #64748b !important;

            font-size: 50px !important;

            border: 4px solid #ffffff !important;
        }

    </style>

</head>


<body>


<!-- =====================================================
     SUCCESS MESSAGE
===================================================== -->

<?php if ($successMsg !== "") { ?>

    <div class="success-message">

        <i class="fa-solid fa-circle-check"></i>

        <?php echo htmlspecialchars($successMsg); ?>

    </div>

<?php } ?>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<?php include "sidebar.php"; ?>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="content">

    <div class="dashboard-card">


        <!-- PROFILE PHOTO -->

        <div class="dashboard-profile">

            <?php if ($profile_image !== "") { ?>

                <img
                    src="<?php echo htmlspecialchars($profile_image); ?>"
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

        <?php if ($email !== "") { ?>

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