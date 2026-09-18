<?php

session_start();

require_once "config/db.php";

/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header("Location: login.php");
    exit();
}

$username = trim($_SESSION['username']);


/* =====================================================
   HELPER
===================================================== */

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =====================================================
   GET LOGGED-IN USER
===================================================== */

$stmt = $conn->prepare("
    SELECT
        id,
        fullname,
        username,
        email,
        phone,
        education,
        career_objective,
        profile_photo
    FROM users
    WHERE username = ?
    LIMIT 1
");

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();

$user_id = (int)$user['id'];


/* =====================================================
   EDUCATION
===================================================== */

$education = [];

$stmt = $conn->prepare("
    SELECT
        education_type,
        institution_name,
        course,
        department,
        start_year,
        end_year,
        percentage,
        grade
    FROM education
    WHERE user_id = ?
    ORDER BY id ASC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $education[] = $row;
}


/* =====================================================
   PROJECTS
===================================================== */

$projects = [];

$stmt = $conn->prepare("
    SELECT
        title,
        description,
        technologies,
        demo_link
    FROM projects
    WHERE user_id = ?
    ORDER BY id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}


/* =====================================================
   CERTIFICATES
===================================================== */

$certificates = [];

$stmt = $conn->prepare("
    SELECT
        certificate_name,
        issuer,
        certificate_url
    FROM certificates
    WHERE user_id = ?
    ORDER BY id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $certificates[] = $row;
}


/* =====================================================
   CONTACT
===================================================== */

$contact = null;

$stmt = $conn->prepare("
    SELECT
        address,
        city,
        state,
        pincode,
        alternate_email,
        linkedin,
        github,
        website
    FROM contact
    WHERE user_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $contact = $result->fetch_assoc();
}


/* =====================================================
   PROFILE PHOTO
   SUPPORT BOTH:
   1. BLOB IMAGE
   2. IMAGE FILE PATH / FILENAME
===================================================== */

$photo = "";

if (!empty($user['profile_photo'])) {

    $profilePhoto = $user['profile_photo'];

    /*
     * Check whether database value is an actual image BLOB.
     */

    if (
        is_string($profilePhoto) &&
        (
            str_starts_with($profilePhoto, "\xFF\xD8\xFF") ||
            str_starts_with($profilePhoto, "\x89PNG") ||
            str_starts_with($profilePhoto, "GIF8") ||
            str_starts_with($profilePhoto, "RIFF")
        )
    ) {

        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $mime = $finfo->buffer($profilePhoto);

        if ($mime) {

            $photo =
                "data:" .
                $mime .
                ";base64," .
                base64_encode($profilePhoto);
        }

    } else {

        /*
         * If profile_photo stores a filename/path.
         */

        $photoPath = trim($profilePhoto);

        /*
         * If already a URL/data URL.
         */

        if (
            str_starts_with($photoPath, "http://") ||
            str_starts_with($photoPath, "https://") ||
            str_starts_with($photoPath, "data:image")
        ) {

            $photo = $photoPath;

        } else {

            /*
             * Common upload locations.
             */

            $possiblePaths = [
                $photoPath,
                "uploads/" . $photoPath,
                "uploads/profile/" . $photoPath,
                "images/" . $photoPath
            ];

            foreach ($possiblePaths as $path) {

                if (file_exists($path)) {

                    $photo = $path;

                    break;
                }
            }
        }
    }
}


/* =====================================================
   USER INITIAL
===================================================== */

$initial = "U";

if (!empty($user['fullname'])) {

    $nameParts = preg_split(
        '/\s+/',
        trim($user['fullname'])
    );

    if (!empty($nameParts[0])) {

        $initial = strtoupper(
            substr(
                $nameParts[0],
                0,
                1
            )
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

    <title>
        <?= e($user['fullname']); ?> - Resume
    </title>


    <!-- EXISTING SIDEBAR CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- RESUME CSS -->

    <link
        rel="stylesheet"
        href="css/resume.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- HTML2PDF -->

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js">
    </script>


    <style>

        /* =====================================================
           PAGE
        ===================================================== */

        .resume-page {

            padding: 25px;

            min-height: 100vh;

            background: #f4f6f9;

            box-sizing: border-box;
        }


        /* =====================================================
           TOOLBAR
        ===================================================== */

        .resume-toolbar {

            width: 100%;

            max-width: 850px;

            margin: 0 auto 18px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;
        }


        .resume-toolbar h2 {

            margin: 0;

            color: #222;

            font-size: 24px;
        }


        .resume-toolbar p {

            margin: 4px 0 0;

            color: #777;

            font-size: 13px;
        }


        /* =====================================================
           TOP DOWNLOAD
        ===================================================== */

        .print-btn {

            border: none;

            background: #2563eb;

            color: #fff;

            padding: 9px 16px;

            border-radius: 6px;

            cursor: pointer;

            font-weight: 600;

            font-size: 13px;
        }


        .print-btn:hover {

            background: #1d4ed8;
        }


        /* =====================================================
           WHITE RESUME BOX
        ===================================================== */

        .resume-container {

            width: 100%;

            max-width: 850px;

            margin: 0 auto;

            background: #fff;

            padding: 30px;

            border-radius: 8px;

            box-shadow:
                0 3px 15px rgba(0,0,0,0.08);

            box-sizing: border-box;
        }


        /* =====================================================
           HEADER
        ===================================================== */

        .resume-header {

            display: flex;

            align-items: center;

            gap: 18px;

            padding-bottom: 20px;

            border-bottom: 2px solid #222;
        }


        /* =====================================================
           PROFILE PHOTO AREA
           SMALL SQUARE PHOTO
        ===================================================== */

        .profile-area {

            width: 82px;

            height: 82px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;
        }


        .profile-photo {

            width: 82px;

            height: 82px;

            border-radius: 8px;

            object-fit: cover;

            display: block;

            border: 1px solid #e5e7eb;

            background: #f3f4f6;
        }


        /* =====================================================
           PLACEHOLDER
        ===================================================== */

        .profile-placeholder {

            width: 82px;

            height: 82px;

            border-radius: 8px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #2563eb;

            color: #fff;

            font-size: 30px;

            font-weight: bold;

            border: 1px solid #1d4ed8;

            box-sizing: border-box;
        }


        /* =====================================================
           HEADER DETAILS
        ===================================================== */

        .header-details {

            min-width: 0;

            flex: 1;
        }


        .header-details h1 {

            margin: 0;

            font-size: 27px;

            color: #111;
        }


        .resume-username {

            margin-top: 3px;

            margin-bottom: 6px;

            font-size: 12px;

            color: #666;
        }


        .header-details h2 {

            margin: 4px 0 8px;

            font-size: 14px;

            font-weight: normal;

            color: #555;
        }


        /* =====================================================
           CONTACT
        ===================================================== */

        .contact-line {

            display: flex;

            flex-wrap: wrap;

            gap: 12px;

            margin-top: 5px;

            font-size: 12px;

            color: #555;
        }


        .contact-line a {

            color: #2563eb;

            text-decoration: none;
        }


        .contact-line a:hover {

            text-decoration: underline;
        }


        /* =====================================================
           SECTIONS
        ===================================================== */

        .resume-section {

            margin-top: 22px;
        }


        .resume-section h3 {

            margin: 0 0 10px;

            padding-bottom: 6px;

            border-bottom: 1px solid #d1d5db;

            font-size: 14px;

            letter-spacing: 1px;

            color: #222;
        }


        .resume-section p {

            line-height: 1.6;

            color: #444;

            font-size: 13px;
        }


        /* =====================================================
           EDUCATION
        ===================================================== */

        .education-item {

            padding: 11px 0;

            border-bottom: 1px solid #eee;
        }


        .education-item:last-child {

            border-bottom: none;
        }


        .education-top {

            display: flex;

            justify-content: space-between;

            gap: 15px;
        }


        .education-top h4 {

            margin: 0 0 5px;

            font-size: 14px;

            color: #222;
        }


        .institution {

            font-size: 13px;

            font-weight: 600;

            color: #2563eb;
        }


        .small-text {

            margin-top: 4px;

            font-size: 12px;

            color: #666;
        }


        .year {

            font-size: 12px;

            color: #666;

            white-space: nowrap;
        }


        .education-result {

            display: flex;

            flex-wrap: wrap;

            gap: 18px;

            margin-top: 7px;

            font-size: 12px;

            color: #555;
        }


        /* =====================================================
           PROJECTS
        ===================================================== */

        .project-item {

            padding: 11px 0;

            border-bottom: 1px solid #eee;
        }


        .project-item:last-child {

            border-bottom: none;
        }


        .project-item h4 {

            margin: 0 0 5px;

            font-size: 14px;

            color: #222;
        }


        .technologies {

            margin-bottom: 5px;

            font-size: 12px;

            color: #2563eb;
        }


        .project-item p {

            margin: 4px 0;
        }


        .project-link {

            display: inline-block;

            margin-top: 4px;

            color: #2563eb;

            text-decoration: none;

            font-size: 12px;

            font-weight: 600;
        }


        /* =====================================================
           CERTIFICATES
        ===================================================== */

        .certificate-item {

            padding: 9px 0;

            border-bottom: 1px solid #eee;
        }


        .certificate-item:last-child {

            border-bottom: none;
        }


        .certificate-item h4 {

            margin: 0 0 4px;

            font-size: 13px;

            color: #222;
        }


        .certificate-item p {

            margin: 2px 0;
        }


        .certificate-link {

            color: #2563eb;

            text-decoration: none;

            font-size: 12px;
        }


        /* =====================================================
           CONTACT DETAILS
        ===================================================== */

        .contact-details p {

            margin: 5px 0;

            font-size: 13px;

            color: #444;
        }


        /* =====================================================
           BOTTOM BUTTONS
        ===================================================== */

        .resume-actions {

            width: 100%;

            max-width: 850px;

            margin: 18px auto 0;

            display: flex;

            justify-content: center;

            gap: 12px;
        }


        .action-btn {

            border: none;

            padding: 10px 20px;

            border-radius: 6px;

            cursor: pointer;

            font-size: 13px;

            font-weight: 600;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 6px;
        }


        .download-btn {

            background: #2563eb;

            color: #fff;
        }


        .download-btn:hover {

            background: #1d4ed8;
        }


        .share-btn {

            background: #16a34a;

            color: #fff;
        }


        .share-btn:hover {

            background: #15803d;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 768px) {

            .resume-page {

                padding: 12px;
            }


            .resume-toolbar {

                flex-direction: column;

                align-items: flex-start;
            }


            .resume-container {

                padding: 20px;
            }


            .resume-header {

                align-items: flex-start;
            }


            .profile-area,
            .profile-photo,
            .profile-placeholder {

                width: 70px;

                height: 70px;
            }


            .profile-placeholder {

                font-size: 25px;
            }


            .header-details h1 {

                font-size: 22px;
            }


            .education-top {

                flex-direction: column;
            }


            .year {

                white-space: normal;
            }


            .resume-actions {

                flex-direction: column;
            }


            .action-btn {

                width: 100%;
            }
        }


        /* =====================================================
           PRINT
        ===================================================== */

        @media print {

            .resume-toolbar,
            .resume-actions {

                display: none !important;
            }


            .resume-page {

                padding: 0 !important;

                background: #fff !important;
            }


            .resume-container {

                max-width: 100% !important;

                margin: 0 !important;

                box-shadow: none !important;

                border-radius: 0 !important;
            }
        }

    </style>

</head>


<body>


<!-- =====================================================
     EXISTING SIDEBAR
     DO NOT CHANGE sidebar.php
===================================================== -->

<?php include "sidebar.php"; ?>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="main-content">

    <div class="resume-page">


        <!-- =================================================
             TOOLBAR
        ================================================== -->

        <div class="resume-toolbar">

            <div>

                <h2>Resume</h2>

                <p>Professional Resume</p>

            </div>

        </div>


        <!-- =================================================
             RESUME WHITE BOX
        ================================================== -->

        <div
            class="resume-container"
            id="resumeContent"
        >


            <!-- =================================================
                 HEADER / ABOUT
            ================================================== -->

            <header class="resume-header">


                <!-- SMALL SQUARE PROFILE PHOTO -->

                <div class="profile-area">

                    <?php if (!empty($photo)): ?>

                        <img
                            src="<?= e($photo); ?>"
                            class="profile-photo"
                            alt="Profile Photo"
                        >

                    <?php else: ?>

                        <div class="profile-placeholder">

                            <?= e($initial); ?>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- USER DETAILS -->

                <div class="header-details">


                    <?php if (!empty($user['fullname'])): ?>

                        <h1>
                            <?= e($user['fullname']); ?>
                        </h1>

                    <?php endif; ?>


                    <?php if (!empty($user['username'])): ?>

                        <div class="resume-username">

                            @<?= e($user['username']); ?>

                        </div>

                    <?php endif; ?>


                    <?php if (!empty($user['education'])): ?>

                        <h2>

                            <?= e($user['education']); ?>

                        </h2>

                    <?php endif; ?>


                    <!-- EMAIL / PHONE / CITY -->

                    <div class="contact-line">


                        <?php if (!empty($user['email'])): ?>

                            <span>

                                <i class="fa-solid fa-envelope"></i>

                                <?= e($user['email']); ?>

                            </span>

                        <?php endif; ?>


                        <?php if (!empty($user['phone'])): ?>

                            <span>

                                <i class="fa-solid fa-phone"></i>

                                <?= e($user['phone']); ?>

                            </span>

                        <?php endif; ?>


                        <?php if (
                            $contact &&
                            !empty($contact['city'])
                        ): ?>

                            <span>

                                <i class="fa-solid fa-location-dot"></i>

                                <?= e($contact['city']); ?>

                            </span>

                        <?php endif; ?>


                    </div>


                    <!-- SOCIAL LINKS -->

                    <div class="contact-line">


                        <?php if (
                            $contact &&
                            !empty($contact['linkedin'])
                        ): ?>

                            <a
                                href="<?= e($contact['linkedin']); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <i class="fa-brands fa-linkedin"></i>

                                LinkedIn

                            </a>

                        <?php endif; ?>


                        <?php if (
                            $contact &&
                            !empty($contact['github'])
                        ): ?>

                            <a
                                href="<?= e($contact['github']); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <i class="fa-brands fa-github"></i>

                                GitHub

                            </a>

                        <?php endif; ?>


                        <?php if (
                            $contact &&
                            !empty($contact['website'])
                        ): ?>

                            <a
                                href="<?= e($contact['website']); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                            >

                                <i class="fa-solid fa-globe"></i>

                                Website

                            </a>

                        <?php endif; ?>


                    </div>

                </div>

            </header>


            <!-- =================================================
                 CAREER OBJECTIVE
            ================================================== -->

            <?php if (!empty($user['career_objective'])): ?>

                <section class="resume-section">

                    <h3>
                        ABOUT / CAREER OBJECTIVE
                    </h3>

                    <p>

                        <?= nl2br(
                            e($user['career_objective'])
                        ); ?>

                    </p>

                </section>

            <?php endif; ?>


            <!-- =================================================
                 EDUCATION
            ================================================== -->

            <?php if (!empty($education)): ?>

                <section class="resume-section">

                    <h3>
                        EDUCATION
                    </h3>


                    <?php foreach ($education as $edu): ?>

                        <div class="education-item">


                            <div class="education-top">


                                <div>


                                    <?php if (
                                        !empty(
                                            $edu['education_type']
                                        )
                                    ): ?>

                                        <h4>

                                            <?= e(
                                                $edu[
                                                    'education_type'
                                                ]
                                            ); ?>

                                        </h4>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty(
                                            $edu['institution_name']
                                        )
                                    ): ?>

                                        <div class="institution">

                                            <?= e(
                                                $edu[
                                                    'institution_name'
                                                ]
                                            ); ?>

                                        </div>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty($edu['course']) ||
                                        !empty($edu['department'])
                                    ): ?>

                                        <div class="small-text">


                                            <?php if (
                                                !empty(
                                                    $edu['course']
                                                )
                                            ): ?>

                                                <?= e(
                                                    $edu['course']
                                                ); ?>

                                            <?php endif; ?>


                                            <?php if (
                                                !empty(
                                                    $edu['course']
                                                ) &&
                                                !empty(
                                                    $edu['department']
                                                )
                                            ): ?>

                                                &nbsp; | &nbsp;

                                            <?php endif; ?>


                                            <?php if (
                                                !empty(
                                                    $edu['department']
                                                )
                                            ): ?>

                                                <?= e(
                                                    $edu[
                                                        'department'
                                                    ]
                                                ); ?>

                                            <?php endif; ?>


                                        </div>

                                    <?php endif; ?>


                                </div>


                                <?php if (
                                    !empty($edu['start_year']) ||
                                    !empty($edu['end_year'])
                                ): ?>

                                    <div class="year">

                                        <?= e(
                                            $edu['start_year']
                                        ); ?>


                                        <?php if (
                                            !empty(
                                                $edu['start_year']
                                            ) &&
                                            !empty(
                                                $edu['end_year']
                                            )
                                        ): ?>

                                            -

                                        <?php endif; ?>


                                        <?= e(
                                            $edu['end_year']
                                        ); ?>

                                    </div>

                                <?php endif; ?>


                            </div>


                            <?php if (
                                !empty($edu['percentage']) ||
                                !empty($edu['grade'])
                            ): ?>

                                <div class="education-result">


                                    <?php if (
                                        !empty(
                                            $edu['percentage']
                                        )
                                    ): ?>

                                        <span>

                                            Percentage:

                                            <?= e(
                                                $edu[
                                                    'percentage'
                                                ]
                                            ); ?>

                                        </span>

                                    <?php endif; ?>


                                    <?php if (
                                        !empty($edu['grade'])
                                    ): ?>

                                        <span>

                                            Grade:

                                            <?= e(
                                                $edu['grade']
                                            ); ?>

                                        </span>

                                    <?php endif; ?>


                                </div>

                            <?php endif; ?>


                        </div>

                    <?php endforeach; ?>

                </section>

            <?php endif; ?>


            <!-- =================================================
                 PROJECTS
            ================================================== -->

            <?php if (!empty($projects)): ?>

                <section class="resume-section">

                    <h3>
                        PROJECTS
                    </h3>


                    <?php foreach ($projects as $project): ?>

                        <div class="project-item">


                            <?php if (
                                !empty(
                                    $project['title']
                                )
                            ): ?>

                                <h4>

                                    <?= e(
                                        $project['title']
                                    ); ?>

                                </h4>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $project['technologies']
                                )
                            ): ?>

                                <div class="technologies">

                                    Technologies:

                                    <?= e(
                                        $project[
                                            'technologies'
                                        ]
                                    ); ?>

                                </div>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $project['description']
                                )
                            ): ?>

                                <p>

                                    <?= nl2br(
                                        e(
                                            $project[
                                                'description'
                                            ]
                                        )
                                    ); ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $project['demo_link']
                                )
                            ): ?>

                                <a
                                    href="<?= e(
                                        $project[
                                            'demo_link'
                                        ]
                                    ); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="project-link"
                                >

                                    View Project

                                </a>

                            <?php endif; ?>


                        </div>

                    <?php endforeach; ?>

                </section>

            <?php endif; ?>


            <!-- =================================================
                 CERTIFICATIONS
            ================================================== -->

            <?php if (!empty($certificates)): ?>

                <section class="resume-section">

                    <h3>
                        CERTIFICATIONS
                    </h3>


                    <?php foreach (
                        $certificates
                        as $certificate
                    ): ?>

                        <div class="certificate-item">


                            <?php if (
                                !empty(
                                    $certificate[
                                        'certificate_name'
                                    ]
                                )
                            ): ?>

                                <h4>

                                    <?= e(
                                        $certificate[
                                            'certificate_name'
                                        ]
                                    ); ?>

                                </h4>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $certificate['issuer']
                                )
                            ): ?>

                                <p>

                                    Issued by:

                                    <?= e(
                                        $certificate[
                                            'issuer'
                                        ]
                                    ); ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $certificate[
                                        'certificate_url'
                                    ]
                                )
                            ): ?>

                                <a
                                    href="<?= e(
                                        $certificate[
                                            'certificate_url'
                                        ]
                                    ); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="certificate-link"
                                >

                                    View Certificate

                                </a>

                            <?php endif; ?>


                        </div>

                    <?php endforeach; ?>

                </section>

            <?php endif; ?>


            <!-- =================================================
                 CONTACT
            ================================================== -->

            <?php if ($contact): ?>

                <?php

                $hasContact =
                    !empty($contact['address']) ||
                    !empty($contact['city']) ||
                    !empty($contact['state']) ||
                    !empty($contact['pincode']) ||
                    !empty($contact['alternate_email']);

                ?>


                <?php if ($hasContact): ?>

                    <section class="resume-section">

                        <h3>
                            CONTACT
                        </h3>


                        <div class="contact-details">


                            <?php if (
                                !empty(
                                    $contact['address']
                                )
                            ): ?>

                                <p>

                                    <strong>
                                        Address:
                                    </strong>

                                    <?= e(
                                        $contact['address']
                                    ); ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $contact['city']
                                )
                            ): ?>

                                <p>

                                    <strong>
                                        City:
                                    </strong>

                                    <?= e(
                                        $contact['city']
                                    ); ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $contact['state']
                                )
                            ): ?>

                                <p>

                                    <strong>
                                        State:
                                    </strong>

                                    <?= e(
                                        $contact['state']
                                    ); ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $contact['pincode']
                                )
                            ): ?>

                                <p>

                                    <strong>
                                        Pincode:
                                    </strong>

                                    <?= e(
                                        $contact['pincode']
                                    ); ?>

                                </p>

                            <?php endif; ?>


                            <?php if (
                                !empty(
                                    $contact[
                                        'alternate_email'
                                    ]
                                )
                            ): ?>

                                <p>

                                    <strong>
                                        Alternate Email:
                                    </strong>

                                    <?= e(
                                        $contact[
                                            'alternate_email'
                                        ]
                                    ); ?>

                                </p>

                            <?php endif; ?>


                        </div>

                    </section>

                <?php endif; ?>

            <?php endif; ?>


        </div>


        <!-- =================================================
             DOWNLOAD + SHARE
        ================================================== -->

        <div class="resume-actions">


            <!-- DOWNLOAD PDF -->

            <button
                type="button"
                class="action-btn download-btn"
                onclick="downloadResume()"
            >

                <i class="fa-solid fa-download"></i>

                Download PDF

            </button>


            <!-- SHARE PDF -->

            <button
                type="button"
                class="action-btn share-btn"
                onclick="shareResume()"
            >

                <i class="fa-solid fa-share-nodes"></i>

                Share Resume

            </button>


        </div>


    </div>

</div>


<script>

/* =====================================================
   DOWNLOAD PDF
===================================================== */

function downloadResume()
{
    const resume =
        document.getElementById("resumeContent");

    if (!resume) {

        alert("Resume content not found.");

        return;
    }


    if (typeof html2pdf === "undefined") {

        alert(
            "PDF library could not be loaded. Please check your internet connection."
        );

        return;
    }


    const fullname =
        <?= json_encode(
            $user['fullname'] ?? 'Resume'
        ); ?>;


    let filename =
        String(fullname)
            .replace(/[^a-z0-9]/gi, "_")
            .replace(/_+/g, "_");


    if (filename === "") {

        filename = "Resume";
    }


    filename += "_Resume.pdf";


    const options = {

        margin: 0,

        filename: filename,

        image: {

            type: "jpeg",

            quality: 0.98
        },

        html2canvas: {

            scale: 2,

            useCORS: true,

            allowTaint: true,

            backgroundColor: "#ffffff"
        },

        jsPDF: {

            unit: "mm",

            format: "a4",

            orientation: "portrait"
        },

        pagebreak: {

            mode: [
                "css",
                "legacy"
            ]
        }
    };


    html2pdf()

        .set(options)

        .from(resume)

        .save();
}


/* =====================================================
   SHARE RESUME AS PDF FILE
===================================================== */

async function shareResume()
{
    const resume =
        document.getElementById("resumeContent");


    if (!resume) {

        alert("Resume content not found.");

        return;
    }


    if (typeof html2pdf === "undefined") {

        alert(
            "PDF library could not be loaded. Please check your internet connection."
        );

        return;
    }


    const fullname =
        <?= json_encode(
            $user['fullname'] ?? 'Resume'
        ); ?>;


    let filename =
        String(fullname)
            .replace(/[^a-z0-9]/gi, "_")
            .replace(/_+/g, "_");


    if (filename === "") {

        filename = "Resume";
    }


    filename += "_Resume.pdf";


    const options = {

        margin: 0,

        image: {

            type: "jpeg",

            quality: 0.98
        },

        html2canvas: {

            scale: 2,

            useCORS: true,

            allowTaint: true,

            backgroundColor: "#ffffff"
        },

        jsPDF: {

            unit: "mm",

            format: "a4",

            orientation: "portrait"
        },

        pagebreak: {

            mode: [
                "css",
                "legacy"
            ]
        }
    };


    try {

        /*
         * Create PDF Blob from ONLY resumeContent.
         *
         * Sidebar is outside resumeContent.
         * Bottom buttons are also outside resumeContent.
         *
         * Therefore neither sidebar nor buttons
         * will be included in the PDF.
         */

        const pdfBlob =
            await html2pdf()

                .set({
                    ...options,
                    filename: filename
                })

                .from(resume)

                .outputPdf("blob");


        /*
         * Convert PDF Blob into a File.
         */

        const pdfFile =
            new File(
                [pdfBlob],
                filename,
                {
                    type: "application/pdf"
                }
            );


        /*
         * Check whether browser supports
         * sharing files.
         */

        if (
            navigator.share &&
            navigator.canShare &&
            navigator.canShare({
                files: [pdfFile]
            })
        ) {

            await navigator.share({

                title:
                    String(fullname) +
                    " - Resume",

                text:
                    "Please find my resume.",

                files: [
                    pdfFile
                ]

            });

            return;
        }


        /*
         * Browser does not support
         * PDF file sharing.
         */

        alert(
            "PDF sharing is not supported in this browser. Please use Download PDF."
        );

    } catch (error) {


        /*
         * User cancelled share popup.
         */

        if (
            error &&
            error.name === "AbortError"
        ) {

            return;
        }


        console.error(
            "Resume PDF sharing failed:",
            error
        );


        alert(
            "Unable to share the resume PDF. Please try Download PDF."
        );
    }
}

</script>


</body>

</html>