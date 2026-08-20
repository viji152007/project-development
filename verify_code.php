<?php

session_start();

require_once "config/db.php";
require_once "config/mail.php";

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$message = "";


/* =====================================================
   CHECK VERIFICATION SESSION
===================================================== */

if (!isset($_SESSION["verify_email"])) {

    $message = "
        <div class='alert alert-danger'>
            Verification session expired.
        </div>
    ";

} else {

    $email = $_SESSION["verify_email"];


    /* =================================================
       VERIFY CODE
    ================================================= */

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["verify"])
    ) {

        $verification_code =
            trim($_POST["verification_code"] ?? "");


        if ($verification_code === "") {

            $message = "
                <div class='alert alert-danger'>
                    Please enter the verification code.
                </div>
            ";

        } else {

            /* =========================================
               GET USER
            ========================================= */

            $stmt = $conn->prepare(
                "SELECT
                    id,
                    username,
                    email,
                    verification_code,
                    verification_expires,
                    is_verified
                 FROM users
                 WHERE email = ?
                 LIMIT 1"
            );

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();


                /* =====================================
                   ALREADY VERIFIED
                ===================================== */

                if ((int)$user["is_verified"] === 1) {

                    $message = "
                        <div class='alert alert-success'>
                            Email already verified.
                        </div>
                    ";

                }


                /* =====================================
                   WRONG CODE
                ===================================== */

                elseif (
                    trim($verification_code) !==
                    trim($user["verification_code"])
                ) {

                    $message = "
                        <div class='alert alert-danger'>
                            Invalid verification code.
                            Please try again.
                        </div>
                    ";

                }


                /* =====================================
                   EXPIRED CODE
                ===================================== */

                elseif (
                    empty($user["verification_expires"]) ||
                    strtotime(
                        $user["verification_expires"]
                    ) < time()
                ) {

                    $message = "
                        <div class='alert alert-danger'>
                            Verification code expired.
                            Please click Resend Code.
                        </div>
                    ";

                }


                /* =====================================
                   CORRECT CODE
                ===================================== */

                else {

                    /* =================================
                       VERIFY USER
                    ================================= */

                    $update = $conn->prepare(
                        "UPDATE users
                         SET
                            is_verified = 1,
                            verification_code = NULL,
                            verification_expires = NULL
                         WHERE id = ?"
                    );

                    $update->bind_param(
                        "i",
                        $user["id"]
                    );

                    $update->execute();

                    $update->close();


                    /* =================================
                       LOGIN SESSION
                    ================================= */

                    $_SESSION["user_id"] =
                        $user["id"];

                    $_SESSION["username"] =
                        $user["username"];

                    $_SESSION["email"] =
                        $user["email"];


                    /* =================================
                       REMOVE VERIFY SESSION
                    ================================= */

                    unset(
                        $_SESSION["verify_email"]
                    );


                    /* =================================
                       DIRECT DASHBOARD
                    ================================= */

                    header(
                        "Location: dashboard.php"
                    );

                    exit();
                }

            } else {

                $message = "
                    <div class='alert alert-danger'>
                        User not found.
                    </div>
                ";
            }


            $stmt->close();
        }
    }


    /* =================================================
       RESEND CODE
    ================================================= */

    elseif (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["resend"])
    ) {


        /* =============================================
           GET USER
        ============================================= */

        $stmt = $conn->prepare(
            "SELECT
                id,
                username,
                email,
                is_verified
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        $stmt->bind_param(
            "s",
            $email
        );

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();


            /* =========================================
               CHECK ALREADY VERIFIED
            ========================================= */

            if ((int)$user["is_verified"] === 1) {

                $message = "
                    <div class='alert alert-success'>
                        Your email is already verified.
                    </div>
                ";

            } else {


                /* =====================================
                   GENERATE NEW 6 DIGIT CODE
                ===================================== */

                $new_code = str_pad(
                    random_int(0, 999999),
                    6,
                    "0",
                    STR_PAD_LEFT
                );


                /* =====================================
                   NEW EXPIRY - 10 MINUTES
                ===================================== */

                $new_expiry = date(
                    "Y-m-d H:i:s",
                    time() + (10 * 60)
                );


                /* =====================================
                   UPDATE DATABASE
                ===================================== */

                $update = $conn->prepare(
                    "UPDATE users
                     SET
                        verification_code = ?,
                        verification_expires = ?,
                        is_verified = 0
                     WHERE id = ?"
                );


                $update->bind_param(
                    "ssi",
                    $new_code,
                    $new_expiry,
                    $user["id"]
                );


                if ($update->execute()) {


                    /* =================================
                       SEND NEW EMAIL
                    ================================= */

                    $mail = new PHPMailer(true);


                    try {

                        /* SMTP */

                        $mail->isSMTP();

                        $mail->Host =
                            "smtp.gmail.com";

                        $mail->SMTPAuth = true;

                        $mail->Username =
                            $mail_username;

                        $mail->Password =
                            $mail_password;

                        $mail->SMTPSecure =
                            PHPMailer::ENCRYPTION_STARTTLS;

                        $mail->Port = 587;

                        $mail->Timeout = 15;

                        $mail->SMTPDebug = 0;


                        /* =================================
                           SENDER
                        ================================= */

                        $mail->setFrom(
                            $mail_username,
                            "Personal Portfolio"
                        );


                        /* =================================
                           RECEIVER
                        ================================= */

                        $mail->addAddress(
                            $user["email"],
                            $user["username"]
                        );


                        /* =================================
                           EMAIL
                        ================================= */

                        $mail->isHTML(true);

                        $mail->Subject =
                            "New Verification Code - Personal Portfolio";


                        $mail->Body = "

                            <div style='
                                font-family:Arial,sans-serif;
                                max-width:600px;
                                margin:auto;
                                padding:30px;
                                border:1px solid #ddd;
                                border-radius:12px;
                            '>

                                <h2 style='
                                    text-align:center;
                                    color:#2563eb;
                                '>
                                    Email Verification
                                </h2>

                                <p>
                                    Hello
                                    <strong>"
                                    . htmlspecialchars(
                                        $user["username"]
                                    )
                                    . "
                                    </strong>,
                                </p>

                                <p>
                                    You requested a new
                                    verification code.
                                </p>

                                <p>
                                    Your new verification
                                    code is:
                                </p>

                                <div style='
                                    text-align:center;
                                    margin:25px 0;
                                '>

                                    <span style='
                                        display:inline-block;
                                        font-size:32px;
                                        font-weight:bold;
                                        letter-spacing:8px;
                                        padding:15px 25px;
                                        background:#f1f5f9;
                                        border-radius:10px;
                                    '>
                                        "
                                        . $new_code .
                                        "
                                    </span>

                                </div>

                                <p>
                                    This code will expire
                                    in
                                    <strong>
                                        10 minutes
                                    </strong>.
                                </p>

                                <p>
                                    Your previous
                                    verification code
                                    is no longer valid.
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


                        /* =================================
                           PLAIN TEXT
                        ================================= */

                        $mail->AltBody =
                            "Your new verification code is: "
                            . $new_code
                            . ". This code expires in 10 minutes.";


                        /* =================================
                           SEND
                        ================================= */

                        $mail->send();


                        $message = "
                            <div class='alert alert-success'>
                                New verification code sent
                                successfully.
                            </div>
                        ";


                    } catch (Exception $e) {

                        $message = "
                            <div class='alert alert-danger'>
                                Unable to send verification
                                email. Please try again.
                            </div>
                        ";
                    }


                } else {

                    $message = "
                        <div class='alert alert-danger'>
                            Unable to generate new
                            verification code.
                        </div>
                    ";
                }


                $update->close();
            }


        } else {

            $message = "
                <div class='alert alert-danger'>
                    User not found.
                </div>
            ";
        }


        $stmt->close();
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

    <title>
        Verify Email | Personal Portfolio
    </title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- BOOTSTRAP -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- EXISTING CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body class="login-page">


<div class="card">


    <!-- =============================================
         HEADER
    ============================================== -->

    <div
        class="card-header bg-primary text-white text-center"
    >

        <h3>

            <i
                class="fa-solid fa-envelope-circle-check"
            ></i>

            Verify Your Email

        </h3>

    </div>


    <!-- =============================================
         BODY
    ============================================== -->

    <div class="card-body">


        <!-- MESSAGE -->

        <?= $message ?>


        <?php if (isset($_SESSION["verify_email"])): ?>


            <!-- EMAIL -->

            <p class="text-center">

                Verification code sent to:

                <br>

                <strong>

                    <?= htmlspecialchars(
                        $_SESSION["verify_email"]
                    ) ?>

                </strong>

            </p>


            <!-- =========================================
                 VERIFY FORM
            ========================================== -->

            <form
                method="POST"
                autocomplete="off"
            >

                <div class="mb-3">

                    <label class="form-label">

                        Verification Code

                    </label>


                    <input
                        type="text"
                        name="verification_code"
                        class="form-control text-center"
                        placeholder="Enter 6-digit code"
                        maxlength="6"
                        minlength="6"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="verify"
                    class="btn btn-primary w-100"
                >

                    <i
                        class="fa-solid fa-circle-check"
                    ></i>

                    Verify Email

                </button>

            </form>


            <!-- =========================================
                 RESEND TEXT
            ========================================== -->

            <div class="text-center mt-3">

                <span>
                    Didn't receive the code?
                </span>

            </div>


            <!-- =========================================
                 RESEND FORM
            ========================================== -->

            <form
                method="POST"
                class="mt-2"
            >

                <button
                    type="submit"
                    name="resend"
                    class="btn btn-outline-primary w-100"
                >

                    <i
                        class="fa-solid fa-rotate-right"
                    ></i>

                    Resend Code

                </button>

            </form>


        <?php endif; ?>


    </div>

</div>


</body>

</html>