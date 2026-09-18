<?php

session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "config/db.php";


/* =========================================================
   CHECK LOGIN
========================================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$user_id = (int) $_SESSION['user_id'];

$message = "";
$message_type = "";


/* =========================================================
   GET USER DATA
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

if (!$stmt) {
    die("Database Error: " . $conn->error);
}

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

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_profile"])
) {

    $fullname = trim($_POST["fullname"] ?? "");
    $education = trim($_POST["education"] ?? "");
    $career_objective = trim($_POST["career_objective"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");


    /* =====================================================
       VALIDATION
    ===================================================== */

    if ($fullname === "") {

        $message = "Please enter your full name.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email.";
        $message_type = "error";

    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {

        $message = "Phone number must contain exactly 10 digits.";
        $message_type = "error";
    }


    /* =====================================================
       PHOTO PROCESSING
    ===================================================== */

    $new_photo = false;
    $image_data = null;


    if (
        $message === "" &&
        isset($_FILES["profile_photo"]) &&
        $_FILES["profile_photo"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES["profile_photo"]["error"] !== UPLOAD_ERR_OK
        ) {

            $message = "Photo upload failed.";
            $message_type = "error";

        } else {

            $tmp_name = $_FILES["profile_photo"]["tmp_name"];

            $image_info = @getimagesize($tmp_name);


            if ($image_info === false) {

                $message = "Please select a valid image.";
                $message_type = "error";

            } else {

                $allowed_types = [
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ];


                if (
                    !in_array(
                        $image_info["mime"],
                        $allowed_types,
                        true
                    )
                ) {

                    $message =
                        "Only JPG, PNG and WEBP images are allowed.";

                    $message_type = "error";

                } else {

                    $image_data = file_get_contents($tmp_name);


                    if ($image_data === false) {

                        $message =
                            "Unable to read selected photo.";

                        $message_type = "error";

                    } else {

                        $new_photo = true;
                    }
                }
            }
        }
    }


    /* =====================================================
       UPDATE DATABASE
    ===================================================== */

    if ($message === "") {


        /* =================================================
           UPDATE WITH NEW PHOTO
        ================================================= */

        if ($new_photo) {

            $stmt = $conn->prepare("
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


            if (!$stmt) {
                die("Database Error: " . $conn->error);
            }


            $stmt->bind_param(
                "ssssssi",
                $fullname,
                $education,
                $career_objective,
                $email,
                $phone,
                $image_data,
                $user_id
            );
        }


        /* =================================================
           UPDATE WITHOUT PHOTO
        ================================================= */

        else {

            $stmt = $conn->prepare("
                UPDATE users
                SET
                    fullname = ?,
                    education = ?,
                    career_objective = ?,
                    email = ?,
                    phone = ?
                WHERE id = ?
            ");


            if (!$stmt) {
                die("Database Error: " . $conn->error);
            }


            $stmt->bind_param(
                "sssssi",
                $fullname,
                $education,
                $career_objective,
                $email,
                $phone,
                $user_id
            );
        }


        /* =================================================
           EXECUTE
        ================================================= */

        if ($stmt->execute()) {

            $stmt->close();

            $_SESSION["fullname"] = $fullname;

            header("Location: edit_profile.php?updated=1");

            exit();

        } else {

            $message =
                "Update Error: " . $stmt->error;

            $message_type = "error";

            $stmt->close();
        }
    }
}


/* =========================================================
   SUCCESS MESSAGE
========================================================= */

if (isset($_GET["updated"])) {

    $message = "Profile updated successfully.";

    $message_type = "success";
}


/* =========================================================
   GET LATEST DATA
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


if (!$stmt) {
    die("Database Error: " . $conn->error);
}


$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   DB PHOTO → BASE64
========================================================= */

$profile_image = "";


if (
    isset($user["profile_photo"]) &&
    $user["profile_photo"] !== null &&
    strlen($user["profile_photo"]) > 0
) {

    $image_info = @getimagesizefromstring(
        $user["profile_photo"]
    );


    if (
        $image_info !== false &&
        isset($image_info["mime"])
    ) {

        $profile_image =
            "data:" .
            $image_info["mime"] .
            ";base64," .
            base64_encode(
                $user["profile_photo"]
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

    <title>Edit Profile</title>


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


<?php include "sidebar.php"; ?>


<div class="content">


    <div class="dashboard-card edit-profile-card">


        <!-- TITLE -->

        <h1>

            <i class="fa-solid fa-user-pen"></i>

            Edit Profile

        </h1>


        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div class="<?php echo htmlspecialchars($message_type); ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- PROFILE PHOTO -->

        <div class="profile-photo-section">


            <?php if ($profile_image !== ""): ?>

                <img
                    id="photoPreview"
                    src="<?php echo htmlspecialchars($profile_image); ?>"
                    class="current-profile-photo"
                    alt="Profile Photo"
                >


            <?php else: ?>

                <div
                    id="noPhoto"
                    class="no-photo"
                >

                    <i class="fa-solid fa-user"></i>

                    <span>No Photo</span>

                </div>

            <?php endif; ?>


            <!-- CHOOSE PHOTO -->

            <label
                for="profile_photo"
                class="photo-label"
            >

                <i class="fa-solid fa-camera"></i>

                Choose Photo

            </label>


        </div>


        <!-- FORM -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- PHOTO INPUT -->

            <input
                type="file"
                id="profile_photo"
                name="profile_photo"
                accept="image/jpeg,image/png,image/webp"
            >


            <!-- FULL NAME -->

            <div class="edit-form-group">

                <label>

                    <i class="fa-solid fa-user"></i>

                    Full Name

                </label>


                <input
                    type="text"
                    name="fullname"
                    value="<?php
                        echo htmlspecialchars(
                            $user["fullname"] ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <!-- EDUCATION -->

            <div class="edit-form-group">

                <label>

                    <i class="fa-solid fa-graduation-cap"></i>

                    Education

                </label>


                <input
                    type="text"
                    name="education"
                    value="<?php
                        echo htmlspecialchars(
                            $user["education"] ?? ""
                        );
                    ?>"
                >

            </div>


            <!-- CAREER OBJECTIVE -->

            <div class="edit-form-group">

                <label>

                    <i class="fa-solid fa-bullseye"></i>

                    Career Objective

                </label>


                <textarea
                    name="career_objective"
                ><?php
                    echo htmlspecialchars(
                        $user["career_objective"] ?? ""
                    );
                ?></textarea>

            </div>


            <!-- EMAIL -->

            <div class="edit-form-group">

                <label>

                    <i class="fa-solid fa-envelope"></i>

                    Email

                </label>


                <input
                    type="email"
                    name="email"
                    value="<?php
                        echo htmlspecialchars(
                            $user["email"] ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <!-- PHONE -->

            <div class="edit-form-group">

                <label>

                    <i class="fa-solid fa-phone"></i>

                    Phone Number

                </label>


                <input
                    type="tel"
                    name="phone"
                    value="<?php
                        echo htmlspecialchars(
                            $user["phone"] ?? ""
                        );
                    ?>"
                    maxlength="10"
                    minlength="10"
                    pattern="[0-9]{10}"
                    inputmode="numeric"
                    required
                    oninput="
                        this.value =
                        this.value
                        .replace(/[^0-9]/g, '')
                        .slice(0, 10);
                    "
                >

            </div>


            <!-- BUTTONS -->

            <div class="edit-buttons">


                <!-- UPDATE -->

                <button
                    type="submit"
                    name="update_profile"
                >

                    <i class="fa-solid fa-rotate"></i>

                    Update Profile

                </button>


                <!-- BACK → ABOUT -->

                <a href="about.php">

                    <i class="fa-solid fa-arrow-left"></i>

                    Back

                </a>


            </div>


        </form>


    </div>


</div>


<!-- PHOTO PREVIEW -->

<script>

const photoInput =
    document.getElementById("profile_photo");


let photoPreview =
    document.getElementById("photoPreview");


const noPhoto =
    document.getElementById("noPhoto");


photoInput.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) {
        return;
    }


    const reader = new FileReader();


    reader.onload = function (event) {


        if (photoPreview) {

            photoPreview.src =
                event.target.result;

            photoPreview.style.display =
                "block";

        } else {

            const newImage =
                document.createElement("img");

            newImage.id =
                "photoPreview";

            newImage.className =
                "current-profile-photo";

            newImage.alt =
                "Profile Photo";

            newImage.src =
                event.target.result;


            if (noPhoto) {

                noPhoto.parentNode.insertBefore(
                    newImage,
                    noPhoto
                );

                noPhoto.style.display =
                    "none";
            }


            photoPreview = newImage;
        }


        if (noPhoto) {

            noPhoto.style.display =
                "none";
        }

    };


    reader.readAsDataURL(file);

});

</script>


</body>

</html>