<?php

session_start();

require_once "config/db.php";
require_once "config/mail.php";

/* =====================================================
   PHPMailer
===================================================== */

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$message = "";
$message_type = "";


/* =====================================================
   REGISTER
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    /* =================================================
       VALIDATION
    ================================================= */

    if (
        $username === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {

        $message = "All fields are required.";
        $message_type = "error";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    }

    elseif (strlen($username) < 3) {

        $message = "Username must contain at least 3 characters.";
        $message_type = "error";

    }

    elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $message_type = "error";

    }

    elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "error";

    }

    else {

        /* =================================================
           CHECK USERNAME OR EMAIL
        ================================================= */

        $check = $conn->prepare(
            "SELECT id, username, email, is_verified
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        $check->bind_param(
            "ss",
            $username,
            $email
        );

        $check->execute();

        $result = $check->get_result();

        $existing_user = null;

        if ($result->num_rows === 1) {
            $existing_user = $result->fetch_assoc();
        }

        $check->close();


        /* =================================================
           EXISTING USER
        ================================================= */

        if ($existing_user) {

            $existing_id = $existing_user["id"];
            $existing_username = $existing_user["username"];
            $existing_email = $existing_user["email"];
            $is_verified = (int)$existing_user["is_verified"];


            /* =============================================
               VERIFIED USER
            ============================================= */

            if ($is_verified === 1) {

                if (
                    strtolower($existing_username) ===
                    strtolower($username)
                ) {

                    $message = "Username already exists.";

                }

                elseif (
                    strtolower($existing_email) ===
                    strtolower($email)
                ) {

                    $message = "Email already exists.";

                }

                else {

                    $message = "Username or email already exists.";

                }

                $message_type = "error";

            }


            /* =============================================
               UNVERIFIED USER
            ============================================= */

            else {

                $verification_code = str_pad(
                    random_int(0, 999999),
                    6,
                    "0",
                    STR_PAD_LEFT
                );

                $verification_expires = date(
                    "Y-m-d H:i:s",
                    time() + (10 * 60)
                );

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                /* =========================================
                   UPDATE EXISTING UNVERIFIED ACCOUNT
                ========================================= */

                $update = $conn->prepare(
                    "UPDATE users
                     SET username = ?,
                         password = ?,
                         verification_code = ?,
                         verification_expires = ?,
                         is_verified = 0
                     WHERE id = ?"
                );

                $update->bind_param(
                    "ssssi",
                    $username,
                    $hashed_password,
                    $verification_code,
                    $verification_expires,
                    $existing_id
                );


                if ($update->execute()) {

                    $_SESSION["verify_email"] = $email;

                    $mail = new PHPMailer(true);

                    try {

                        $mail->isSMTP();
                        $mail->Host = "smtp.gmail.com";
                        $mail->SMTPAuth = true;
                        $mail->Username = $mail_username;
                        $mail->Password = $mail_password;
                        $mail->SMTPSecure =
                            PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;
                        $mail->Timeout = 10;
                        $mail->SMTPDebug = 0;

                        $mail->setFrom(
                            $mail_username,
                            "Personal Portfolio"
                        );

                        $mail->addAddress(
                            $email,
                            $username
                        );

                        $mail->isHTML(true);

                        $mail->Subject =
                            "Email Verification - Personal Portfolio";

                        $mail->Body = "

                            <div style='
                                font-family:Arial;
                                max-width:600px;
                                margin:auto;
                                padding:30px;
                                border:1px solid #ddd;
                                border-radius:10px;
                            '>

                                <h2 style='text-align:center;'>
                                    Email Verification
                                </h2>

                                <p>
                                    Hello
                                    <strong>"
                                    . htmlspecialchars($username)
                                    . "</strong>,
                                </p>

                                <p>
                                    Your verification code is:
                                </p>

                                <div style='
                                    text-align:center;
                                    margin:25px 0;
                                '>

                                    <span style='
                                        font-size:32px;
                                        font-weight:bold;
                                        letter-spacing:8px;
                                    '>"
                                        . $verification_code .
                                    "</span>

                                </div>

                                <p>
                                    This code will expire in
                                    <strong>10 minutes</strong>.
                                </p>

                                <hr>

                                <p style='
                                    text-align:center;
                                    color:#777;
                                '>
                                    Personal Portfolio
                                </p>

                            </div>

                        ";

                        $mail->AltBody =
                            "Your verification code is: "
                            . $verification_code .
                            ". This code expires in 10 minutes.";

                        $mail->send();

                        header(
                            "Location: verify_code.php"
                        );

                        exit();

                    }

                    catch (Exception $e) {

                        $message =
                            "Unable to send verification email.";

                        $message_type = "error";
                    }

                }

                else {

                    $message =
                        "Unable to update account.";

                    $message_type = "error";
                }

                $update->close();
            }

        }


        /* =================================================
           NEW USER
        ================================================= */

        else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $verification_code = str_pad(
                random_int(0, 999999),
                6,
                "0",
                STR_PAD_LEFT
            );

            $verification_expires = date(
                "Y-m-d H:i:s",
                time() + (10 * 60)
            );


            /* =============================================
               INSERT USER
            ============================================= */

            $stmt = $conn->prepare(
                "INSERT INTO users
                (
                    username,
                    email,
                    password,
                    verification_code,
                    verification_expires,
                    is_verified
                )
                VALUES (?, ?, ?, ?, ?, 0)"
            );


            if (!$stmt) {

                die(
                    "Database Error: " .
                    $conn->error
                );
            }


            $stmt->bind_param(
                "sssss",
                $username,
                $email,
                $hashed_password,
                $verification_code,
                $verification_expires
            );


            /* =============================================
               INSERT
            ============================================= */

            if ($stmt->execute()) {

                $_SESSION["verify_email"] = $email;

                $mail = new PHPMailer(true);

                try {

                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $mail_username;
                    $mail->Password = $mail_password;
                    $mail->SMTPSecure =
                        PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->Timeout = 10;
                    $mail->SMTPDebug = 0;


                    $mail->setFrom(
                        $mail_username,
                        "Personal Portfolio"
                    );


                    $mail->addAddress(
                        $email,
                        $username
                    );


                    $mail->isHTML(true);

                    $mail->Subject =
                        "Email Verification - Personal Portfolio";


                    $mail->Body = "

                        <div style='
                            font-family:Arial;
                            max-width:600px;
                            margin:auto;
                            padding:30px;
                            border:1px solid #ddd;
                            border-radius:10px;
                        '>

                            <h2 style='text-align:center;'>
                                Email Verification
                            </h2>

                            <p>
                                Hello
                                <strong>"
                                . htmlspecialchars($username)
                                . "</strong>,
                            </p>

                            <p>
                                Thank you for registering
                                with Personal Portfolio.
                            </p>

                            <p>
                                Your verification code is:
                            </p>

                            <div style='
                                text-align:center;
                                margin:25px 0;
                            '>

                                <span style='
                                    font-size:32px;
                                    font-weight:bold;
                                    letter-spacing:8px;
                                '>"
                                    . $verification_code .
                                "</span>

                            </div>

                            <p>
                                This code will expire in
                                <strong>10 minutes</strong>.
                            </p>

                            <p>
                                Please do not share this code
                                with anyone.
                            </p>

                            <hr>

                            <p style='
                                text-align:center;
                                color:#777;
                            '>
                                Personal Portfolio
                            </p>

                        </div>

                    ";


                    $mail->AltBody =
                        "Your verification code is: "
                        . $verification_code .
                        ". This code expires in 10 minutes.";


                    $mail->send();


                    header(
                        "Location: verify_code.php"
                    );

                    exit();


                }

                catch (Exception $e) {

                    $new_user_id = $stmt->insert_id;


                    $delete = $conn->prepare(
                        "DELETE FROM users WHERE id = ?"
                    );

                    $delete->bind_param(
                        "i",
                        $new_user_id
                    );

                    $delete->execute();
                    $delete->close();


                    unset(
                        $_SESSION["verify_email"]
                    );


                    $message =
                        "Unable to send verification email. Please try again.";

                    $message_type = "error";
                }


            }

            else {

                $message =
                    "Registration failed. Please try again.";

                $message_type = "error";
            }


            $stmt->close();
        }
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

    <title>Register | Personal Portfolio</title>


    <!-- EXISTING CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <style>

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper input {
            width: 100%;
            padding-right: 45px !important;
        }


        /* =========================================
           HIDE EDGE BROWSER DEFAULT EYE
           OUR 👁 EYE WILL REMAIN
        ========================================= */

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }


        .eye-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 18px;
            user-select: none;
            z-index: 5;
        }

    </style>

</head>


<body>


<div class="register-container">

    <div class="register-box">


        <!-- REGISTER TITLE -->

        <h1>
            👤 Register
        </h1>


        <p class="subtitle">
            Create your portfolio account
        </p>


        <!-- MESSAGE -->

        <?php if (!empty($message)): ?>

            <div
                class="message <?php echo $message_type; ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <!-- REGISTER FORM -->

        <form
            method="POST"
            action=""
        >


            <!-- USERNAME -->

            <div class="input-group">

                <input
                    type="text"
                    name="username"
                    placeholder="Enter username"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST["username"] ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="input-group">

                <input
                    type="email"
                    name="email"
                    placeholder="Enter email"
                    value="<?php
                        echo htmlspecialchars(
                            $_POST["email"] ?? ""
                        );
                    ?>"
                    required
                >

            </div>


            <!-- PASSWORD -->

            <div class="input-group">

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter password"
                        required
                    >

                    <!-- OUR EYE -->

                    <span
                        class="eye-icon"
                        onclick="togglePassword(
                            'password',
                            this
                        )"
                    >👁</span>

                </div>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="input-group">

                <div class="password-wrapper">

                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        required
                    >

                    <!-- OUR EYE -->

                    <span
                        class="eye-icon"
                        onclick="togglePassword(
                            'confirm_password',
                            this
                        )"
                    >👁</span>

                </div>

            </div>


            <!-- REGISTER BUTTON -->

            <button type="submit">
                Register
            </button>


        </form>


        <!-- LOGIN -->

        <p class="login-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>


    </div>

</div>


<script>

function togglePassword(inputId, eye) {

    const input =
        document.getElementById(inputId);

    if (input.type === "password") {

        input.type = "text";

    } else {

        input.type = "password";

    }

}

</script>


</body>

</html>