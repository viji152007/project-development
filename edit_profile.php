<?php

session_start();


/* =========================================================
   LOGIN CHECK
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


/* =========================================================
   DATABASE
========================================================= */

include("config/db.php");

$user_id = (int) $_SESSION['user_id'];

$message = "";
$message_type = "";


/* =========================================================
   GET USER DETAILS
========================================================= */

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

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {

    session_destroy();

    header("Location: login.php");
    exit();
}


/* =========================================================
   UPDATE PROFILE
========================================================= */

if (isset($_POST['update_profile'])) {

    $fullname =
        trim($_POST['fullname'] ?? '');

    $education =
        trim($_POST['education'] ?? '');

    $career_objective =
        trim($_POST['career_objective'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $profile_photo =
        $user['profile_photo'] ?? '';


    /* =====================================================
       FULL NAME
    ===================================================== */

    if ($fullname === "") {

        $message =
            "Please enter your full name.";

        $message_type =
            "error";
    }


    /* =====================================================
       EMAIL
    ===================================================== */

    elseif ($email === "") {

        $message =
            "Please enter your email.";

        $message_type =
            "error";
    }


    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message =
            "Please enter a valid email.";

        $message_type =
            "error";
    }


    /* =====================================================
       PHONE - EXACTLY 10 DIGITS
    ===================================================== */

    elseif (!preg_match('/^[0-9]{10}$/', $phone)) {

        $message =
            "Phone number must contain exactly 10 digits.";

        $message_type =
            "error";
    }


    /* =====================================================
       PHOTO UPLOAD
    ===================================================== */

    if (
        $message === "" &&
        isset($_FILES['profile_photo']) &&
        $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE
    ) {


        if (
            $_FILES['profile_photo']['error']
            !== UPLOAD_ERR_OK
        ) {

            $message =
                "Photo upload failed.";

            $message_type =
                "error";

        } else {


            /* =================================================
               UPLOAD DIRECTORY
            ================================================= */

            $upload_dir =
                __DIR__ . "/uploads/profile/";


            if (!is_dir($upload_dir)) {

                mkdir(
                    $upload_dir,
                    0777,
                    true
                );
            }


            /* =================================================
               FILE EXTENSION
            ================================================= */

            $extension =
                strtolower(
                    pathinfo(
                        $_FILES['profile_photo']['name'],
                        PATHINFO_EXTENSION
                    )
                );


            $allowed_extensions = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];


            if (
                !in_array(
                    $extension,
                    $allowed_extensions,
                    true
                )
            ) {

                $message =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

                $message_type =
                    "error";

            } else {


                /* =================================================
                   CHECK IMAGE
                ================================================= */

                $image_info =
                    getimagesize(
                        $_FILES['profile_photo']['tmp_name']
                    );


                if ($image_info === false) {

                    $message =
                        "Please select a valid image.";

                    $message_type =
                        "error";

                } else {


                    /* =================================================
                       CREATE NEW FILE NAME
                    ================================================= */

                    $new_filename =
                        "profile_" .
                        $user_id .
                        "_" .
                        time() .
                        "." .
                        $extension;


                    $target_file =
                        $upload_dir .
                        $new_filename;


                    /* =================================================
                       MOVE PHOTO
                    ================================================= */

                    if (
                        move_uploaded_file(
                            $_FILES['profile_photo']['tmp_name'],
                            $target_file
                        )
                    ) {


                        /* =================================================
                           DELETE OLD PHOTO
                        ================================================= */

                        if (
                            !empty($profile_photo) &&
                            file_exists(
                                $upload_dir .
                                $profile_photo
                            )
                        ) {

                            unlink(
                                $upload_dir .
                                $profile_photo
                            );
                        }


                        $profile_photo =
                            $new_filename;

                    } else {

                        $message =
                            "Unable to save the selected photo.";

                        $message_type =
                            "error";
                    }
                }
            }
        }
    }


    /* =====================================================
       DATABASE UPDATE
    ===================================================== */

    if ($message === "") {


        $update = $conn->prepare("
            UPDATE users
            SET
                fullname = ?,
                education = ?,
                career_objective = ?,
                email = ?,
                phone = ?,
                profile_photo = ?
            WHERE id = ?
        ");


        $update->bind_param(
            "ssssssi",
            $fullname,
            $education,
            $career_objective,
            $email,
            $phone,
            $profile_photo,
            $user_id
        );


        if ($update->execute()) {


            /* Update session name */

            $_SESSION['fullname'] =
                $fullname;


            /*
             * Stay on edit profile page
             * so photo/details are immediately visible.
             */

            header("Location: edit_profile.php?updated=1");
            exit();


        } else {

            $message =
                "Profile update failed.";

            $message_type =
                "error";
        }


        $update->close();
    }
}


/* =========================================================
   SUCCESS MESSAGE AFTER REDIRECT
========================================================= */

if (isset($_GET['updated'])) {

    $message =
        "Profile updated successfully.";

    $message_type =
        "success";
}


/* =========================================================
   GET UPDATED USER DETAILS
========================================================= */

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

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Profile - Personal Portfolio</title>


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

<div class="sidebar">


    <h2>

        <i class="fa-solid fa-user"></i>

        My Portfolio

    </h2>


    <a href="dashboard.php">

        <i class="fa-solid fa-house"></i>

        Home

    </a>


    <a href="about.php">

        <i class="fa-solid fa-user"></i>

        About

    </a>


    <a href="skills.php">

        <i class="fa-solid fa-code"></i>

        Skills

    </a>


    <a href="projects.php">

        <i class="fa-solid fa-folder"></i>

        Projects

    </a>


    <a href="certificates.php">

        <i class="fa-solid fa-certificate"></i>

        Certificates

    </a>


    <a href="resume.php">

        <i class="fas fa-file-alt"></i>

        Resume

    </a>


    <a href="contact.php">

        <i class="fa-solid fa-envelope"></i>

        Contact

    </a>


    <a href="change_password.php">

        <i class="fa-solid fa-lock"></i>

        Change Password

    </a>


    <a href="preview.php">

        <i class="fa-solid fa-eye"></i>

        Preview

    </a>


    <a href="logout.php">

        <i class="fa-solid fa-right-from-bracket"></i>

        Logout

    </a>


</div>



<!-- =========================================================
     CONTENT
========================================================= -->

<div class="content">


    <div class="dashboard-card edit-profile-card">


        <!-- TITLE -->

        <h1>

            <i class="fa-solid fa-user-pen"></i>

            Edit Profile

        </h1>



        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div class="<?php echo $message_type; ?>">

                <?php

                echo htmlspecialchars($message);

                ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             PROFILE PHOTO
        ================================================= -->

        <div class="profile-photo-section">


            <?php if (!empty($user['profile_photo'])): ?>


                <img
                    src="uploads/profile/<?php
                        echo htmlspecialchars(
                            $user['profile_photo']
                        );
                    ?>"
                    class="current-profile-photo"
                    alt="Profile Photo"
                >


                <label
                    for="profile_photo"
                    class="photo-label"
                >

                    <i class="fa-solid fa-camera"></i>

                    Change Photo

                </label>


            <?php else: ?>


                <label for="profile_photo">

                    <div class="no-photo">

                        <i class="fa-solid fa-camera"></i>

                        <span>Add Photo</span>

                    </div>

                </label>


                <label
                    for="profile_photo"
                    class="photo-label"
                >

                    <i class="fa-solid fa-plus"></i>

                    Add Photo

                </label>


            <?php endif; ?>


        </div>



        <!-- =================================================
             FORM
        ================================================= -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- HIDDEN FILE INPUT -->

            <input
                type="file"
                id="profile_photo"
                name="profile_photo"
                accept="image/jpeg,image/png,image/webp"
            >



            <!-- FULL NAME -->

            <div class="edit-form-group">


                <label for="fullname">

                    <i class="fa-solid fa-user"></i>

                    Full Name

                </label>


                <input
                    type="text"
                    id="fullname"
                    name="fullname"
                    value="<?php
                        echo htmlspecialchars(
                            $user['fullname'] ?? ''
                        );
                    ?>"
                    required
                >


            </div>



            <!-- EDUCATION -->

            <div class="edit-form-group">


                <label for="education">

                    <i class="fa-solid fa-graduation-cap"></i>

                    Education

                </label>


                <input
                    type="text"
                    id="education"
                    name="education"
                    value="<?php
                        echo htmlspecialchars(
                            $user['education'] ?? ''
                        );
                    ?>"
                >


            </div>



            <!-- CAREER OBJECTIVE -->

            <div class="edit-form-group">


                <label for="career_objective">

                    <i class="fa-solid fa-bullseye"></i>

                    Career Objective

                </label>


                <textarea
                    id="career_objective"
                    name="career_objective"
                ><?php
                    echo htmlspecialchars(
                        $user['career_objective'] ?? ''
                    );
                ?></textarea>


            </div>



            <!-- EMAIL -->

            <div class="edit-form-group">


                <label for="email">

                    <i class="fa-solid fa-envelope"></i>

                    Email

                </label>


                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php
                        echo htmlspecialchars(
                            $user['email'] ?? ''
                        );
                    ?>"
                    required
                >


            </div>



            <!-- PHONE -->

            <div class="edit-form-group">


                <label for="phone">

                    <i class="fa-solid fa-phone"></i>

                    Phone Number

                </label>


                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?php
                        echo htmlspecialchars(
                            $user['phone'] ?? ''
                        );
                    ?>"
                    placeholder="Enter 10 digit phone number"
                    maxlength="10"
                    minlength="10"
                    pattern="[0-9]{10}"
                    inputmode="numeric"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);"
                    required
                >


            </div>



            <!-- =================================================
                 BUTTONS
            ================================================= -->

            <div class="edit-buttons">


                <button
                    type="submit"
                    name="update_profile"
                    class="save-btn"
                >

                    <i class="fa-solid fa-rotate"></i>

                    Update Profile

                </button>


                <a
                    href="about.php"
                    class="cancel-btn"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Back

                </a>


            </div>


        </form>


    </div>


</div>


</body>

</html>