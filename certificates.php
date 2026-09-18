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
   DELETE CERTIFICATE
   DELETE DIRECTLY FROM THIS PAGE
========================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_certificate'])
) {

    $certificate_id = isset($_POST['certificate_id'])
        ? (int)$_POST['certificate_id']
        : 0;


    if ($certificate_id > 0) {

        /*
         * Delete only the certificate
         * belonging to the logged-in user.
         */

        $stmt = $conn->prepare("
            DELETE FROM certificates
            WHERE id = ?
              AND user_id = ?
        ");

        if (!$stmt) {
            die("Delete query error: " . $conn->error);
        }

        $stmt->bind_param(
            "ii",
            $certificate_id,
            $user_id
        );

        $stmt->execute();

        $stmt->close();
    }


    /*
     * Redirect back to certificates.php
     * after deletion.
     *
     * This prevents duplicate deletion
     * when the page is refreshed.
     */

    header("Location: certificates.php?deleted=1");
    exit();
}


/* =========================================
   DELETE SUCCESS MESSAGE
========================================= */

$delete_success = false;

if (
    isset($_GET['deleted']) &&
    $_GET['deleted'] === '1'
) {
    $delete_success = true;
}


/* =========================================
   GET CERTIFICATES
========================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        certificate_name,
        issuer,
        certificate_url,
        created_at
    FROM certificates
    WHERE user_id = ?
    ORDER BY id DESC
");

if (!$stmt) {
    die("Certificate query error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$certificates = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Certificates</title>


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


    <style>

        /* =========================================
           CERTIFICATES PAGE
        ========================================= */

        .certificates-page {

            width: 100%;

            max-width: 1100px;

            margin: 0 auto;

            padding: 35px 30px;

            box-sizing: border-box;

        }


        /* =========================================
           HEADER
        ========================================= */

        .certificates-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 30px;

        }


        .certificates-title h2 {

            margin: 0 0 7px 0;

            font-size: 28px;

        }


        .certificates-title p {

            margin: 0;

            opacity: 0.7;

        }


        /* =========================================
           ADD BUTTON
        ========================================= */

        .add-certificate-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 11px 18px;

            border-radius: 8px;

            background: #2196f3;

            color: #ffffff;

            text-decoration: none;

            font-weight: 600;

            white-space: nowrap;

            transition: 0.3s;

        }


        .add-certificate-btn:hover {

            background: #1976d2;

            transform: translateY(-1px);

        }


        /* =========================================
           SUCCESS MESSAGE
        ========================================= */

        .certificate-success-message {

            display: flex;

            align-items: center;

            gap: 10px;

            margin-bottom: 22px;

            padding: 13px 16px;

            border-radius: 8px;

            background: #f0fdf4;

            color: #15803d;

            border: 1px solid #bbf7d0;

            font-size: 14px;

            font-weight: 600;

        }


        /* =========================================
           CERTIFICATE GRID
        ========================================= */

        .certificates-grid {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 22px;

        }


        /* =========================================
           WHITE CERTIFICATE CARD
        ========================================= */

        .certificate-card {

            padding: 24px;

            border-radius: 14px;

            background: #ffffff;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

            box-sizing: border-box;

            color: #1f2937;

            transition: 0.3s;

        }


        .certificate-card:hover {

            transform: translateY(-3px);

            border-color: #90caf9;

            box-shadow:
                0 8px 22px rgba(0, 0, 0, 0.10);

        }


        /* =========================================
           CERTIFICATE TITLE
        ========================================= */

        .certificate-card h3 {

            margin: 0 0 14px 0;

            font-size: 21px;

            color: #1f2937;

        }


        /* =========================================
           ISSUER
        ========================================= */

        .certificate-info {

            margin: 0 0 16px 0;

            line-height: 1.6;

            color: #6b7280;

        }


        .certificate-info strong {

            color: #374151;

        }


        /* =========================================
           VIEW BUTTON
        ========================================= */

        .certificate-view {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 8px 12px;

            border-radius: 7px;

            background: #eff6ff;

            color: #2563eb;

            border: 1px solid #dbeafe;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            transition: 0.3s;

        }


        .certificate-view:hover {

            background: #dbeafe;

        }


        /* =========================================
           ACTION AREA
        ========================================= */

        .certificate-actions {

            display: flex;

            gap: 10px;

            border-top: 1px solid #e5e7eb;

            padding-top: 16px;

            margin-top: 18px;

        }


        /* =========================================
           EDIT
        ========================================= */

        .certificate-edit {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 9px 15px;

            border-radius: 7px;

            background: #e0f2fe;

            color: #0284c7;

            border: 1px solid #bae6fd;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            transition: 0.3s;

        }


        .certificate-edit:hover {

            background: #bae6fd;

        }


        /* =========================================
           DELETE
        ========================================= */

        .certificate-delete-form {

            margin: 0;

        }


        .certificate-delete {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 9px 15px;

            border-radius: 7px;

            background: #fef2f2;

            color: #dc2626;

            border: 1px solid #fecaca;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.3s;

        }


        .certificate-delete:hover {

            background: #fee2e2;

        }


        /* =========================================
           NO CERTIFICATES
        ========================================= */

        .no-certificates {

            padding: 55px 25px;

            text-align: center;

            border-radius: 14px;

            background: #ffffff;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

            color: #374151;

        }


        .no-certificates i {

            font-size: 45px;

            margin-bottom: 15px;

            color: #9ca3af;

        }


        .no-certificates h3 {

            margin: 0 0 8px 0;

        }


        .no-certificates p {

            margin: 0 0 20px 0;

            color: #6b7280;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 800px) {

            .certificates-grid {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 600px) {

            .certificates-page {

                padding: 25px 18px;

            }


            .certificates-header {

                flex-direction: column;

                align-items: flex-start;

            }


            .add-certificate-btn {

                width: 100%;

            }


            .certificate-card {

                padding: 20px;

            }


            .certificate-actions {

                flex-direction: column;

            }


            .certificate-edit,
            .certificate-delete {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     SIDEBAR
========================================= -->

<?php include("sidebar.php"); ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <div class="certificates-page">


        <!-- =====================================
             HEADER
        ====================================== -->

        <div class="certificates-header">


            <div class="certificates-title">

                <h2>
                    My Certificates
                </h2>

                <p>
                    Manage your professional certificates.
                </p>

            </div>


            <a
                href="add_certificate.php"
                class="add-certificate-btn"
            >

                <i class="fa-solid fa-plus"></i>

                Add Certificate

            </a>


        </div>


        <!-- =====================================
             DELETE SUCCESS MESSAGE
        ====================================== -->

        <?php if ($delete_success): ?>

            <div class="certificate-success-message">

                <i class="fa-solid fa-circle-check"></i>

                Certificate deleted successfully.

            </div>

        <?php endif; ?>


        <!-- =====================================
             CERTIFICATE LIST
        ====================================== -->

        <?php if ($certificates->num_rows > 0): ?>


            <div class="certificates-grid">


                <?php while ($certificate = $certificates->fetch_assoc()): ?>


                    <div class="certificate-card">


                        <!-- CERTIFICATE NAME -->

                        <h3>

                            <?= htmlspecialchars(
                                $certificate['certificate_name']
                            ); ?>

                        </h3>


                        <!-- ISSUER -->

                        <?php if (!empty($certificate['issuer'])): ?>

                            <p class="certificate-info">

                                <strong>
                                    Issued By:
                                </strong>

                                <?= htmlspecialchars(
                                    $certificate['issuer']
                                ); ?>

                            </p>

                        <?php endif; ?>


                        <!-- VIEW CERTIFICATE -->

                        <?php if (!empty($certificate['certificate_url'])): ?>

                            <a
                                href="<?= htmlspecialchars(
                                    $certificate['certificate_url']
                                ); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="certificate-view"
                            >

                                <i class="fa-solid fa-eye"></i>

                                View Certificate

                            </a>

                        <?php endif; ?>


                        <!-- EDIT + DELETE -->

                        <div class="certificate-actions">


                            <!-- EDIT -->

                            <a
                                href="edit_certificate.php?id=<?= (int)$certificate['id']; ?>"
                                class="certificate-edit"
                            >

                                <i class="fa-solid fa-pen"></i>

                                Edit

                            </a>


                            <!-- DELETE DIRECTLY -->

                            <form
                                action=""
                                method="POST"
                                class="certificate-delete-form"
                                onsubmit="return confirm('Are you sure you want to delete this certificate?');"
                            >

                                <input
                                    type="hidden"
                                    name="certificate_id"
                                    value="<?= (int)$certificate['id']; ?>"
                                >


                                <button
                                    type="submit"
                                    name="delete_certificate"
                                    class="certificate-delete"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                    Delete

                                </button>

                            </form>


                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <div class="no-certificates">

                <i class="fa-solid fa-certificate"></i>

                <h3>
                    No Certificates Found
                </h3>

                <p>
                    You haven't added any certificates yet.
                </p>

                <a
                    href="add_certificate.php"
                    class="add-certificate-btn"
                >

                    <i class="fa-solid fa-plus"></i>

                    Add Your First Certificate

                </a>

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>