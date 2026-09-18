<?php
session_start();

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
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
   GET PROJECT ID
========================================= */

$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($project_id <= 0) {
    header("Location: projects.php");
    exit();
}


/* =========================================
   DELETE PROJECT
   DELETE DIRECTLY FROM DATABASE
========================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_project'])
) {

    $delete_id = isset($_POST['project_id'])
        ? (int)$_POST['project_id']
        : 0;

    if ($delete_id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM projects
            WHERE id = ?
              AND user_id = ?
        ");

        if (!$stmt) {
            die("Delete query error: " . $conn->error);
        }

        $stmt->bind_param("ii", $delete_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: projects.php?deleted=1");
    exit();
}


/* =========================================
   UPDATE PROJECT
========================================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['update_project'])
) {

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $technologies = trim($_POST['technologies'] ?? '');
    $demo_link = trim($_POST['demo_link'] ?? '');


    /* =====================================
       VALIDATION
    ===================================== */

    if ($title === '') {

        $error = "Project title is required.";

    } else {

        $stmt = $conn->prepare("
            UPDATE projects
            SET
                title = ?,
                description = ?,
                technologies = ?,
                demo_link = ?
            WHERE id = ?
              AND user_id = ?
        ");

        if (!$stmt) {
            die("Update query error: " . $conn->error);
        }

        $stmt->bind_param(
            "ssssii",
            $title,
            $description,
            $technologies,
            $demo_link,
            $project_id,
            $user_id
        );

        if ($stmt->execute()) {

            $stmt->close();

            header("Location: projects.php?updated=1");
            exit();

        } else {

            $error = "Failed to update project.";

            $stmt->close();
        }
    }

} else {


    /* =====================================
       GET PROJECT DETAILS
       ONLY CURRENT USER PROJECT
    ===================================== */

    $stmt = $conn->prepare("
        SELECT
            id,
            title,
            description,
            technologies,
            demo_link
        FROM projects
        WHERE id = ?
          AND user_id = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("Project query error: " . $conn->error);
    }

    $stmt->bind_param("ii", $project_id, $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $project = $result->fetch_assoc();

    $stmt->close();


    if (!$project) {
        header("Location: projects.php");
        exit();
    }


    /* =====================================
       SET FORM VALUES
    ===================================== */

    $title = $project['title'] ?? '';
    $description = $project['description'] ?? '';
    $technologies = $project['technologies'] ?? '';
    $demo_link = $project['demo_link'] ?? '';
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

    <title>Edit Project</title>


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
           EDIT PROJECT PAGE
        ========================================= */

        .edit-project-page {

            width: 100%;

            max-width: 900px;

            margin: 0 auto;

            padding: 35px 30px;

            box-sizing: border-box;

        }


        /* =========================================
           HEADER
        ========================================= */

        .edit-project-header {

            margin-bottom: 30px;

        }


        .edit-project-header h2 {

            margin: 0 0 7px 0;

            font-size: 28px;

        }


        .edit-project-header p {

            margin: 0;

            opacity: 0.7;

        }


        /* =========================================
           FORM CARD
        ========================================= */

        .edit-project-card {

            background: #ffffff;

            border: 1px solid #e5e7eb;

            border-radius: 14px;

            padding: 30px;

            box-sizing: border-box;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

        }


        /* =========================================
           FORM GROUP
        ========================================= */

        .form-group {

            margin-bottom: 22px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-weight: 600;

            color: #1f2937;

        }


        .form-group label i {

            margin-right: 6px;

            color: #2196f3;

        }


        /* =========================================
           INPUT
        ========================================= */

        .form-group input,
        .form-group textarea {

            width: 100%;

            padding: 12px 14px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            font-size: 15px;

            font-family: inherit;

            box-sizing: border-box;

            outline: none;

            color: #1f2937;

            background: #ffffff;

            transition: 0.3s;

        }


        .form-group input:focus,
        .form-group textarea:focus {

            border-color: #2196f3;

            box-shadow:
                0 0 0 3px rgba(33, 150, 243, 0.12);

        }


        .form-group textarea {

            min-height: 150px;

            resize: vertical;

            line-height: 1.6;

        }


        /* =========================================
           ERROR MESSAGE
        ========================================= */

        .error-message {

            margin-bottom: 20px;

            padding: 12px 15px;

            border-radius: 8px;

            background: #fff1f2;

            border: 1px solid #fecdd3;

            color: #dc2626;

        }


        /* =========================================
           ACTION BUTTONS
        ========================================= */

        .form-actions {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 15px;

            border-top: 1px solid #e5e7eb;

            padding-top: 22px;

            margin-top: 10px;

        }


        .left-actions {

            display: flex;

            gap: 10px;

        }


        .right-actions {

            display: flex;

            gap: 10px;

        }


        .back-btn,
        .update-btn,
        .delete-btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            padding: 10px 17px;

            border-radius: 7px;

            text-decoration: none;

            font-size: 14px;

            font-weight: 600;

            cursor: pointer;

            transition: 0.3s;

            box-sizing: border-box;

        }


        /* =========================================
           BACK
        ========================================= */

        .back-btn {

            background: #f3f4f6;

            color: #374151;

            border: 1px solid #d1d5db;

        }


        .back-btn:hover {

            background: #e5e7eb;

        }


        /* =========================================
           UPDATE
        ========================================= */

        .update-btn {

            background: #2196f3;

            color: #ffffff;

            border: 1px solid #2196f3;

        }


        .update-btn:hover {

            background: #1976d2;

            border-color: #1976d2;

        }


        /* =========================================
           DELETE
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
           MOBILE
        ========================================= */

        @media (max-width: 600px) {

            .edit-project-page {

                padding: 25px 18px;

            }


            .edit-project-card {

                padding: 20px;

            }


            .form-actions {

                flex-direction: column;

                align-items: stretch;

            }


            .left-actions,
            .right-actions {

                width: 100%;

                flex-direction: column;

            }


            .back-btn,
            .update-btn,
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


    <div class="edit-project-page">


        <!-- =====================================
             HEADER
        ====================================== -->

        <div class="edit-project-header">

            <h2>
                <i class="fa-solid fa-pen"></i>
                Edit Project
            </h2>

            <p>
                Update your portfolio project details.
            </p>

        </div>


        <!-- =====================================
             FORM CARD
        ====================================== -->

        <div class="edit-project-card">


            <!-- ERROR -->

            <?php if (!empty($error)): ?>

                <div class="error-message">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    <?= htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <!-- =================================
                 UPDATE FORM
            ================================== -->

            <form
                action="edit_project.php?id=<?= (int)$project_id; ?>"
                method="POST"
            >


                <!-- PROJECT TITLE -->

                <div class="form-group">

                    <label for="title">

                        <i class="fa-solid fa-heading"></i>

                        Project Title

                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?= htmlspecialchars($title); ?>"
                        placeholder="Enter project title"
                        required
                    >

                </div>


                <!-- DESCRIPTION -->

                <div class="form-group">

                    <label for="description">

                        <i class="fa-solid fa-align-left"></i>

                        Description

                    </label>

                    <textarea
                        id="description"
                        name="description"
                        placeholder="Enter project description"
                    ><?= htmlspecialchars($description); ?></textarea>

                </div>


                <!-- TECHNOLOGIES -->

                <div class="form-group">

                    <label for="technologies">

                        <i class="fa-solid fa-code"></i>

                        Technologies

                    </label>

                    <input
                        type="text"
                        id="technologies"
                        name="technologies"
                        value="<?= htmlspecialchars($technologies); ?>"
                        placeholder="Example: PHP, MySQL, HTML, CSS, JavaScript"
                    >

                </div>


                <!-- DEMO LINK -->

                <div class="form-group">

                    <label for="demo_link">

                        <i class="fa-solid fa-link"></i>

                        Demo Link

                    </label>

                    <input
                        type="url"
                        id="demo_link"
                        name="demo_link"
                        value="<?= htmlspecialchars($demo_link); ?>"
                        placeholder="https://example.com"
                    >

                </div>


                <!-- =================================
                     ACTIONS
                ================================== -->

                <div class="form-actions">


                    <!-- BACK -->

                    <div class="left-actions">

                        <a
                            href="projects.php"
                            class="back-btn"
                        >

                            <i class="fa-solid fa-arrow-left"></i>

                            Back

                        </a>

                    </div>


                    <!-- UPDATE + DELETE -->

                    <div class="right-actions">


                        <button
                            type="submit"
                            name="update_project"
                            class="update-btn"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            Update Project

                        </button>


                    </div>

                </div>

            </form>


            <!-- =================================
                 DELETE FORM
                 SEPARATE FORM
            ================================== -->

            <form
                action="edit_project.php?id=<?= (int)$project_id; ?>"
                method="POST"
                onsubmit="return confirm('Are you sure you want to permanently delete this project?');"
                style="margin-top: 12px;"
            >

                <input
                    type="hidden"
                    name="project_id"
                    value="<?= (int)$project_id; ?>"
                >


                <button
                    type="submit"
                    name="delete_project"
                    class="delete-btn"
                >

                    <i class="fa-solid fa-trash"></i>

                    Delete Project

                </button>

            </form>


        </div>


    </div>


</div>


</body>

</html>