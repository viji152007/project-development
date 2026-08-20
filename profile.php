<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";


/* =========================
   PROFILE PHOTO UPLOAD
   ========================= */

if (isset($_POST['upload_photo'])) {

    if (
        isset($_FILES['profile_photo']) &&
        $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK
    ) {

        $file = $_FILES['profile_photo'];

        $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];

        $extension = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (!in_array($extension, $allowed_types)) {

            $message = "Only JPG, JPEG, PNG and WEBP images are allowed.";

        } else {

            $image_data = file_get_contents($file['tmp_name']);

            if ($image_data === false) {

                $message = "Unable to read the image.";

            } else {

                /* Store actual image in database */

                $stmt = $conn->prepare(
                    "UPDATE users
                     SET profile_photo = ?
                     WHERE id = ?"
                );

                $null = NULL;

                $stmt->bind_param(
                    "bi",
                    $null,
                    $user_id
                );

                $stmt->send_long_data(
                    0,
                    $image_data
                );

                if ($stmt->execute()) {

                    $message = "Profile photo updated successfully.";

                } else {

                    $message = "Photo upload failed.";
                }

                $stmt->close();
            }
        }

    } else {

        $message = "Please select a photo.";
    }
}


/* =========================
   GET CURRENT USER
   ========================= */

$stmt = $conn->prepare(
    "SELECT fullname, username, email, profile_photo
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


/* =========================
   DISPLAY DATABASE IMAGE
   ========================= */

$profile_image = "";

if (
    isset($user['profile_photo']) &&
    !empty($user['profile_photo'])
) {

    $image_info = @getimagesizefromstring(
        $user['profile_photo']
    );

    if ($image_info !== false) {

        $mime_type = $image_info['mime'];

        $profile_image =
            "data:" .
            $mime_type .
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Profile</title>

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <link rel="stylesheet"
          href="css/style.css">

</head>


<body>


<div class="dashboard-layout">


    <!-- =========================
         SIDEBAR
         ========================= -->

    <aside class="sidebar">

        <h2>My Portfolio</h2>

        <ul>

            <li>
                <a href="profile.php">

                    <i class="fas fa-user-circle"></i>

                    My Profile

                </a>
            </li>


            <li>
                <a href="about.php">

                    <i class="fas fa-user"></i>

                    About

                </a>
            </li>


            <li>
                <a href="admin/skills.php">

                    <i class="fas fa-code"></i>

                    Skills

                </a>
            </li>


            <li>
                <a href="admin/projects/projects.php">

                    <i class="fas fa-folder"></i>

                    Projects

                </a>
            </li>


            <li>
                <a href="certificate.php">

                    <i class="fas fa-certificate"></i>

                    Certificates

                </a>
            </li>


            <li>
                <a href="resume.php">

                    <i class="fas fa-file"></i>

                    Resume

                </a>
            </li>


            <li>
                <a href="contact.php">

                    <i class="fas fa-envelope"></i>

                    Contact

                </a>
            </li>


            <li>
                <a href="preview.php">

                    <i class="fas fa-eye"></i>

                    Preview

                </a>
            </li>


            <li>
                <a href="logout.php">

                    <i class="fas fa-sign-out-alt"></i>

                    Logout

                </a>
            </li>

        </ul>

    </aside>


    <!-- =========================
         PROFILE CONTENT
         ========================= -->

    <main class="dashboard-main">


        <div class="profile-container">


            <h1>

                <i class="fa-solid fa-user-circle"></i>

                My Profile

            </h1>


            <?php if ($message != ""): ?>

                <div class="profile-message">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <!-- PROFILE PHOTO -->

            <div class="profile-placeholder">

                <?php if ($profile_image != ""): ?>

                    <img
                        src="<?php echo $profile_image; ?>"
                        alt="Profile Photo">

                <?php else: ?>

                    <i class="fa-solid fa-user"></i>

                <?php endif; ?>

            </div>


            <!-- UPLOAD PHOTO -->

            <form
                method="POST"
                enctype="multipart/form-data">

                <input
                    type="file"
                    name="profile_photo"
                    accept=".jpg,.jpeg,.png,.webp"
                    required>

                <br><br>

                <button
                    type="submit"
                    name="upload_photo"
                    class="profile-upload-btn">

                    <i class="fa-solid fa-camera"></i>

                    Upload Profile Photo

                </button>

            </form>


            <!-- USER DETAILS -->

            <div class="profile-details">


                <p>

                    <strong>Full Name:</strong>

                    <?php
                    echo htmlspecialchars(
                        $user['fullname']
                    );
                    ?>

                </p>


                <p>

                    <strong>Username:</strong>

                    <?php
                    echo htmlspecialchars(
                        $user['username']
                    );
                    ?>

                </p>


                <p>

                    <strong>Email:</strong>

                    <?php
                    echo htmlspecialchars(
                        $user['email']
                    );
                    ?>

                </p>


            </div>


        </div>


    </main>


</div>


</body>

</html>