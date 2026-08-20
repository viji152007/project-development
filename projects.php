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


/* =========================================
   DATABASE CHECK
========================================= */

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}


/* =========================================
   GET LOGGED-IN USER
========================================= */

$username = $_SESSION['username'];

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
   GET PROJECTS
========================================= */

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

if (!$stmt) {
    die("Project query error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$projects = [];

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Projects</title>


    <!-- MAIN CSS -->

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
           PROJECT PAGE
        ========================================= */

        .projects-page {

            width: 100%;

            max-width: 1100px;

            margin: 0 auto;

            padding: 35px 30px;

            box-sizing: border-box;

        }


        /* =========================================
           HEADER
        ========================================= */

        .projects-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 30px;

        }


        .projects-title h2 {

            margin: 0 0 7px 0;

            font-size: 28px;

        }


        .projects-title p {

            margin: 0;

            opacity: 0.7;

        }


        /* =========================================
           ADD PROJECT BUTTON
        ========================================= */

        .add-project-btn {

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


        .add-project-btn:hover {

            background: #1976d2;

            transform: translateY(-1px);

        }


        /* =========================================
           PROJECT GRID
        ========================================= */

        .projects-grid {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 22px;

        }


        /* =========================================
           PROJECT CARD - WHITE THEME
        ========================================= */

        .project-card {

            padding: 24px;

            border-radius: 14px;

            background: #ffffff;

            border: 1px solid #e5e7eb;

            box-sizing: border-box;

            transition: 0.3s;

            color: #1f2937;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

        }


        .project-card:hover {

            transform: translateY(-3px);

            border-color: #2196f3;

            box-shadow:
                0 8px 22px rgba(0, 0, 0, 0.12);

        }


        /* =========================================
           PROJECT TITLE
        ========================================= */

        .project-card h3 {

            margin: 0 0 12px 0;

            font-size: 21px;

            color: #111827;

        }


        /* =========================================
           DESCRIPTION
        ========================================= */

        .project-description {

            margin: 0 0 16px 0;

            line-height: 1.6;

            color: #4b5563;

        }


        /* =========================================
           TECHNOLOGIES
        ========================================= */

        .project-tech {

            margin-bottom: 18px;

            padding: 10px 12px;

            border-radius: 8px;

            background: #f0f7ff;

            border: 1px solid #dbeafe;

            font-size: 14px;

            color: #374151;

        }


        .project-tech strong {

            color: #2196f3;

        }


        /* =========================================
           DEMO LINK
        ========================================= */

        .project-links {

            display: flex;

            flex-wrap: wrap;

            gap: 9px;

            margin-bottom: 18px;

        }


        .project-link {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            padding: 8px 12px;

            border-radius: 7px;

            background: #f3f4f6;

            color: #2563eb;

            text-decoration: none;

            font-size: 14px;

            transition: 0.3s;

        }


        .project-link:hover {

            background: #e5e7eb;

        }


        /* =========================================
           ACTION BUTTONS
        ========================================= */

        .project-actions {

            display: flex;

            gap: 10px;

            border-top: 1px solid #e5e7eb;

            padding-top: 16px;

        }


        .edit-btn,
        .delete-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 9px 15px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            transition: 0.3s;

        }


        /* =========================================
           EDIT BUTTON
        ========================================= */

        .edit-btn {

            background: #e8f3ff;

            color: #1976d2;

            border: 1px solid #bfdbfe;

        }


        .edit-btn:hover {

            background: #dbeafe;

        }


        /* =========================================
           DELETE BUTTON
        ========================================= */

        .delete-btn {

            background: #fff1f2;

            color: #dc2626;

            border: 1px solid #fecdd3;

        }


        .delete-btn:hover {

            background: #ffe4e6;

        }


        /* =========================================
           NO PROJECTS
        ========================================= */

        .no-projects {

            padding: 55px 25px;

            text-align: center;

            border-radius: 14px;

            background: #ffffff;

            border: 1px solid #e5e7eb;

            color: #1f2937;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

        }


        .no-projects i {

            font-size: 45px;

            margin-bottom: 15px;

            opacity: 0.5;

        }


        .no-projects h3 {

            margin: 0 0 8px 0;

        }


        .no-projects p {

            margin: 0 0 20px 0;

            opacity: 0.7;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 800px) {

            .projects-grid {

                grid-template-columns: 1fr;

            }

        }


        @media (max-width: 600px) {

            .projects-page {

                padding: 25px 18px;

            }


            .projects-header {

                flex-direction: column;

                align-items: flex-start;

            }


            .add-project-btn {

                width: 100%;

            }


            .project-card {

                padding: 20px;

            }


            .project-actions {

                flex-direction: column;

            }


            .edit-btn,
            .delete-btn {

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


    <div class="projects-page">


        <!-- =====================================
             PAGE HEADER
        ====================================== -->

        <div class="projects-header">

            <div class="projects-title">

                <h2>My Projects</h2>

                <p>
                    Manage your portfolio projects.
                </p>

            </div>


    <a href="add_project.php" class="add-project-btn">
    <i class="fa-solid fa-plus"></i>
    Add Project
</a>

        </div>


        <!-- =====================================
             PROJECT LIST
        ====================================== -->

        <?php if (!empty($projects)): ?>

            <div class="projects-grid">


                <?php foreach ($projects as $project): ?>

                    <div class="project-card">


                        <!-- PROJECT TITLE -->

                        <h3>

                            <?= htmlspecialchars(
                                $project['title'] ?? ''
                            ); ?>

                        </h3>


                        <!-- DESCRIPTION -->

                        <p class="project-description">

                            <?= nl2br(
                                htmlspecialchars(
                                    $project['description'] ?? ''
                                )
                            ); ?>

                        </p>


                        <!-- TECHNOLOGIES -->

                        <?php if (!empty($project['technologies'])): ?>

                            <div class="project-tech">

                                <strong>
                                    Technologies:
                                </strong>

                                <?= htmlspecialchars(
                                    $project['technologies']
                                ); ?>

                            </div>

                        <?php endif; ?>


                        <!-- DEMO -->

                        <?php if (!empty($project['demo_link'])): ?>

                            <div class="project-links">

                                <a
                                    href="<?= htmlspecialchars($project['demo_link']); ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="project-link"
                                >

                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>

                                    Demo

                                </a>

                            </div>

                        <?php endif; ?>


                        <!-- ACTIONS -->

                        <div class="project-actions">


                            <a
                                href="edit_project.php?id=<?= (int)$project['id']; ?>"
                                class="edit-btn"
                            >

                                <i class="fa-solid fa-pen"></i>

                                Edit

                            </a>


                            <a
                                href="delete_project.php?id=<?= (int)$project['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this project?');"
                            >

                                <i class="fa-solid fa-trash"></i>

                                Delete

                            </a>


                        </div>


                    </div>

                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <!-- =====================================
                 NO PROJECTS
            ====================================== -->

            <div class="no-projects">

                <i class="fa-solid fa-folder-open"></i>

                <h3>
                    No Projects Found
                </h3>

                <p>
                    You haven't added any projects yet.
                </p>


                <a
                    href="add_project.php"
                    class="add-project-btn"
                >

                    <i class="fa-solid fa-plus"></i>

                    Add Your First Project

                </a>

            </div>


        <?php endif; ?>


    </div>

</div>


</body>

</html>