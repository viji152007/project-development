<?php
session_start();

include("config/db.php");

/* =========================================================
   LOGIN
========================================================= */

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /* =====================================================
       EMPTY FIELD CHECK
    ===================================================== */

    if ($email === "" || $password === "") {

        $message = "Please enter email and password.";
        $message_type = "error";

    } else {

        /* =================================================
           FIND USER BY EMAIL
        ================================================= */

        $stmt = $conn->prepare("
            SELECT
                id,
                username,
                fullname,
                email,
                password
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        /* =================================================
           USER NOT FOUND
        ================================================= */

        if ($result->num_rows === 0) {

            $message = "Invalid email or password.";
            $message_type = "error";

        } else {

            $user = $result->fetch_assoc();

            /* =============================================
               PASSWORD CHECK
            ============================================= */

            if (password_verify($password, $user['password'])) {

                /* =========================================
                   LOGIN SUCCESS
                ========================================= */

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];

                /* =========================================
                   DASHBOARD
                ========================================= */

                header("Location: dashboard.php");
                exit();

            } else {

                $message = "Invalid email or password.";
                $message_type = "error";
            }
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

    <title>Login - Portfolio</title>

    <!-- EXISTING THEME -->
    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <style>

        /* =====================================================
           LOGIN PAGE
           Existing website theme will remain unchanged
        ===================================================== */

        body.login-page {

            min-height: 100vh !important;

            margin: 0 !important;

            padding: 30px 20px !important;

            display: flex !important;

            align-items: center !important;

            justify-content: center !important;

            box-sizing: border-box !important;
        }


        .login-wrapper {

            width: 100% !important;

            min-height: calc(100vh - 60px) !important;

            display: flex !important;

            align-items: center !important;

            justify-content: center !important;
        }


        /* =====================================================
           LOGIN CARD ONLY
        ===================================================== */

        .login-card {

            width: 100% !important;

            max-width: 440px !important;

            margin: 0 auto !important;

            position: relative !important;

            left: auto !important;

            right: auto !important;

            top: auto !important;

            bottom: auto !important;

            flex: none !important;

            background: #ffffff !important;

            border: 1px solid #e5e7eb !important;

            border-radius: 20px !important;

            overflow: hidden !important;

            box-shadow:
                0 15px 40px rgba(0, 0, 0, 0.10) !important;
        }


        /* =====================================================
           LOGIN HEADER
        ===================================================== */

        .login-card .card-header {

            width: 100% !important;

            padding: 28px 30px !important;

            text-align: center !important;

            background: #ffffff !important;

            border-bottom: 1px solid #eeeeee !important;

            box-sizing: border-box !important;
        }


        .login-card .card-header h3 {

            margin: 0 !important;

            color: #111827 !important;

            font-size: 26px !important;

            font-weight: 700 !important;
        }


        /* =====================================================
           LOGIN BODY
        ===================================================== */

        .login-card .card-body {

            width: 100% !important;

            padding: 32px !important;

            background: #ffffff !important;

            box-sizing: border-box !important;
        }


        /* =====================================================
           FORM
        ===================================================== */

        .login-form {

            width: 100% !important;
        }


        .login-form .form-group {

            width: 100% !important;

            margin-bottom: 20px !important;
        }


        .login-form .form-label {

            display: block !important;

            margin-bottom: 8px !important;

            color: #111827 !important;

            font-weight: 600 !important;
        }


        .login-form .form-control {

            width: 100% !important;

            height: 48px !important;

            padding: 10px 14px !important;

            box-sizing: border-box !important;

            background: #ffffff !important;

            border: 1px solid #d1d5db !important;

            border-radius: 9px !important;

            outline: none !important;
        }


        .login-form .form-control:focus {

            border-color: #2563eb !important;

            box-shadow:
                0 0 0 3px rgba(37, 99, 235, 0.10) !important;
        }


        /* =====================================================
           LOGIN BUTTON
        ===================================================== */

        .login-button-box {

            width: 100% !important;

            margin-top: 8px !important;
        }


        .login-button-box .btn-primary {

            display: block !important;

            width: 100% !important;

            height: 48px !important;

            margin: 0 !important;

            border-radius: 9px !important;

            cursor: pointer !important;
        }


        /* =====================================================
           ERROR MESSAGE
        ===================================================== */

        .login-message {

            width: 100% !important;

            padding: 11px 13px !important;

            margin-bottom: 20px !important;

            border-radius: 8px !important;

            box-sizing: border-box !important;

            text-align: center !important;

            font-size: 14px !important;
        }


        .login-message.error {

            color: #dc2626 !important;

            background: rgba(220, 38, 38, 0.08) !important;

            border: 1px solid rgba(220, 38, 38, 0.15) !important;
        }


        /* =====================================================
           OR DIVIDER
        ===================================================== */

        .login-divider {

            width: 100% !important;

            display: flex !important;

            align-items: center !important;

            gap: 12px !important;

            margin: 25px 0 !important;
        }


        .login-divider span {

            flex: 1 !important;

            height: 1px !important;

            background: #d1d5db !important;
        }


        .login-divider small {

            color: #6b7280 !important;

            font-weight: 600 !important;
        }


        /* =====================================================
           REGISTER
        ===================================================== */

        .register-link {

            width: 100% !important;

            text-align: center !important;
        }


        .register-link p {

            margin: 0 0 6px !important;

            color: #6b7280 !important;
        }


        .register-link a {

            color: #2563eb !important;

            text-decoration: none !important;

            font-weight: 600 !important;
        }


        .register-link a:hover {

            text-decoration: underline !important;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 500px) {

            body.login-page {

                padding: 20px 15px !important;
            }


            .login-wrapper {

                min-height: calc(100vh - 40px) !important;
            }


            .login-card {

                max-width: 100% !important;

                border-radius: 16px !important;
            }


            .login-card .card-header {

                padding: 24px 20px !important;
            }


            .login-card .card-body {

                padding: 25px 20px !important;
            }
        }

    </style>

</head>


<body class="login-page">


<div class="login-wrapper">


    <div class="card login-card">


        <!-- HEADER -->

        <div class="card-header">

            <h3>Login</h3>

        </div>


        <!-- BODY -->

        <div class="card-body">


            <!-- ERROR MESSAGE -->

            <?php if ($message !== ""): ?>

                <div class="login-message <?= htmlspecialchars($message_type); ?>">

                    <?= htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <!-- LOGIN FORM -->

            <form
                method="POST"
                class="login-form"
                autocomplete="off"
            >

                <!-- Fake fields to reduce browser autofill -->

                <input
                    type="text"
                    name="fake_username"
                    style="display:none"
                    tabindex="-1"
                    autocomplete="off"
                >

                <input
                    type="password"
                    name="fake_password"
                    style="display:none"
                    tabindex="-1"
                    autocomplete="new-password"
                >


                <!-- EMAIL -->

                <div class="form-group">

                    <label
                        class="form-label"
                        for="login_email"
                    >
                        Email
                    </label>

                    <input
                        type="email"
                        id="login_email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
                        autocomplete="off"
                        autocorrect="off"
                        autocapitalize="none"
                        spellcheck="false"
                        readonly
                        onfocus="this.removeAttribute('readonly');"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label
                        class="form-label"
                        for="login_password"
                    >
                        Password
                    </label>

                    <input
                        type="password"
                        id="login_password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        autocomplete="new-password"
                        readonly
                        onfocus="this.removeAttribute('readonly');"
                        required
                    >

                </div>


                <!-- LOGIN BUTTON -->

                <div class="login-button-box">

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        Login
                    </button>

                </div>

            </form>


            <!-- OR -->

            <div class="login-divider">

                <span></span>

                <small>OR</small>

                <span></span>

            </div>


            <!-- REGISTER -->

            <div class="register-link">

                <p>
                    Don't have an account?
                </p>

                <a href="register.php">
                    Create New Account
                </a>

            </div>


        </div>

    </div>

</div>


</body>

</html>