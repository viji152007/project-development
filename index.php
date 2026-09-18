<?php
session_start();

require_once "config/db.php";

$profile_image = "";
$fullname = "Personal Portfolio Website";

/* Logged-in user details */
if (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];

    $stmt = $conn->prepare(
        "SELECT fullname, profile_photo
         FROM users
         WHERE id = ?
         LIMIT 1"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    if ($user) {

        $fullname = $user['fullname'];

        /* Photo stored as BLOB in database */
        if (!empty($user['profile_photo'])) {

            $image_info = @getimagesizefromstring(
                $user['profile_photo']
            );

            if ($image_info && isset($image_info['mime'])) {

                $profile_image =
                    "data:" .
                    $image_info['mime'] .
                    ";base64," .
                    base64_encode($user['profile_photo']);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Personal Portfolio Website</title>

    <!-- Font Awesome -->
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Existing CSS -->
    <link rel="stylesheet" href="css/style.css">

    <style>

        /* Profile Photo Round */

        .home-profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 20px auto;
            border: 4px solid white;
        }

        .home-no-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            font-size: 45px;
            color: #64748b;
            border: 4px solid white;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <!-- =========================
             PROFILE PHOTO FROM DB
             ========================= -->

        <?php if ($profile_image != ""): ?>

            <img
                src="<?php echo htmlspecialchars($profile_image); ?>"
                class="home-profile-photo"
                alt="Profile Photo"
            >

        <?php else: ?>

            <div class="home-no-photo">
                <i class="fa-solid fa-user"></i>
            </div>

        <?php endif; ?>


        <h1>

            <i class="fa-solid fa-user"></i>

            <?php
            echo htmlspecialchars($fullname);
            ?>

        </h1>


        <p class="title">

            Create your professional portfolio
            and share it with everyone.

        </p>


        <p class="text">

            New User? Create an account to build your portfolio.

        </p>


        <a href="register.php" class="btn">

            <i class="fa-solid fa-user-plus"></i>

            Register

        </a>


        <p class="text2">

            Already have an account? Login here.

        </p>


        <a href="login.php" class="btn">

            <i class="fa-solid fa-right-to-bracket"></i>

            Login

        </a>


    </div>

</div>

</body>

</html>