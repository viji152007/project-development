<?php

session_start();


/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['username'])) {

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
   GET LOGGED-IN USER
========================================= */

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


if (!$user) {

    die("User not found.");

}


$user_id = (int)$user['id'];


/* =========================================
   GET CERTIFICATE ID
========================================= */

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
) {

    header("Location: certificates.php");

    exit();

}


$certificate_id = (int)$_GET['id'];


if ($certificate_id <= 0) {

    header("Location: certificates.php");

    exit();

}


/* =========================================
   GET CERTIFICATE
   ONLY LOGGED-IN USER'S CERTIFICATE
========================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        certificate_name,
        issuer,
        certificate_url,
        created_at
    FROM certificates
    WHERE id = ?
      AND user_id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Certificate query error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "ii",
    $certificate_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$certificate = $result->fetch_assoc();

$stmt->close();


/* =========================================
   CERTIFICATE NOT FOUND
========================================= */

if (!$certificate) {

    die("Certificate not found.");

}


/* =========================================
   VARIABLES
========================================= */

$error = "";

$success = "";


/* =========================================
   UPDATE CERTIFICATE
========================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_certificate'])
) {


    /* -----------------------------------------
       GET FORM VALUES
    ----------------------------------------- */

    $certificate_name =
        trim(
            $_POST['certificate_name'] ?? ''
        );


    $issuer =
        trim(
            $_POST['issuer'] ?? ''
        );


    $certificate_url =
        trim(
            $_POST['certificate_url'] ?? ''
        );


    /* -----------------------------------------
       VALIDATE NAME
    ----------------------------------------- */

    if ($certificate_name === '') {

        $error =
            "Certificate name is required.";

    }


    /* -----------------------------------------
       VALIDATE URL
    ----------------------------------------- */

    if (
        $error === '' &&
        $certificate_url !== ''
    ) {

        if (
            !filter_var(
                $certificate_url,
                FILTER_VALIDATE_URL
            )
        ) {

            $error =
                "Please enter a valid certificate URL.";

        }

    }


    /* -----------------------------------------
       UPDATE DATABASE
    ----------------------------------------- */

    if ($error === '') {


        $stmt = $conn->prepare("
            UPDATE certificates
            SET
                certificate_name = ?,
                issuer = ?,
                certificate_url = ?
            WHERE id = ?
              AND user_id = ?
        ");


        if (!$stmt) {

            $error =
                "Update query error: " .
                $conn->error;

        } else {


            $stmt->bind_param(
                "sssii",
                $certificate_name,
                $issuer,
                $certificate_url,
                $certificate_id,
                $user_id
            );


            if ($stmt->execute()) {


                /* -----------------------------
                   UPDATE DISPLAY DATA
                ----------------------------- */

                $certificate[
                    'certificate_name'
                ] = $certificate_name;


                $certificate[
                    'issuer'
                ] = $issuer;


                $certificate[
                    'certificate_url'
                ] = $certificate_url;


                $success =
                    "Certificate updated successfully.";


            } else {

                $error =
                    "Failed to update certificate. Please try again.";

            }


            $stmt->close();

        }

    }

}


/* =========================================
   DELETE CERTIFICATE
========================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_certificate'])
) {


    /* -----------------------------------------
       DELETE ONLY CURRENT USER'S CERTIFICATE
    ----------------------------------------- */

    $stmt = $conn->prepare("
        DELETE FROM certificates
        WHERE id = ?
          AND user_id = ?
    ");


    if (!$stmt) {

        die(
            "Delete query error: " .
            $conn->error
        );

    }


    $stmt->bind_param(
        "ii",
        $certificate_id,
        $user_id
    );


    if ($stmt->execute()) {


        $stmt->close();


        /* -----------------------------
           GO BACK TO CERTIFICATES
        ----------------------------- */

        header(
            "Location: certificates.php?deleted=1"
        );

        exit();


    } else {

        $error =
            "Failed to delete certificate. Please try again.";

    }


    $stmt->close();

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
        Edit Certificate
    </title>


    <!-- =========================================
         EXISTING CSS
    ========================================== -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- =========================================
         FONT AWESOME
    ========================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>


        /* =========================================
           EDIT CERTIFICATE PAGE
        ========================================= */

        .edit-certificate-page {

            width: 100%;

            max-width: 900px;

            margin: 0 auto;

            padding: 35px 30px;

            box-sizing: border-box;

        }


        /* =========================================
           HEADER
        ========================================= */

        .edit-certificate-header {

            margin-bottom: 25px;

        }


        .edit-certificate-header h2 {

            margin: 0 0 7px 0;

            font-size: 28px;

        }


        .edit-certificate-header p {

            margin: 0;

            opacity: 0.7;

        }


        /* =========================================
           CARD
        ========================================= */

        .edit-certificate-card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            padding: 30px;

            box-sizing: border-box;

            box-shadow:
                0 4px 15px rgba(
                    0,
                    0,
                    0,
                    0.08
                );

        }


        /* =========================================
           FORM GROUP
        ========================================= */

        .certificate-form-group {

            margin-bottom: 22px;

        }


        .certificate-form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: 600;

            color: #374151;

        }


        .certificate-form-group label span {

            color: #dc2626;

        }


        .certificate-form-group input {

            width: 100%;

            padding: 12px 14px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            font-size: 15px;

            color: #1f2937;

            background: #ffffff;

            outline: none;

            box-sizing: border-box;

            transition: 0.3s;

        }


        .certificate-form-group input:focus {

            border-color: #2196f3;

            box-shadow:
                0 0 0 3px
                rgba(
                    33,
                    150,
                    243,
                    0.10
                );

        }


        .certificate-form-group small {

            display: block;

            margin-top: 6px;

            font-size: 12px;

            color: #6b7280;

        }


        /* =========================================
           ALERT
        ========================================== */

        .certificate-alert {

            padding: 12px 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 14px;

        }


        .certificate-error {

            background: #fef2f2;

            color: #b91c1c;

            border: 1px solid #fecaca;

        }


        .certificate-success {

            background: #f0fdf4;

            color: #15803d;

            border: 1px solid #bbf7d0;

        }


        /* =========================================
           ACTION AREA
        ========================================== */

        .edit-certificate-actions {

            display: flex;

            align-items: center;

            gap: 12px;

            padding-top: 18px;

            border-top: 1px solid #e5e7eb;

            margin-top: 10px;

        }


        /* =========================================
           UPDATE BUTTON
        ========================================== */

        .update-certificate-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 11px 18px;

            border: none;

            border-radius: 8px;

            background: #2196f3;

            color: #ffffff;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.3s;

        }


        .update-certificate-btn:hover {

            background: #1976d2;

            transform: translateY(-1px);

        }


        /* =========================================
           BACK BUTTON
        ========================================== */

        .back-certificate-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 10px 16px;

            border-radius: 8px;

            background: #f3f4f6;

            color: #374151;

            border: 1px solid #d1d5db;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            transition: 0.3s;

        }


        .back-certificate-btn:hover {

            background: #e5e7eb;

        }


        /* =========================================
           DELETE AREA
        ========================================== */

        .delete-certificate-area {

            margin-top: 20px;

            padding-top: 18px;

            border-top: 1px solid #e5e7eb;

        }


        /* =========================================
           DELETE BUTTON
        ========================================== */

        .delete-certificate-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 10px 16px;

            border-radius: 8px;

            background: #fef2f2;

            color: #dc2626;

            border: 1px solid #fecaca;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.3s;

        }


        .delete-certificate-btn:hover {

            background: #fee2e2;

        }


        /* =========================================
           MOBILE
        ========================================== */

        @media (max-width: 600px) {


            .edit-certificate-page {

                padding: 25px 18px;

            }


            .edit-certificate-card {

                padding: 20px;

            }


            .edit-certificate-actions {

                flex-direction: column;

                align-items: stretch;

            }


            .update-certificate-btn,
            .back-certificate-btn {

                width: 100%;

            }


            .delete-certificate-btn {

                width: 100%;

            }


        }


    </style>


</head>


<body>


<!-- =========================================
     EXISTING SIDEBAR
     DO NOT CHANGE
========================================= -->

<?php include("sidebar.php"); ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <div class="edit-certificate-page">


        <!-- =====================================
             HEADER
        ====================================== -->

        <div class="edit-certificate-header">


            <h2>

                <i class="fa-solid fa-certificate"></i>

                Edit Certificate

            </h2>


            <p>

                Update your professional certificate details.

            </p>


        </div>


        <!-- =====================================
             FORM CARD
        ====================================== -->

        <div class="edit-certificate-card">


            <!-- =================================
                 SUCCESS MESSAGE
            ================================== -->

            <?php if ($success !== ''): ?>


                <div
                    class="certificate-alert certificate-success"
                >

                    <i
                        class="fa-solid fa-circle-check"
                    ></i>

                    <?= htmlspecialchars(
                        $success
                    ); ?>

                </div>


            <?php endif; ?>


            <!-- =================================
                 ERROR MESSAGE
            ================================== -->

            <?php if ($error !== ''): ?>


                <div
                    class="certificate-alert certificate-error"
                >

                    <i
                        class="fa-solid fa-circle-exclamation"
                    ></i>

                    <?= htmlspecialchars(
                        $error
                    ); ?>

                </div>


            <?php endif; ?>


            <!-- =================================
                 UPDATE FORM
            ================================== -->

            <form
                method="POST"
                action=""
            >


                <!-- =================================
                     CERTIFICATE NAME
                ================================== -->

                <div
                    class="certificate-form-group"
                >


                    <label>

                        Certificate Name
                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        name="certificate_name"
                        value="<?= htmlspecialchars(
                            $certificate[
                                'certificate_name'
                            ] ?? ''
                        ); ?>"
                        placeholder="Enter certificate name"
                        required
                    >


                </div>


                <!-- =================================
                     ISSUER
                ================================== -->

                <div
                    class="certificate-form-group"
                >


                    <label>

                        Issued By

                    </label>


                    <input
                        type="text"
                        name="issuer"
                        value="<?= htmlspecialchars(
                            $certificate[
                                'issuer'
                            ] ?? ''
                        ); ?>"
                        placeholder="Enter issuing organization"
                    >


                </div>


                <!-- =================================
                     CERTIFICATE URL
                ================================== -->

                <div
                    class="certificate-form-group"
                >


                    <label>

                        Certificate URL

                    </label>


                    <input
                        type="url"
                        name="certificate_url"
                        value="<?= htmlspecialchars(
                            $certificate[
                                'certificate_url'
                            ] ?? ''
                        ); ?>"
                        placeholder="https://example.com/certificate"
                    >


                    <small>

                        Optional. Add the public URL where your certificate can be viewed.

                    </small>


                </div>


                <!-- =================================
                     UPDATE + BACK
                ================================== -->

                <div
                    class="edit-certificate-actions"
                >


                    <!-- UPDATE -->

                    <button
                        type="submit"
                        name="update_certificate"
                        class="update-certificate-btn"
                    >

                        <i
                            class="fa-solid fa-floppy-disk"
                        ></i>

                        Update Certificate

                    </button>


                    <!-- BACK -->

                    <a
                        href="certificates.php"
                        class="back-certificate-btn"
                    >

                        <i
                            class="fa-solid fa-arrow-left"
                        ></i>

                        Back

                    </a>


                </div>


            </form>


            <!-- =================================
                 DELETE
                 
                 SEPARATE FORM
                 NOT NESTED
            ================================== -->

            <div
                class="delete-certificate-area"
            >


                <form
                    method="POST"
                    action=""
                    onsubmit="return confirm('Are you sure you want to delete this certificate permanently?');"
                >


                    <button
                        type="submit"
                        name="delete_certificate"
                        class="delete-certificate-btn"
                    >

                        <i
                            class="fa-solid fa-trash"
                        ></i>

                        Delete Certificate

                    </button>


                </form>


            </div>


        </div>


    </div>


</div>


</body>

</html>