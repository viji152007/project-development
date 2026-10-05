<?php

include "config/db.php";

/* =====================================================
   GET USERNAME FROM URL
===================================================== */

$request_uri = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

$base_path = '/vijiii/portfolio/';
$username = '';

if (strpos($request_uri, $base_path) === 0) {

    $username = trim(
        substr(
            $request_uri,
            strlen($base_path)
        ),
        '/'
    );

}

/* =====================================================
   OLD URL FALLBACK
===================================================== */

if (
    $username === '' &&
    isset($_GET['user'])
) {

    $username = trim($_GET['user']);

}

if ($username === '') {

    die("Portfolio not found.");

}

/* =====================================================
   GET SECTION
===================================================== */

$section = $_GET['section'] ?? 'about';

$allowed_sections = [
    'about',
    'education',
    'skills',
    'projects',
    'certificates',
    'resume',
    'contact'
];

if (
    !in_array(
        $section,
        $allowed_sections,
        true
    )
) {

    $section = 'about';

}

/* =====================================================
   GET PUBLIC USER
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

if (!$stmt) {

    die(
        "User query error: " .
        $conn->error
    );

}

$stmt->bind_param(
    "s",
    $username
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    die("Portfolio not found.");

}

$user = $result->fetch_assoc();

$stmt->close();

/* =====================================================
   USER ID CONTROLS ALL PUBLIC DATA
===================================================== */

$user_id = (int)$user['id'];

/* =====================================================
   PROFILE PHOTO - DATABASE BLOB
===================================================== */

function getProfilePhotoSrc($photo)
{

    if (empty($photo)) {

        return '';

    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->buffer($photo);

    if (
        !$mime ||
        strpos($mime, 'image/') !== 0
    ) {

        return '';

    }

    return
        'data:' .
        $mime .
        ';base64,' .
        base64_encode($photo);

}

/* =====================================================
   GET EDUCATION - THIS USER ONLY
===================================================== */

$education = null;

$stmt = $conn->prepare("
    SELECT
        id,
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
    ORDER BY id DESC
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $education = $stmt->get_result();

    $stmt->close();

}

/* =====================================================
   GET SKILLS - THIS USER ONLY
===================================================== */

$skills = null;

$stmt = $conn->prepare("
    SELECT
        id,
        skill_name,
        percentage
    FROM skills
    WHERE user_id = ?
    ORDER BY id DESC
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $skills = $stmt->get_result();

    $stmt->close();

}

/* =====================================================
   GET PROJECTS - THIS USER ONLY
===================================================== */

$projects = null;

$stmt = $conn->prepare("
    SELECT
        id,
        title,
        description,
        technologies,
        demo_link
    FROM projects
    WHERE user_id = ?
    ORDER BY id DESC
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $projects = $stmt->get_result();

    $stmt->close();

}

/* =====================================================
   GET CERTIFICATES - THIS USER ONLY
===================================================== */

$certificates = null;

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

if ($stmt) {

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $certificates = $stmt->get_result();

    $stmt->close();

}

/* =====================================================
   GET CONTACT - THIS USER ONLY
===================================================== */

$contacts = [];

$stmt = $conn->prepare("
    SELECT
        id,
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
    ORDER BY id DESC
");

if ($stmt) {

    $stmt->bind_param(
        "i",
        $user_id
    );

    $stmt->execute();

    $contact_result = $stmt->get_result();

    while (
        $contact_row =
        $contact_result->fetch_assoc()
    ) {

        $contacts[] = $contact_row;

    }

    $stmt->close();

}

/* =====================================================
   SKILL ICON
===================================================== */

function getSkillIcon($skillName)
{

    $skill = strtolower(
        trim($skillName)
    );

    if (
        strpos($skill, 'html') !== false
    ) {

        return 'fa-brands fa-html5';

    }

    if (
        strpos($skill, 'css') !== false
    ) {

        return 'fa-brands fa-css3-alt';

    }

    if (
        strpos($skill, 'javascript') !== false ||
        $skill === 'js'
    ) {

        return 'fa-brands fa-js';

    }

    if (
        strpos($skill, 'php') !== false
    ) {

        return 'fa-brands fa-php';

    }

    if (
        strpos($skill, 'python') !== false
    ) {

        return 'fa-brands fa-python';

    }

    if (
        strpos($skill, 'java') !== false
    ) {

        return 'fa-brands fa-java';

    }

    if (
        strpos($skill, 'mysql') !== false ||
        strpos($skill, 'database') !== false
    ) {

        return 'fa-solid fa-database';

    }

    if (
        strpos($skill, 'github') !== false
    ) {

        return 'fa-brands fa-github';

    }

    if (
        strpos($skill, 'bootstrap') !== false
    ) {

        return 'fa-brands fa-bootstrap';

    }

    if (
        strpos($skill, 'react') !== false
    ) {

        return 'fa-brands fa-react';

    }

    if (
        strpos($skill, 'node') !== false
    ) {

        return 'fa-brands fa-node-js';

    }

    return 'fa-solid fa-star';

}

/* =====================================================
   PUBLIC PORTFOLIO LINK
===================================================== */

function portfolioLink(
    $username,
    $section
) {

    return
        "http://localhost:8080/vijiii/portfolio/" .
        rawurlencode($username) .
        "?section=" .
        rawurlencode($section);

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

<?php
echo htmlspecialchars(
    $user['fullname']
);
?>

- Portfolio

</title>

<link
    rel="stylesheet"
    href="/vijiii/css/style.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
>

<style>

/* =================================================
   EDUCATION
================================================= */

.portfolio-education-list {
    display:flex;
    flex-direction:column;
    gap:20px;
    margin-top:25px;
}

.portfolio-education-card {
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:25px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

.portfolio-education-card h3 {
    margin:0 0 8px;
    font-size:21px;
    color:#111827;
}

.portfolio-education-card .institution {
    margin:0 0 20px;
    font-size:16px;
    font-weight:600;
    color:#2196f3;
}

.education-detail {
    display:flex;
    flex-direction:column;
    gap:10px;
}

.education-detail-row {
    padding:9px 12px;
    background:#f8fafc;
    border-radius:7px;
}

.education-detail-row strong {
    color:#111827;
    display:inline-block;
    min-width:125px;
}

/* =================================================
   CONTACT
================================================= */

.portfolio-contact-list {
    display:flex;
    flex-direction:column;
    gap:20px;
    margin-top:25px;
}

.portfolio-contact-card {
    background:#ffffff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:25px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

.portfolio-contact-card h3 {
    margin:0 0 18px;
    font-size:20px;
    color:#111827;
}

.portfolio-contact-info {
    display:flex;
    flex-direction:column;
    gap:12px;
}

.portfolio-contact-row {
    padding:11px 14px;
    background:#f8fafc;
    border-radius:8px;
    font-size:14px;
    color:#374151;
    line-height:1.6;
    word-break:break-word;
}

.portfolio-contact-row strong {
    color:#111827;
    display:inline-block;
    min-width:145px;
}

.portfolio-contact-row a {
    color:#1976d2;
    text-decoration:none;
}

.no-contact {
    padding:20px;
    background:#f8fafc;
    border-radius:10px;
}

/* =================================================
   PUBLIC RESUME
================================================= */

.public-resume {
    max-width:900px;
    margin:0 auto;
    background:#ffffff;
    padding:35px;
    border-radius:14px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

.public-resume-header {
    text-align:center;
    margin-bottom:25px;
}

.public-resume-header h1 {
    margin-bottom:5px;
}

.public-resume-header p {
    color:#6b7280;
}

.public-resume-profile {
    display:flex;
    align-items:center;
    gap:20px;
    margin-bottom:25px;
}

.public-resume-photo,
.public-resume-default-photo {
    width:110px;
    height:110px;
    border-radius:50%;
}

.public-resume-photo {
    object-fit:cover;
}

.public-resume-default-photo {
    background:#e5e7eb;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:40px;
}

.public-resume-profile h2 {
    margin:0 0 5px;
}

.public-resume-profile p {
    margin:0;
    color:#6b7280;
}

.public-resume-info {
    display:flex;
    flex-wrap:wrap;
    gap:12px;
    padding:15px;
    background:#f8fafc;
    border-radius:10px;
    margin-bottom:25px;
}

.public-resume-info span {
    padding:8px 12px;
}

.public-resume-section {
    margin-top:28px;
}

.public-resume-section h3 {
    border-bottom:2px solid #00d9ff;
    padding-bottom:8px;
    margin-bottom:15px;
}

.public-resume-education,
.public-resume-project {
    background:#f8fafc;
    padding:15px;
    border-radius:10px;
    margin-bottom:15px;
}

.public-resume-education h4,
.public-resume-project h4 {
    margin:0 0 8px;
}

.public-resume-education p,
.public-resume-project p {
    margin:7px 0;
}

.public-resume-skills {
    display:flex;
    flex-wrap:wrap;
    gap:10px;
}

.public-resume-skill {
    background:#eef2ff;
    padding:8px 14px;
    border-radius:20px;
}

.public-resume-contact p {
    margin:8px 0;
}

.public-resume-actions {
    display:flex;
    justify-content:center;
    gap:12px;
    margin-top:30px;
}

.public-resume-btn {
    border:none;
    padding:11px 18px;
    border-radius:8px;
    color:#ffffff;
    cursor:pointer;
    font-weight:600;
}

.public-resume-download {
    background:#16a34a;
}

.public-resume-share {
    background:#7c3aed;
}

/* =================================================
   PRINT
================================================= */

@media print {

    .sidebar,
    .public-resume-actions {
        display:none !important;
    }

    .content {
        margin:0 !important;
        padding:0 !important;
    }

    .public-resume {
        box-shadow:none;
        max-width:100%;
    }

}

/* =================================================
   MOBILE
================================================= */

@media (max-width:700px) {

    .portfolio-education-card,
    .portfolio-contact-card {
        padding:18px;
    }

    .public-resume {
        padding:20px;
    }

    .public-resume-profile {
        flex-direction:column;
        text-align:center;
    }

    .public-resume-actions {
        flex-direction:column;
    }

    .public-resume-btn {
        width:100%;
    }

}

</style>

</head>

<body>

<!-- =====================================================
     PUBLIC PORTFOLIO SIDEBAR
===================================================== -->

<div class="sidebar">

    <h2>
        <i class="fa-solid fa-user"></i>
        My Portfolio
    </h2>

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'about'
            );
        ?>"
        class="<?php
            echo $section === 'about'
                ? 'active'
                : '';
        ?>"
    >
        <i class="fa-solid fa-user"></i>
        <span>About</span>
    </a>

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'education'
            );
        ?>"
        class="<?php
            echo $section === 'education'
                ? 'active'
                : '';
        ?>"
    >
        <i class="fa-solid fa-graduation-cap"></i>
        <span>Education</span>
    </a>

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'skills'
            );
        ?>"
        class="<?php
            echo $section === 'skills'
                ? 'active'
                : '';
        ?>"
    >
        <i class="fa-solid fa-code"></i>
        <span>Skills</span>
    </a>

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'projects'
            );
        ?>"
        class="<?php
            echo $section === 'projects'
                ? 'active'
                : '';
        ?>"
    >
        <i class="fa-solid fa-folder"></i>
        <span>Projects</span>
    </a>

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'certificates'
            );
        ?>"
        class="<?php
            echo $section === 'certificates'
                ? 'active'
                : '';
        ?>"
    >
        <i class="fa-solid fa-certificate"></i>
        <span>Certificates</span>
    </a>

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'resume'
            );
        ?>"
        class="<?php
            echo $section === 'resume'
                ? 'active'
                : '';
        ?>"
    >
        <i class="fa-solid fa-file-alt"></i>
        <span>Resume</span>
    </a>

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'contact'
            );
        ?>"
        class="<?php
            echo $section === 'contact'
                ? 'active'
                : '';
        ?>"
    >
        <i class="fa-solid fa-envelope"></i>
        <span>Contact</span>
    </a>

</div>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="content">


<!-- =====================================================
     ABOUT
===================================================== -->

<?php if ($section === 'about') { ?>

<div class="dashboard-card">

    <h1>
        <i class="fa-solid fa-user"></i>
        About Me
    </h1>

    <?php

    $profilePhotoSrc = getProfilePhotoSrc(
        $user['profile_photo']
    );

    ?>

    <?php if ($profilePhotoSrc !== '') { ?>

        <img
            src="<?php
                echo htmlspecialchars(
                    $profilePhotoSrc
                );
            ?>"
            width="150"
            height="150"
            class="profile-img"
            alt="Profile Photo"
        >

    <?php } else { ?>

        <div class="profile-img">
            <i class="fa-solid fa-user"></i>
        </div>

    <?php } ?>

    <p>
        <strong>Full Name:</strong>

        <?php
        echo htmlspecialchars(
            $user['fullname']
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

    <p>
        <strong>Phone:</strong>

        <?php
        echo htmlspecialchars(
            $user['phone']
        );
        ?>
    </p>

    <p>
        <strong>Career Objective:</strong>
        <br>

        <?php
        echo nl2br(
            htmlspecialchars(
                $user['career_objective'] ?? ''
            )
        );
        ?>
    </p>

</div>

<?php } ?>


<!-- =====================================================
     EDUCATION
===================================================== -->

<?php if ($section === 'education') { ?>

<div class="dashboard-card">

    <h1>
        <i class="fa-solid fa-graduation-cap"></i>
        Education
    </h1>

    <?php if (
        $education &&
        $education->num_rows > 0
    ) { ?>

        <div class="portfolio-education-list">

            <?php while (
                $edu =
                $education->fetch_assoc()
            ) { ?>

                <div class="portfolio-education-card">

                    <h3>
                        <i class="fa-solid fa-graduation-cap"></i>

                        <?php
                        echo htmlspecialchars(
                            $edu['education_type']
                        );
                        ?>
                    </h3>

                    <p class="institution">

                        <?php
                        echo htmlspecialchars(
                            $edu['institution_name']
                        );
                        ?>

                    </p>

                    <div class="education-detail">

                        <?php if (
                            !empty($edu['course'])
                        ) { ?>

                            <div class="education-detail-row">

                                <strong>
                                    Course:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $edu['course']
                                );
                                ?>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty($edu['department'])
                        ) { ?>

                            <div class="education-detail-row">

                                <strong>
                                    Department:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $edu['department']
                                );
                                ?>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty($edu['start_year'])
                        ) { ?>

                            <div class="education-detail-row">

                                <strong>
                                    Start Year:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $edu['start_year']
                                );
                                ?>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty($edu['end_year'])
                        ) { ?>

                            <div class="education-detail-row">

                                <strong>
                                    End Year:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $edu['end_year']
                                );
                                ?>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty($edu['percentage'])
                        ) { ?>

                            <div class="education-detail-row">

                                <strong>
                                    Percentage / CGPA:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $edu['percentage']
                                );
                                ?>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty($edu['grade'])
                        ) { ?>

                            <div class="education-detail-row">

                                <strong>
                                    Grade:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $edu['grade']
                                );
                                ?>

                            </div>

                        <?php } ?>

                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <p>
            No education details added yet.
        </p>

    <?php } ?>

</div>

<?php } ?>


<!-- =====================================================
     SKILLS
===================================================== -->

<?php if ($section === 'skills') { ?>

<div class="dashboard-card">

    <h1>
        <i class="fa-solid fa-code"></i>
        My Skills
    </h1>

    <?php if (
        $skills &&
        $skills->num_rows > 0
    ) { ?>

        <div class="skill-list">

            <?php while (
                $skill =
                $skills->fetch_assoc()
            ) {

                $percentage =
                    (int)$skill['percentage'];

                $percentage =
                    max(
                        0,
                        min(
                            100,
                            $percentage
                        )
                    );

                $skillIcon =
                    getSkillIcon(
                        $skill['skill_name']
                    );

            ?>

                <div class="skill-item">

                    <i class="<?php
                        echo htmlspecialchars(
                            $skillIcon
                        );
                    ?>"></i>

                    <span>

                        <?php
                        echo htmlspecialchars(
                            $skill['skill_name']
                        );
                        ?>

                    </span>

                    <small>

                        <?php
                        echo $percentage;
                        ?>%

                    </small>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <p>
            No skills added yet.
        </p>

    <?php } ?>

</div>

<?php } ?>


<!-- =====================================================
     PROJECTS
===================================================== -->

<?php if ($section === 'projects') { ?>

<div class="dashboard-card">

    <h1>
        <i class="fa-solid fa-folder"></i>
        My Projects
    </h1>

    <?php if (
        $projects &&
        $projects->num_rows > 0
    ) { ?>

        <div class="portfolio-projects">

            <?php while (
                $project =
                $projects->fetch_assoc()
            ) { ?>

                <div class="portfolio-project-card">

                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $project['title'] ?? ''
                        );
                        ?>

                    </h3>

                    <?php if (
                        !empty(
                            $project['description']
                        )
                    ) { ?>

                        <p>

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $project['description']
                                )
                            );
                            ?>

                        </p>

                    <?php } ?>


                    <?php if (
                        !empty(
                            $project['technologies']
                        )
                    ) { ?>

                        <p>

                            <strong>
                                Technologies:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $project['technologies']
                            );
                            ?>

                        </p>

                    <?php } ?>


                    <?php if (
                        !empty(
                            $project['demo_link']
                        )
                    ) { ?>

                        <a
                            href="<?php
                                echo htmlspecialchars(
                                    $project['demo_link']
                                );
                            ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="portfolio-project-link"
                        >

                            <i class="fa-solid fa-eye"></i>
                            View Project

                        </a>

                    <?php } ?>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <p>
            No projects added yet.
        </p>

    <?php } ?>

</div>

<?php } ?>


<!-- =====================================================
     CERTIFICATES
===================================================== -->

<?php if ($section === 'certificates') { ?>

<div class="dashboard-card">

    <h1>
        <i class="fa-solid fa-certificate"></i>
        My Certificates
    </h1>

    <?php if (
        $certificates &&
        $certificates->num_rows > 0
    ) { ?>

        <div class="portfolio-certificates">

            <?php while (
                $certificate =
                $certificates->fetch_assoc()
            ) { ?>

                <div class="portfolio-certificate-card">

                    <h3>

                        <?php
                        echo htmlspecialchars(
                            $certificate[
                                'certificate_name'
                            ]
                        );
                        ?>

                    </h3>


                    <?php if (
                        !empty(
                            $certificate['issuer']
                        )
                    ) { ?>

                        <p>

                            <strong>
                                Issued By:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $certificate['issuer']
                            );
                            ?>

                        </p>

                    <?php } ?>


                    <?php if (
                        !empty(
                            $certificate['certificate_url']
                        )
                    ) { ?>

                        <a
                            href="<?php
                                echo htmlspecialchars(
                                    $certificate[
                                        'certificate_url'
                                    ]
                                );
                            ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="portfolio-certificate-link"
                        >

                            <i class="fa-solid fa-eye"></i>
                            View Certificate

                        </a>

                    <?php } ?>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <p>
            No certificates added yet.
        </p>

    <?php } ?>

</div>

<?php } ?>


<!-- =====================================================
     PUBLIC RESUME
===================================================== -->

<?php if ($section === 'resume') { ?>

<div class="public-resume">

    <div class="public-resume-header">

        <h1>
            <i class="fa-solid fa-file-lines"></i>
            Professional Resume
        </h1>

        <p>

            <?php
            echo htmlspecialchars(
                $user['fullname']
            );
            ?>

        </p>

    </div>


    <!-- PROFILE -->

    <div class="public-resume-profile">

        <?php

        $profilePhotoSrc =
            getProfilePhotoSrc(
                $user['profile_photo']
            );

        ?>

        <?php if ($profilePhotoSrc !== '') { ?>

            <img
                src="<?php
                    echo htmlspecialchars(
                        $profilePhotoSrc
                    );
                ?>"
                class="public-resume-photo"
                alt="Profile Photo"
            >

        <?php } else { ?>

            <div class="public-resume-default-photo">

                <i class="fa-solid fa-user"></i>

            </div>

        <?php } ?>


        <div>

            <h2>

                <?php
                echo htmlspecialchars(
                    $user['fullname']
                );
                ?>

            </h2>

            <p>

                @<?php
                echo htmlspecialchars(
                    $user['username']
                );
                ?>

            </p>

        </div>

    </div>


    <!-- BASIC INFORMATION -->

    <div class="public-resume-info">

        <?php if (
            !empty(
                $user['education']
            )
        ) { ?>

            <span>

                <i class="fa-solid fa-graduation-cap"></i>

                <?php
                echo htmlspecialchars(
                    $user['education']
                );
                ?>

            </span>

        <?php } ?>


        <?php if (
            !empty(
                $user['email']
            )
        ) { ?>

            <span>

                <i class="fa-solid fa-envelope"></i>

                <?php
                echo htmlspecialchars(
                    $user['email']
                );
                ?>

            </span>

        <?php } ?>


        <?php if (
            !empty(
                $user['phone']
            )
        ) { ?>

            <span>

                <i class="fa-solid fa-phone"></i>

                <?php
                echo htmlspecialchars(
                    $user['phone']
                );
                ?>

            </span>

        <?php } ?>

    </div>


    <!-- ABOUT -->

    <?php if (
        !empty(
            $user['career_objective']
        )
    ) { ?>

        <div class="public-resume-section">

            <h3>
                ABOUT / CAREER OBJECTIVE
            </h3>

            <p>

                <?php
                echo nl2br(
                    htmlspecialchars(
                        $user['career_objective']
                    )
                );
                ?>

            </p>

        </div>

    <?php } ?>


    <!-- EDUCATION -->

    <div class="public-resume-section">

        <h3>
            EDUCATION
        </h3>

        <?php if (
            $education &&
            $education->num_rows > 0
        ) { ?>

            <?php while (
                $edu =
                $education->fetch_assoc()
            ) { ?>

                <div class="public-resume-education">

                    <h4>

                        <?php
                        echo htmlspecialchars(
                            $edu['education_type']
                        );
                        ?>

                    </h4>

                    <strong>

                        <?php
                        echo htmlspecialchars(
                            $edu['institution_name']
                        );
                        ?>

                    </strong>


                    <?php if (
                        !empty(
                            $edu['course']
                        ) ||
                        !empty(
                            $edu['department']
                        )
                    ) { ?>

                        <p>

                            <?php
                            echo htmlspecialchars(
                                $edu['course']
                            );
                            ?>

                            <?php if (
                                !empty(
                                    $edu['department']
                                )
                            ) { ?>

                                &nbsp; | &nbsp;

                                <?php
                                echo htmlspecialchars(
                                    $edu['department']
                                );
                                ?>

                            <?php } ?>

                        </p>

                    <?php } ?>


                    <?php if (
                        !empty(
                            $edu['start_year']
                        ) ||
                        !empty(
                            $edu['end_year']
                        )
                    ) { ?>

                        <p>

                            <?php
                            echo htmlspecialchars(
                                $edu['start_year']
                            );
                            ?>

                            -

                            <?php
                            echo htmlspecialchars(
                                $edu['end_year']
                            );
                            ?>

                        </p>

                    <?php } ?>


                    <?php if (
                        !empty(
                            $edu['grade']
                        )
                    ) { ?>

                        <p>

                            <strong>
                                Grade:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $edu['grade']
                            );
                            ?>

                        </p>

                    <?php } ?>


                    <?php if (
                        !empty(
                            $edu['percentage']
                        )
                    ) { ?>

                        <p>

                            <strong>
                                Percentage:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $edu['percentage']
                            );
                            ?>

                        </p>

                    <?php } ?>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>
                No education details added.
            </p>

        <?php } ?>

    </div>


    <!-- SKILLS -->

    <?php if (
        $skills &&
        $skills->num_rows > 0
    ) { ?>

        <div class="public-resume-section">

            <h3>
                SKILLS
            </h3>

            <div class="public-resume-skills">

                <?php while (
                    $skill =
                    $skills->fetch_assoc()
                ) { ?>

                    <span class="public-resume-skill">

                        <i class="<?php
                            echo htmlspecialchars(
                                getSkillIcon(
                                    $skill['skill_name']
                                )
                            );
                        ?>"></i>

                        <?php
                        echo htmlspecialchars(
                            $skill['skill_name']
                        );
                        ?>

                        <?php if (
                            isset(
                                $skill['percentage']
                            )
                        ) { ?>

                            -
                            <?php
                            echo (int)
                                $skill['percentage'];
                            ?>%

                        <?php } ?>

                    </span>

                <?php } ?>

            </div>

        </div>

    <?php } ?>


    <!-- PROJECTS -->

    <div class="public-resume-section">

        <h3>
            PROJECTS
        </h3>

        <?php if (
            $projects &&
            $projects->num_rows > 0
        ) { ?>

            <?php while (
                $project =
                $projects->fetch_assoc()
            ) { ?>

                <div class="public-resume-project">

                    <h4>

                        <?php
                        echo htmlspecialchars(
                            $project['title']
                        );
                        ?>

                    </h4>


                    <?php if (
                        !empty(
                            $project['technologies']
                        )
                    ) { ?>

                        <p>

                            <strong>
                                Technologies:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $project['technologies']
                            );
                            ?>

                        </p>

                    <?php } ?>


                    <?php if (
                        !empty(
                            $project['description']
                        )
                    ) { ?>

                        <p>

                            <?php
                            echo nl2br(
                                htmlspecialchars(
                                    $project['description']
                                )
                            );
                            ?>

                        </p>

                    <?php } ?>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>
                No projects added.
            </p>

        <?php } ?>

    </div>


    <!-- CONTACT -->

    <div class="public-resume-section public-resume-contact">

        <h3>
            CONTACT
        </h3>

        <?php if (
            !empty($contacts)
        ) { ?>

            <?php foreach (
                $contacts as $contact
            ) { ?>


                <?php if (
                    !empty(
                        $contact['address']
                    )
                ) { ?>

                    <p>

                        <strong>
                            Address:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $contact['address']
                        );
                        ?>

                    </p>

                <?php } ?>


                <?php if (
                    !empty(
                        $contact['city']
                    )
                ) { ?>

                    <p>

                        <strong>
                            City:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $contact['city']
                        );
                        ?>

                    </p>

                <?php } ?>


                <?php if (
                    !empty(
                        $contact['state']
                    )
                ) { ?>

                    <p>

                        <strong>
                            State:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $contact['state']
                        );
                        ?>

                    </p>

                <?php } ?>


                <?php if (
                    !empty(
                        $contact['alternate_email']
                    )
                ) { ?>

                    <p>

                        <strong>
                            Alternate Email:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $contact[
                                'alternate_email'
                            ]
                        );
                        ?>

                    </p>

                <?php } ?>

            <?php } ?>

        <?php } ?>

    </div>


    <!-- ACTIONS -->

    <div class="public-resume-actions">

        <button
            type="button"
            class="public-resume-btn public-resume-download"
            onclick="window.print()"
        >

            <i class="fa-solid fa-download"></i>

            Download PDF

        </button>


        <button
            type="button"
            class="public-resume-btn public-resume-share"
            onclick="sharePublicResume()"
        >

            <i class="fa-solid fa-share-nodes"></i>

            Share Resume

        </button>

    </div>

</div>


<script>

function sharePublicResume()
{

    const resumeUrl =
        window.location.origin +
        "<?php
            echo portfolioLink(
                $username,
                'resume'
            );
        ?>";


    if (navigator.share) {

        navigator.share({

            title:
                "<?php
                echo htmlspecialchars(
                    $user['fullname']
                );
                ?> - Resume",

            text:
                "View my resume",

            url:
                resumeUrl

        }).catch(function(error) {

            console.log(error);

        });

    } else {

        navigator.clipboard
            .writeText(resumeUrl)
            .then(function() {

                alert(
                    "Resume link copied successfully!"
                );

            })
            .catch(function() {

                alert(
                    "Unable to copy resume link."
                );

            });

    }

}

</script>

<?php } ?>


<!-- =====================================================
     CONTACT
===================================================== -->

<?php if ($section === 'contact') { ?>

<div class="dashboard-card">

    <h1>
        <i class="fa-solid fa-envelope"></i>
        Contact
    </h1>

    <p>
        <strong>
            Contact Information
        </strong>
    </p>


    <?php if (
        !empty($contacts)
    ) { ?>

        <div class="portfolio-contact-list">

            <?php foreach (
                $contacts as $contact
            ) { ?>

                <div class="portfolio-contact-card">

                    <h3>

                        <i class="fa-solid fa-address-card"></i>

                        Contact Details

                    </h3>


                    <div class="portfolio-contact-info">


                        <?php if (
                            !empty(
                                $contact['address']
                            )
                        ) { ?>

                            <div class="portfolio-contact-row">

                                <strong>

                                    <i class="fa-solid fa-location-dot"></i>

                                    Address:

                                </strong>

                                <span>

                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $contact['address']
                                        )
                                    );
                                    ?>

                                </span>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty(
                                $contact['city']
                            ) ||
                            !empty(
                                $contact['state']
                            ) ||
                            !empty(
                                $contact['pincode']
                            )
                        ) { ?>

                            <div class="portfolio-contact-row">

                                <strong>

                                    <i class="fa-solid fa-map"></i>

                                    Location:

                                </strong>

                                <span>

                                    <?php

                                    if (
                                        !empty(
                                            $contact['city']
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            $contact['city']
                                        );

                                    }


                                    if (
                                        !empty(
                                            $contact['state']
                                        )
                                    ) {

                                        if (
                                            !empty(
                                                $contact['city']
                                            )
                                        ) {

                                            echo ", ";

                                        }

                                        echo htmlspecialchars(
                                            $contact['state']
                                        );

                                    }


                                    if (
                                        !empty(
                                            $contact['pincode']
                                        )
                                    ) {

                                        if (
                                            !empty(
                                                $contact['city']
                                            ) ||
                                            !empty(
                                                $contact['state']
                                            )
                                        ) {

                                            echo " - ";

                                        }

                                        echo htmlspecialchars(
                                            $contact['pincode']
                                        );

                                    }

                                    ?>

                                </span>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty(
                                $user['email']
                            )
                        ) { ?>

                            <div class="portfolio-contact-row">

                                <strong>

                                    <i class="fa-solid fa-envelope"></i>

                                    Email:

                                </strong>

                                <a
                                    href="mailto:<?php
                                        echo htmlspecialchars(
                                            $user['email']
                                        );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $user['email']
                                    );
                                    ?>

                                </a>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty(
                                $user['phone']
                            )
                        ) { ?>

                            <div class="portfolio-contact-row">

                                <strong>

                                    <i class="fa-solid fa-phone"></i>

                                    Phone:

                                </strong>

                                <a
                                    href="tel:<?php
                                        echo htmlspecialchars(
                                            $user['phone']
                                        );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $user['phone']
                                    );
                                    ?>

                                </a>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty(
                                $contact[
                                    'alternate_email'
                                ]
                            )
                        ) { ?>

                            <div class="portfolio-contact-row">

                                <strong>

                                    <i class="fa-solid fa-envelope-open"></i>

                                    Alternate Email:

                                </strong>

                                <a
                                    href="mailto:<?php
                                        echo htmlspecialchars(
                                            $contact[
                                                'alternate_email'
                                            ]
                                        );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $contact[
                                            'alternate_email'
                                        ]
                                    );
                                    ?>

                                </a>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty(
                                $contact['linkedin']
                            )
                        ) { ?>

                            <div class="portfolio-contact-row">

                                <strong>

                                    <i class="fa-brands fa-linkedin"></i>

                                    LinkedIn:

                                </strong>

                                <a
                                    href="<?php
                                        echo htmlspecialchars(
                                            $contact[
                                                'linkedin'
                                            ]
                                        );
                                    ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >

                                    View LinkedIn

                                </a>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty(
                                $contact['github']
                            )
                        ) { ?>

                            <div class="portfolio-contact-row">

                                <strong>

                                    <i class="fa-brands fa-github"></i>

                                    GitHub:

                                </strong>

                                <a
                                    href="<?php
                                        echo htmlspecialchars(
                                            $contact[
                                                'github'
                                            ]
                                        );
                                    ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >

                                    View GitHub

                                </a>

                            </div>

                        <?php } ?>


                        <?php if (
                            !empty(
                                $contact['website']
                            )
                        ) { ?>

                            <div class="portfolio-contact-row">

                                <strong>

                                    <i class="fa-solid fa-globe"></i>

                                    Website:

                                </strong>

                                <a
                                    href="<?php
                                        echo htmlspecialchars(
                                            $contact[
                                                'website'
                                            ]
                                        );
                                    ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >

                                    Visit Website

                                </a>

                            </div>

                        <?php } ?>


                    </div>

                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="no-contact">

            <i class="fa-solid fa-circle-info"></i>

            No contact details added yet.

        </div>

    <?php } ?>

</div>

<?php } ?>


</div>

</body>

</html>