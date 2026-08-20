<?php

include "config/db.php";


/* =====================================================
   GET USERNAME FROM URL

   Example:
   /vijiii/portfolio/viji
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

    $username = trim(
        $_GET['user']
    );

}


if ($username === '') {

    die("Portfolio not found.");

}


/* =====================================================
   GET SECTION
===================================================== */

$section = $_GET['section'] ?? 'about';


/* =====================================================
   ALLOWED SECTIONS
===================================================== */

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
   GET USER
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
   USER ID
===================================================== */

$user_id = (int)$user['id'];


/* =====================================================
   GET EDUCATION
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
   GET SKILLS
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
   GET PROJECTS
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
   GET CERTIFICATES
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
   GET CONTACT DETAILS
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
   PORTFOLIO LINK
===================================================== */

function portfolioLink(
    $username,
    $section
) {

    return
        "/vijiii/portfolio/" .
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


    <!-- EXISTING WEBSITE CSS -->

    <link
        rel="stylesheet"
        href="/vijiii/css/style.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


    <style>

        /* =================================================
           EDUCATION
        ================================================= */

        .portfolio-education-list {

            display: flex;

            flex-direction: column;

            gap: 20px;

            margin-top: 25px;

        }


        .portfolio-education-card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.08);

        }


        .portfolio-education-card h3 {

            margin: 0 0 8px;

            font-size: 21px;

            color: #111827;

        }


        .portfolio-education-card .institution {

            margin: 0 0 20px;

            font-size: 16px;

            font-weight: 600;

            color: #2196f3;

        }


        .education-detail {

            display: flex;

            flex-direction: column;

            gap: 10px;

            font-size: 14px;

            color: #374151;

        }


        .education-detail-row {

            padding: 9px 12px;

            background: #f8fafc;

            border-radius: 7px;

        }


        .education-detail-row strong {

            color: #111827;

            display: inline-block;

            min-width: 125px;

        }


        /* =================================================
           CONTACT
        ================================================= */

        .portfolio-contact-list {

            display: flex;

            flex-direction: column;

            gap: 20px;

            margin-top: 25px;

        }


        .portfolio-contact-card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.08);

        }


        .portfolio-contact-card h3 {

            margin: 0 0 18px;

            font-size: 20px;

            color: #111827;

        }


        .portfolio-contact-info {

            display: flex;

            flex-direction: column;

            gap: 12px;

        }


        .portfolio-contact-row {

            padding: 11px 14px;

            background: #f8fafc;

            border-radius: 8px;

            font-size: 14px;

            color: #374151;

            line-height: 1.6;

            word-break: break-word;

        }


        .portfolio-contact-row strong {

            color: #111827;

            display: inline-block;

            min-width: 145px;

        }


        .portfolio-contact-row a {

            color: #1976d2;

            text-decoration: none;

        }


        .portfolio-contact-row a:hover {

            text-decoration: underline;

        }


        .no-contact {

            padding: 20px;

            background: #f8fafc;

            border-radius: 10px;

            color: #4b5563;

        }


        /* =================================================
           RESUME
        ================================================= */

        .portfolio-resume-box {

            max-width: 900px;

            margin: 0 auto;

        }


        .resume-description {

            color: #4b5563;

            font-size: 15px;

            margin-top: 8px;

        }


        .resume-preview {

            margin-top: 25px;

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            padding: 35px 25px;

            text-align: center;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.08);

        }


        .resume-preview-icon {

            width: 70px;

            height: 70px;

            margin: 0 auto 15px;

            border-radius: 12px;

            background: #fef2f2;

            display: flex;

            align-items: center;

            justify-content: center;

        }


        .resume-preview-icon i {

            font-size: 35px;

            color: #dc2626;

        }


        .resume-preview h3 {

            margin: 10px 0 8px;

            color: #111827;

            font-size: 20px;

        }


        .resume-preview p {

            color: #6b7280;

            font-size: 14px;

            margin-bottom: 25px;

        }


        /* =================================================
           RESUME BUTTONS
        ================================================= */

        .resume-actions {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 12px;

            flex-wrap: wrap;

        }


        .resume-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            padding: 11px 18px;

            border-radius: 8px;

            border: none;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.2s ease;

        }


        .resume-btn:hover {

            transform: translateY(-2px);

            opacity: 0.92;

        }


        .view-btn {

            background: #2196f3;

            color: #ffffff;

        }


        .download-btn {

            background: #16a34a;

            color: #ffffff;

        }


        .share-btn {

            background: #7c3aed;

            color: #ffffff;

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 700px) {

            .portfolio-education-card,
            .portfolio-contact-card {

                padding: 18px;

            }


            .education-detail-row strong,
            .portfolio-contact-row strong {

                display: block;

                margin-bottom: 4px;

            }


            .portfolio-resume-box {

                width: 100%;

            }


            .resume-preview {

                padding: 28px 18px;

            }


            .resume-actions {

                flex-direction: column;

                width: 100%;

            }


            .resume-btn {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     PUBLIC PORTFOLIO SIDEBAR
===================================================== -->

<div class="sidebar">


    <!-- TITLE -->

    <h2>

        <i class="fa-solid fa-user"></i>

        My Portfolio

    </h2>


    <!-- ABOUT -->

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'about'
            );
        ?>"
        class="<?php
            echo (
                $section === 'about'
            )
            ? 'active'
            : '';
        ?>"
    >

        <i class="fa-solid fa-user"></i>

        <span>About</span>

    </a>


    <!-- EDUCATION -->

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'education'
            );
        ?>"
        class="<?php
            echo (
                $section === 'education'
            )
            ? 'active'
            : '';
        ?>"
    >

        <i class="fa-solid fa-graduation-cap"></i>

        <span>Education</span>

    </a>


    <!-- SKILLS -->

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'skills'
            );
        ?>"
        class="<?php
            echo (
                $section === 'skills'
            )
            ? 'active'
            : '';
        ?>"
    >

        <i class="fa-solid fa-code"></i>

        <span>Skills</span>

    </a>


    <!-- PROJECTS -->

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'projects'
            );
        ?>"
        class="<?php
            echo (
                $section === 'projects'
            )
            ? 'active'
            : '';
        ?>"
    >

        <i class="fa-solid fa-folder"></i>

        <span>Projects</span>

    </a>


    <!-- CERTIFICATES -->

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'certificates'
            );
        ?>"
        class="<?php
            echo (
                $section === 'certificates'
            )
            ? 'active'
            : '';
        ?>"
    >

        <i class="fa-solid fa-certificate"></i>

        <span>Certificates</span>

    </a>


    <!-- =================================================
         RESUME

         IMPORTANT:
         This stays inside portfolio.php.
         Sidebar will NOT disappear.
    ================================================= -->

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'resume'
            );
        ?>"
        class="<?php
            echo (
                $section === 'resume'
            )
            ? 'active'
            : '';
        ?>"
    >

        <i class="fa-solid fa-file-alt"></i>

        <span>Resume</span>

    </a>


    <!-- CONTACT -->

    <a
        href="<?php
            echo portfolioLink(
                $username,
                'contact'
            );
        ?>"
        class="<?php
            echo (
                $section === 'contact'
            )
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


        <!-- PROFILE PHOTO -->

        <?php if (
            !empty(
                $user['profile_photo']
            )
        ) { ?>

            <img
                src="/vijiii/uploads/profile/<?php
                    echo htmlspecialchars(
                        $user['profile_photo']
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


        <!-- FULL NAME -->

        <p>

            <strong>
                Full Name:
            </strong>

            <?php
            echo htmlspecialchars(
                $user['fullname'] ?? ''
            );
            ?>

        </p>


        <!-- EMAIL -->

        <p>

            <strong>
                Email:
            </strong>

            <?php
            echo htmlspecialchars(
                $user['email'] ?? ''
            );
            ?>

        </p>


        <!-- PHONE -->

        <p>

            <strong>
                Phone:
            </strong>

            <?php
            echo htmlspecialchars(
                $user['phone'] ?? ''
            );
            ?>

        </p>


        <!-- CAREER OBJECTIVE -->

        <p>

            <strong>
                Career Objective:
            </strong>

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
                    $edu = $education->fetch_assoc()
                ) { ?>


                    <div class="portfolio-education-card">


                        <!-- EDUCATION TYPE -->

                        <h3>

                            <i class="fa-solid fa-graduation-cap"></i>

                            <?php
                            echo htmlspecialchars(
                                $edu['education_type']
                            );
                            ?>

                        </h3>


                        <!-- INSTITUTION -->

                        <p class="institution">

                            <?php
                            echo htmlspecialchars(
                                $edu['institution_name']
                            );
                            ?>

                        </p>


                        <!-- DETAILS -->

                        <div class="education-detail">


                            <?php if (
                                !empty(
                                    $edu['course']
                                )
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
                                !empty(
                                    $edu['department']
                                )
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
                                !empty(
                                    $edu['start_year']
                                )
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
                                !empty(
                                    $edu['end_year']
                                )
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
                                !empty(
                                    $edu['percentage']
                                )
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
                                !empty(
                                    $edu['grade']
                                )
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


        <?php
        if (
            $skills &&
            $skills->num_rows > 0
        ) {
        ?>


            <div class="skill-list">


                <?php
                while (
                    $skill =
                    $skills->fetch_assoc()
                ) {
                ?>


                    <?php

                    $percentage =
                        (int)
                        $skill['percentage'];


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


                        <i
                            class="<?php
                                echo htmlspecialchars(
                                    $skillIcon
                                );
                            ?>"
                        ></i>


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


        <?php
        } else {
        ?>


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


        <?php
        if (
            $projects &&
            $projects->num_rows > 0
        ) {
        ?>


            <div class="portfolio-projects">


                <?php
                while (
                    $project =
                    $projects->fetch_assoc()
                ) {
                ?>


                    <div
                        class="portfolio-project-card"
                    >


                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $project['title']
                                ?? ''
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
                                        $project[
                                            'description'
                                        ]
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
                                    $project[
                                        'technologies'
                                    ]
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
                                        $project[
                                            'demo_link'
                                        ]
                                    );
                                ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="portfolio-project-link"
                            >

                                <i
                                    class="fa-solid fa-eye"
                                ></i>

                                View Project

                            </a>

                        <?php } ?>


                    </div>


                <?php } ?>


            </div>


        <?php
        } else {
        ?>


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


        <?php
        if (
            $certificates &&
            $certificates->num_rows > 0
        ) {
        ?>


            <div class="portfolio-certificates">


                <?php
                while (
                    $certificate =
                    $certificates->fetch_assoc()
                ) {
                ?>


                    <div
                        class="portfolio-certificate-card"
                    >


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
                                    $certificate[
                                        'issuer'
                                    ]
                                );
                                ?>

                            </p>

                        <?php } ?>


                        <?php if (
                            !empty(
                                $certificate[
                                    'certificate_url'
                                ]
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

                                <i
                                    class="fa-solid fa-eye"
                                ></i>

                                View Certificate

                            </a>

                        <?php } ?>


                    </div>


                <?php } ?>


            </div>


        <?php
        } else {
        ?>


            <p>
                No certificates added yet.
            </p>


        <?php } ?>


    </div>


<?php } ?>


<!-- =====================================================
     RESUME

     IMPORTANT:
     Resume stays inside the SAME portfolio page.
     Sidebar remains visible.
===================================================== -->

<?php if ($section === 'resume') { ?>


    <div class="dashboard-card portfolio-resume-box">


        <h1>

            <i class="fa-solid fa-file-alt"></i>

            Resume

        </h1>


        <p class="resume-description">

            View
            <strong>

                <?php
                echo htmlspecialchars(
                    $user['fullname']
                );
                ?>

            </strong>

            's resume.

        </p>


        <!-- =================================================
             RESUME PREVIEW BOX
        ================================================= -->

        <div class="resume-preview">


            <div class="resume-preview-icon">

                <i class="fa-solid fa-file-pdf"></i>

            </div>


            <h3>

                <?php
                echo htmlspecialchars(
                    $user['fullname']
                );
                ?>

                - Resume

            </h3>


            <p>

                Click below to view, download or
                share the resume.

            </p>


            <!-- =================================================
                 BUTTONS
            ================================================= -->

            <div class="resume-actions">


                <!-- VIEW RESUME -->

                <a
                    href="/vijiii/resume.php?user=<?php
                        echo urlencode(
                            $username
                        );
                    ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="resume-btn view-btn"
                >

                    <i class="fa-solid fa-eye"></i>

                    View Resume

                </a>


                <!-- DOWNLOAD -->

                <a
                    href="/vijiii/resume.php?user=<?php
                        echo urlencode(
                            $username
                        );
                    ?>"
                    download
                    class="resume-btn download-btn"
                >

                    <i class="fa-solid fa-download"></i>

                    Download

                </a>


                <!-- SHARE -->

                <button
                    type="button"
                    class="resume-btn share-btn"
                    onclick="shareResume()"
                >

                    <i class="fa-solid fa-share-nodes"></i>

                    Share

                </button>


            </div>


        </div>


    </div>


    <!-- =================================================
         SHARE SCRIPT
    ================================================= -->

    <script>

        function shareResume() {

            const resumeUrl =
                window.location.origin +
                "/vijiii/resume.php?user=<?php
                    echo rawurlencode(
                        $username
                    );
                ?>";


            if (
                navigator.share
            ) {

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

                }).catch(
                    function(error) {

                        console.log(error);

                    }
                );

            } else {

                navigator.clipboard
                    .writeText(
                        resumeUrl
                    )
                    .then(
                        function() {

                            alert(
                                "Resume link copied successfully!"
                            );

                        }
                    )
                    .catch(
                        function() {

                            alert(
                                "Unable to copy resume link."
                            );

                        }
                    );

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


        <?php if (!empty($contacts)) { ?>


            <div class="portfolio-contact-list">


                <?php foreach (
                    $contacts
                    as $contact
                ) { ?>


                    <div class="portfolio-contact-card">


                        <h3>

                            <i
                                class="fa-solid fa-address-card"
                            ></i>

                            Contact Details

                        </h3>


                        <div class="portfolio-contact-info">


                            <!-- ADDRESS -->

                            <?php if (
                                !empty(
                                    $contact['address']
                                )
                            ) { ?>

                                <div
                                    class="portfolio-contact-row"
                                >

                                    <strong>

                                        <i
                                            class="fa-solid fa-location-dot"
                                        ></i>

                                        Address:

                                    </strong>


                                    <span>

                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $contact[
                                                    'address'
                                                ]
                                            )
                                        );
                                        ?>

                                    </span>

                                </div>

                            <?php } ?>


                            <!-- CITY / STATE / PINCODE -->

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


                                <div
                                    class="portfolio-contact-row"
                                >

                                    <strong>

                                        <i
                                            class="fa-solid fa-map"
                                        ></i>

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


                            <!-- MAIN EMAIL -->

                            <?php if (
                                !empty(
                                    $user['email']
                                )
                            ) { ?>

                                <div
                                    class="portfolio-contact-row"
                                >

                                    <strong>

                                        <i
                                            class="fa-solid fa-envelope"
                                        ></i>

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


                            <!-- PHONE -->

                            <?php if (
                                !empty(
                                    $user['phone']
                                )
                            ) { ?>

                                <div
                                    class="portfolio-contact-row"
                                >

                                    <strong>

                                        <i
                                            class="fa-solid fa-phone"
                                        ></i>

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


                            <!-- ALTERNATE EMAIL -->

                            <?php if (
                                !empty(
                                    $contact[
                                        'alternate_email'
                                    ]
                                )
                            ) { ?>

                                <div
                                    class="portfolio-contact-row"
                                >

                                    <strong>

                                        <i
                                            class="fa-solid fa-envelope-open"
                                        ></i>

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


                            <!-- LINKEDIN -->

                            <?php if (
                                !empty(
                                    $contact['linkedin']
                                )
                            ) { ?>

                                <div
                                    class="portfolio-contact-row"
                                >

                                    <strong>

                                        <i
                                            class="fa-brands fa-linkedin"
                                        ></i>

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


                            <!-- GITHUB -->

                            <?php if (
                                !empty(
                                    $contact['github']
                                )
                            ) { ?>

                                <div
                                    class="portfolio-contact-row"
                                >

                                    <strong>

                                        <i
                                            class="fa-brands fa-github"
                                        ></i>

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


                            <!-- WEBSITE -->

                            <?php if (
                                !empty(
                                    $contact['website']
                                )
                            ) { ?>

                                <div
                                    class="portfolio-contact-row"
                                >

                                    <strong>

                                        <i
                                            class="fa-solid fa-globe"
                                        ></i>

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

                <i
                    class="fa-solid fa-circle-info"
                ></i>

                No contact details added yet.

            </div>


        <?php } ?>


    </div>


<?php } ?>


</div>


</body>

</html>