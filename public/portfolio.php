<?php

include("../config/db.php");

/* =========================================
   GET USERNAME
========================================= */

if (!isset($_GET['username']) || empty($_GET['username'])) {
    die("Portfolio not found.");
}

$username = trim($_GET['username']);

/* =========================================
   GET SECTION
========================================= */

$section = $_GET['section'] ?? 'about';


/* =========================================
   GET USER DETAILS
========================================= */

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
$user = $result->fetch_assoc();

if (!$user) {
    die("Portfolio not found.");
}

$user_id = (int)$user['id'];


/* =========================================
   GET SKILLS
========================================= */

$skill_stmt = $conn->prepare("
    SELECT skill_name, skill_level, percentage
    FROM skills
    WHERE user_id = ?
    ORDER BY id DESC
");

$skill_stmt->bind_param("i", $user_id);
$skill_stmt->execute();

$skills = $skill_stmt->get_result();


/* =========================================
   GET EDUCATION
========================================= */

$edu_stmt = $conn->prepare("
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
    ORDER BY id DESC
");

$edu_stmt->bind_param("i", $user_id);
$edu_stmt->execute();

$education = $edu_stmt->get_result();


/* =========================================
   GET PROJECTS
========================================= */

$project_stmt = $conn->prepare("
    SELECT
        title,
        description,
        technologies,
        demo_link
    FROM projects
    WHERE user_id = ?
    ORDER BY id DESC
");

$project_stmt->bind_param("i", $user_id);
$project_stmt->execute();

$projects = $project_stmt->get_result();


/* =========================================
   GET CERTIFICATES
========================================= */

$certificate_stmt = $conn->prepare("
    SELECT
        certificate_name,
        issuer,
        certificate_url
    FROM certificates
    WHERE user_id = ?
    ORDER BY id DESC
");

$certificate_stmt->bind_param("i", $user_id);
$certificate_stmt->execute();

$certificates = $certificate_stmt->get_result();


/* =========================================
   GET CONTACT
========================================= */

$contact_stmt = $conn->prepare("
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

$contact_stmt->bind_param("i", $user_id);
$contact_stmt->execute();

$contact = $contact_stmt->get_result()->fetch_assoc();

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
        <?php echo htmlspecialchars($user['fullname']); ?> - Portfolio
    </title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>


<!-- =========================================
     PUBLIC PORTFOLIO SIDEBAR
========================================= -->

<div class="sidebar">

    <h2>
        My Portfolio
    </h2>

    <a href="?username=<?php echo urlencode($user['username']); ?>&section=about">
        About
    </a>

    <a href="?username=<?php echo urlencode($user['username']); ?>&section=education">
        Education
    </a>

    <a href="?username=<?php echo urlencode($user['username']); ?>&section=skills">
        Skills
    </a>

    <a href="?username=<?php echo urlencode($user['username']); ?>&section=projects">
        Projects
    </a>

    <a href="?username=<?php echo urlencode($user['username']); ?>&section=certificates">
        Certificates
    </a>

    <a href="?username=<?php echo urlencode($user['username']); ?>&section=resume">
        Resume
    </a>

    <a href="?username=<?php echo urlencode($user['username']); ?>&section=contact">
        Contact
    </a>

</div>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


<?php if ($section === 'about'): ?>


    <h1>
        <?php echo htmlspecialchars($user['fullname']); ?>
    </h1>

    <p>
        @<?php echo htmlspecialchars($user['username']); ?>
    </p>

    <?php if (!empty($user['career_objective'])): ?>

        <h2>Career Objective</h2>

        <p>
            <?php
            echo nl2br(
                htmlspecialchars($user['career_objective'])
            );
            ?>
        </p>

    <?php endif; ?>


<?php elseif ($section === 'education'): ?>


    <h1>Education</h1>

    <?php if ($education->num_rows > 0): ?>

        <?php while ($edu = $education->fetch_assoc()): ?>

            <div class="card">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $edu['education_type']
                    );
                    ?>
                </h2>

                <p>
                    <strong>Institution:</strong>
                    <?php
                    echo htmlspecialchars(
                        $edu['institution_name']
                    );
                    ?>
                </p>

                <p>
                    <strong>Course:</strong>
                    <?php
                    echo htmlspecialchars(
                        $edu['course']
                    );
                    ?>
                </p>

                <?php if (!empty($edu['department'])): ?>

                    <p>
                        <strong>Department:</strong>
                        <?php
                        echo htmlspecialchars(
                            $edu['department']
                        );
                        ?>
                    </p>

                <?php endif; ?>

                <p>
                    <strong>Year:</strong>
                    <?php
                    echo htmlspecialchars($edu['start_year']);
                    ?>
                    -
                    <?php
                    echo htmlspecialchars($edu['end_year']);
                    ?>
                </p>

                <?php if (!empty($edu['percentage'])): ?>

                    <p>
                        <strong>Percentage:</strong>
                        <?php
                        echo htmlspecialchars(
                            $edu['percentage']
                        );
                        ?>
                    </p>

                <?php endif; ?>

                <?php if (!empty($edu['grade'])): ?>

                    <p>
                        <strong>Grade:</strong>
                        <?php
                        echo htmlspecialchars(
                            $edu['grade']
                        );
                        ?>
                    </p>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No education details available.</p>

    <?php endif; ?>


<?php elseif ($section === 'skills'): ?>


    <h1>Skills</h1>

    <?php if ($skills->num_rows > 0): ?>

        <?php while ($skill = $skills->fetch_assoc()): ?>

            <div class="card">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $skill['skill_name']
                    );
                    ?>
                </h2>

                <p>
                    Level:
                    <?php
                    echo htmlspecialchars(
                        $skill['skill_level']
                    );
                    ?>
                </p>

                <?php if (!empty($skill['percentage'])): ?>

                    <p>
                        Percentage:
                        <?php
                        echo htmlspecialchars(
                            $skill['percentage']
                        );
                        ?>%
                    </p>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No skills available.</p>

    <?php endif; ?>


<?php elseif ($section === 'projects'): ?>


    <h1>Projects</h1>

    <?php if ($projects->num_rows > 0): ?>

        <?php while ($project = $projects->fetch_assoc()): ?>

            <div class="card">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $project['title']
                    );
                    ?>
                </h2>

                <p>
                    <?php
                    echo nl2br(
                        htmlspecialchars(
                            $project['description']
                        )
                    );
                    ?>
                </p>

                <?php if (!empty($project['technologies'])): ?>

                    <p>
                        <strong>Technologies:</strong>
                        <?php
                        echo htmlspecialchars(
                            $project['technologies']
                        );
                        ?>
                    </p>

                <?php endif; ?>

                <?php if (!empty($project['demo_link'])): ?>

                    <a
                        href="<?php echo htmlspecialchars($project['demo_link']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        View Demo
                    </a>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No projects available.</p>

    <?php endif; ?>


<?php elseif ($section === 'certificates'): ?>


    <h1>Certificates</h1>

    <?php if ($certificates->num_rows > 0): ?>

        <?php while ($certificate = $certificates->fetch_assoc()): ?>

            <div class="card">

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $certificate['certificate_name']
                    );
                    ?>
                </h2>

                <?php if (!empty($certificate['issuer'])): ?>

                    <p>
                        <strong>Issuer:</strong>
                        <?php
                        echo htmlspecialchars(
                            $certificate['issuer']
                        );
                        ?>
                    </p>

                <?php endif; ?>

                <?php if (!empty($certificate['certificate_url'])): ?>

                    <a
                        href="<?php echo htmlspecialchars($certificate['certificate_url']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        View Certificate
                    </a>

                <?php endif; ?>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No certificates available.</p>

    <?php endif; ?>


<?php elseif ($section === 'resume'): ?>


    <!-- =====================================
         PUBLIC USER RESUME
    ====================================== -->

    <h1>
        <?php
        echo htmlspecialchars($user['fullname']);
        ?> - Resume
    </h1>

    <p>
        View
        <strong>
            <?php
            echo htmlspecialchars($user['username']);
            ?>
        </strong>
        's resume.
    </p>


    <div class="card">

        <h2>
            <?php
            echo htmlspecialchars($user['fullname']);
            ?>
            - Resume
        </h2>

        <p>
            <?php
            echo htmlspecialchars($user['email']);
            ?>
        </p>

        <?php if (!empty($user['phone'])): ?>

            <p>
                <?php
                echo htmlspecialchars($user['phone']);
                ?>
            </p>

        <?php endif; ?>


        <!-- CAREER OBJECTIVE -->

        <?php if (!empty($user['career_objective'])): ?>

            <hr>

            <h3>Career Objective</h3>

            <p>
                <?php
                echo nl2br(
                    htmlspecialchars(
                        $user['career_objective']
                    )
                );
                ?>
            </p>

        <?php endif; ?>


        <!-- EDUCATION -->

        <hr>

        <h3>Education</h3>

        <?php if ($education->num_rows > 0): ?>

            <?php while ($edu = $education->fetch_assoc()): ?>

                <div>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $edu['education_type']
                        );
                        ?>
                    </strong>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $edu['institution_name']
                        );
                        ?>
                    </p>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $edu['course']
                        );
                        ?>
                    </p>

                    <?php if (!empty($edu['department'])): ?>

                        <p>
                            Department:
                            <?php
                            echo htmlspecialchars(
                                $edu['department']
                            );
                            ?>
                        </p>

                    <?php endif; ?>

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

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No education details available.</p>

        <?php endif; ?>


        <!-- PROJECTS -->

        <hr>

        <h3>Projects</h3>

        <?php if ($projects->num_rows > 0): ?>

            <?php while ($project = $projects->fetch_assoc()): ?>

                <div>

                    <h4>
                        <?php
                        echo htmlspecialchars(
                            $project['title']
                        );
                        ?>
                    </h4>

                    <p>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $project['description']
                            )
                        );
                        ?>
                    </p>

                    <?php if (!empty($project['technologies'])): ?>

                        <p>
                            <strong>Technologies:</strong>
                            <?php
                            echo htmlspecialchars(
                                $project['technologies']
                            );
                            ?>
                        </p>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No projects available.</p>

        <?php endif; ?>


        <!-- CERTIFICATES -->

        <hr>

        <h3>Certificates</h3>

        <?php if ($certificates->num_rows > 0): ?>

            <?php while ($certificate = $certificates->fetch_assoc()): ?>

                <div>

                    <h4>
                        <?php
                        echo htmlspecialchars(
                            $certificate['certificate_name']
                        );
                        ?>
                    </h4>

                    <?php if (!empty($certificate['issuer'])): ?>

                        <p>
                            Issuer:
                            <?php
                            echo htmlspecialchars(
                                $certificate['issuer']
                            );
                            ?>
                        </p>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No certificates available.</p>

        <?php endif; ?>


        <!-- CONTACT -->

        <hr>

        <h3>Contact</h3>

        <?php if ($contact): ?>

            <?php if (!empty($contact['address'])): ?>

                <p>
                    Address:
                    <?php
                    echo htmlspecialchars(
                        $contact['address']
                    );
                    ?>
                </p>

            <?php endif; ?>

            <?php if (!empty($contact['city'])): ?>

                <p>
                    City:
                    <?php
                    echo htmlspecialchars(
                        $contact['city']
                    );
                    ?>
                </p>

            <?php endif; ?>

            <?php if (!empty($contact['state'])): ?>

                <p>
                    State:
                    <?php
                    echo htmlspecialchars(
                        $contact['state']
                    );
                    ?>
                </p>

            <?php endif; ?>

            <?php if (!empty($contact['pincode'])): ?>

                <p>
                    Pincode:
                    <?php
                    echo htmlspecialchars(
                        $contact['pincode']
                    );
                    ?>
                </p>

            <?php endif; ?>

            <?php if (!empty($contact['alternate_email'])): ?>

                <p>
                    Email:
                    <?php
                    echo htmlspecialchars(
                        $contact['alternate_email']
                    );
                    ?>
                </p>

            <?php endif; ?>

            <?php if (!empty($contact['linkedin'])): ?>

                <p>
                    <a
                        href="<?php echo htmlspecialchars($contact['linkedin']); ?>"
                        target="_blank"
                    >
                        LinkedIn
                    </a>
                </p>

            <?php endif; ?>

            <?php if (!empty($contact['github'])): ?>

                <p>
                    <a
                        href="<?php echo htmlspecialchars($contact['github']); ?>"
                        target="_blank"
                    >
                        GitHub
                    </a>
                </p>

            <?php endif; ?>

            <?php if (!empty($contact['website'])): ?>

                <p>
                    <a
                        href="<?php echo htmlspecialchars($contact['website']); ?>"
                        target="_blank"
                    >
                        Website
                    </a>
                </p>

            <?php endif; ?>

        <?php else: ?>

            <p>No contact details available.</p>

        <?php endif; ?>

    </div>


<?php elseif ($section === 'contact'): ?>


    <h1>Contact</h1>

    <?php if ($contact): ?>

        <?php if (!empty($contact['address'])): ?>

            <p>
                Address:
                <?php
                echo htmlspecialchars(
                    $contact['address']
                );
                ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($contact['city'])): ?>

            <p>
                City:
                <?php
                echo htmlspecialchars(
                    $contact['city']
                );
                ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($contact['state'])): ?>

            <p>
                State:
                <?php
                echo htmlspecialchars(
                    $contact['state']
                );
                ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($contact['pincode'])): ?>

            <p>
                Pincode:
                <?php
                echo htmlspecialchars(
                    $contact['pincode']
                );
                ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($contact['alternate_email'])): ?>

            <p>
                Email:
                <?php
                echo htmlspecialchars(
                    $contact['alternate_email']
                );
                ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($contact['linkedin'])): ?>

            <p>
                <a
                    href="<?php echo htmlspecialchars($contact['linkedin']); ?>"
                    target="_blank"
                >
                    LinkedIn
                </a>
            </p>

        <?php endif; ?>

        <?php if (!empty($contact['github'])): ?>

            <p>
                <a
                    href="<?php echo htmlspecialchars($contact['github']); ?>"
                    target="_blank"
                >
                    GitHub
                </a>
            </p>

        <?php endif; ?>

        <?php if (!empty($contact['website'])): ?>

            <p>
                <a
                    href="<?php echo htmlspecialchars($contact['website']); ?>"
                    target="_blank"
                >
                    Website
                </a>
            </p>

        <?php endif; ?>

    <?php else: ?>

        <p>No contact details available.</p>

    <?php endif; ?>


<?php endif; ?>


</div>

</body>

</html>