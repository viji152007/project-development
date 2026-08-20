<?php
session_start();

/* =========================================
   LOGIN CHECK
========================================= */

if (
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['user']) &&
    !isset($_SESSION['username'])
) {
    header("Location:login.php");
    exit();
}


/* =========================================
   DATABASE
========================================= */

require_once "config/db.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}


/* =========================================
   GET LOGGED-IN USER ID
========================================= */

$user_id = 0;

if (
    isset($_SESSION['user_id']) &&
    is_numeric($_SESSION['user_id'])
) {

    $user_id = (int)$_SESSION['user_id'];

}

elseif (
    isset($_SESSION['user']) &&
    is_numeric($_SESSION['user'])
) {

    $user_id = (int)$_SESSION['user'];

}

elseif (isset($_SESSION['username'])) {

    $username = trim($_SESSION['username']);

    $stmt = $conn->prepare("
        SELECT id
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("User query error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    if ($user) {
        $user_id = (int)$user['id'];
    }
}


/* =========================================
   USER CHECK
========================================= */

if ($user_id <= 0) {
    header("Location: login.php");
    exit();
}


/* =========================================
   MESSAGE
========================================= */

$message = "";


/* =========================================
   ADD CERTIFICATE
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $certificate_name = trim(
        $_POST['certificate_name'] ?? ''
    );

    $issuer = trim(
        $_POST['issuer'] ?? ''
    );

    $certificate_url = trim(
        $_POST['certificate_url'] ?? ''
    );


    /* =====================================
       VALIDATION
    ===================================== */

    if ($certificate_name === "") {

        $message = "Please enter certificate name.";

    }

    elseif ($issuer === "") {

        $message = "Please enter issuing organization.";

    }

    /* www / http / https validation */

    elseif (
        $certificate_url !== "" &&
        !preg_match(
            '/^(https?:\/\/|www\.)[^\s]+$/i',
            $certificate_url
        )
    ) {

        $message = "Please enter a valid certificate link.";

    }

    else {


        /* =================================
           INSERT CERTIFICATE
        ================================= */

        $stmt = $conn->prepare("
            INSERT INTO certificates
            (
                user_id,
                certificate_name,
                issuer,
                certificate_url
            )
            VALUES (?, ?, ?, ?)
        ");

        if (!$stmt) {

            $message = "Database error: " . $conn->error;

        }

        else {

            $stmt->bind_param(
                "isss",
                $user_id,
                $certificate_name,
                $issuer,
                $certificate_url
            );

            if ($stmt->execute()) {

                $stmt->close();

                header("Location:certificates.php");
                exit();

            }

            else {

                $message = "Unable to add certificate.";

                $stmt->close();

            }
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

    <title>Add Certificate</title>


    <!-- EXISTING WEBSITE CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        .certificate-page {

            width: 100%;
            max-width: 850px;
            margin: 0 auto;
            padding: 35px 30px;
            box-sizing: border-box;

        }


        .certificate-header {

            margin-bottom: 25px;

        }


        .certificate-header h2 {

            margin: 0 0 7px 0;
            font-size: 28px;

        }


        .certificate-header p {

            margin: 0;
            opacity: 0.7;

        }


        .certificate-form-card {

            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 30px;
            box-sizing: border-box;

            box-shadow:
                0 4px 18px rgba(0, 0, 0, 0.08);

            color: #1f2937;

        }


        .certificate-form-group {

            margin-bottom: 20px;

        }


        .certificate-form-group label {

            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;

        }


        .certificate-form-group input {

            width: 100%;
            height: 48px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #1f2937;
            font-size: 15px;
            outline: none;
            box-sizing: border-box;

        }


        .certificate-form-group input:focus {

            border-color: #2196f3;

            box-shadow:
                0 0 0 3px rgba(33, 150, 243, 0.10);

        }


        .certificate-form-buttons {

            display: flex;
            gap: 12px;
            margin-top: 28px;

        }


        .save-certificate-btn,
        .cancel-certificate-btn {

            min-height: 44px;
            padding: 0 20px;
            border-radius: 8px;
            text-decoration: none;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            font-weight: 600;
            cursor: pointer;
            box-sizing: border-box;

        }


        .save-certificate-btn {

            border: none;
            background: #2196f3;
            color: #ffffff;

        }


        .save-certificate-btn:hover {

            background: #1976d2;

        }


        .cancel-certificate-btn {

            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;

        }


        .cancel-certificate-btn:hover {

            background: #e5e7eb;

        }


        .certificate-error {

            margin-bottom: 20px;
            padding: 12px 14px;
            border-radius: 8px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #dc2626;
            font-size: 14px;

        }


        @media (max-width: 700px) {

            .certificate-page {

                padding: 25px 18px;

            }

            .certificate-form-card {

                padding: 22px;

            }

            .certificate-form-buttons {

                flex-direction: column;

            }

            .save-certificate-btn,
            .cancel-certificate-btn {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<!-- SIDEBAR -->

<?php include("sidebar.php"); ?>


<!-- MAIN CONTENT -->

<div class="main-content">

    <div class="certificate-page">


        <div class="certificate-header">

            <h2>Add Certificate</h2>

            <p>
                Add your professional certificates and achievements.
            </p>

        </div>


        <div class="certificate-form-card">


            <?php if ($message !== ""): ?>

                <div class="certificate-error">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                autocomplete="off"
            >


                <!-- CERTIFICATE NAME -->

                <div class="certificate-form-group">

                    <label for="certificate_name">
                        Certificate Name
                    </label>

                    <input
                        type="text"
                        id="certificate_name"
                        name="certificate_name"
                        placeholder="Enter certificate name"
                        required
                    >

                </div>


                <!-- ISSUER -->

                <div class="certificate-form-group">

                    <label for="issuer">
                        Issuing Organization
                    </label>

                    <input
                        type="text"
                        id="issuer"
                        name="issuer"
                        placeholder="Example: Salesforce, Google, Microsoft"
                        required
                    >

                </div>


                <!-- CERTIFICATE LINK -->

                <div class="certificate-form-group">

                    <label for="certificate_url">
                        Certificate Link
                    </label>

                    <input
                        type="text"
                        id="certificate_url"
                        name="certificate_url"
                        placeholder="Enter your certificate link"
                    >

                </div>


                <!-- BUTTONS -->

                <div class="certificate-form-buttons">


                    <button
                        type="submit"
                        class="save-certificate-btn"
                    >

                        <i class="fa-solid fa-plus"></i>

                        Add Certificate

                    </button>


                    <a
                        href="certificates.php"
                        class="cancel-certificate-btn"
                    >

                        <i class="fa-solid fa-arrow-left"></i>

                        Cancel

                    </a>


                </div>


            </form>


        </div>


    </div>


</div>


</body>

</html>