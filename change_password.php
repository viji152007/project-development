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
   GET LOGGED-IN USER
========================================================= */

$stmt = $conn->prepare("
    SELECT id, fullname, email
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
   UPDATE PASSWORD
========================================================= */

if (isset($_POST['reset_password'])) {

    $email = trim($_POST['email'] ?? '');

    $new_password =
        $_POST['new_password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';


    /* =====================================================
       EMAIL CHECK
    ===================================================== */

    if ($email === "") {

        $message = "Please enter your email.";

        $message_type = "error";

    }


    /* =====================================================
       EMAIL MATCH CHECK
    ===================================================== */

    elseif (
        strtolower($email) !==
        strtolower($user['email'])
    ) {

        $message =
            "Email does not match your account.";

        $message_type = "error";

    }


    /* =====================================================
       NEW PASSWORD CHECK
    ===================================================== */

    elseif ($new_password === "") {

        $message =
            "Please enter a new password.";

        $message_type = "error";

    }


    /* =====================================================
       PASSWORD LENGTH
    ===================================================== */

    elseif (strlen($new_password) < 6) {

        $message =
            "Password must contain at least 6 characters.";

        $message_type = "error";

    }


    /* =====================================================
       CONFIRM PASSWORD
    ===================================================== */

    elseif ($confirm_password === "") {

        $message =
            "Please confirm your password.";

        $message_type = "error";

    }


    /* =====================================================
       PASSWORD MATCH
    ===================================================== */

    elseif ($new_password !== $confirm_password) {

        $message =
            "Passwords do not match.";

        $message_type = "error";

    }


    /* =====================================================
       UPDATE
    ===================================================== */

    else {

        $hashed_password =
            password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


        $update = $conn->prepare("
            UPDATE users
            SET password = ?
            WHERE id = ?
        ");


        $update->bind_param(
            "si",
            $hashed_password,
            $user_id
        );


        if ($update->execute()) {

            $message =
                "Password updated successfully.";

            $message_type =
                "success";

        } else {

            $message =
                "Password update failed.";

            $message_type =
                "error";
        }


        $update->close();
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

    <title>Change Password - Personal Portfolio</title>


    <!-- MAIN CSS -->

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
     MAIN CONTENT
========================================================= -->

<div class="content">


    <div class="dashboard-card">


        <!-- TITLE -->

        <h1>

            <i class="fa-solid fa-lock"></i>

            Change Password

        </h1>


        <!-- MESSAGE -->

        <?php if ($message !== ""): ?>

            <div class="<?php echo htmlspecialchars($message_type); ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             FORM
        ================================================= -->

        <form
            method="POST"
            autocomplete="off"
        >


            <!-- EMAIL -->

            <div class="form-group">

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
                            $user['email']
                        );
                    ?>"
                    required
                >

            </div>


            <!-- NEW PASSWORD -->

            <div class="form-group">

                <label for="new_password">

                    <i class="fa-solid fa-key"></i>

                    New Password

                </label>


                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    placeholder="Enter new password"
                    required
                >

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm_password">

                    <i class="fa-solid fa-lock"></i>

                    Confirm Password

                </label>


                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm new password"
                    required
                >

            </div>


            <!-- BUTTONS -->

            <div class="form-actions">


                <button
                    type="submit"
                    name="reset_password"
                    class="btn-primary"
                >

                    <i class="fa-solid fa-rotate"></i>

                    Update Password

                </button>


                <a
                    href="dashboard.php"
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