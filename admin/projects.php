<?php
session_start();

/* =========================================
   ADMIN LOGIN CHECK
========================================= */

if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    header("Location: login.php");
    exit();
}


/* =========================================
   DATABASE
========================================= */

require_once "../config/db.php";


/* =========================================
   SEARCH
========================================= */

$search = $_GET['search'] ?? '';

if ($search !== '') {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT *
        FROM projects
        WHERE title LIKE ?
           OR description LIKE ?
        ORDER BY id DESC
    ");

    if (!$stmt) {
        die("Search query error: " . $conn->error);
    }

    $stmt->bind_param(
        "ss",
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT *
        FROM projects
        ORDER BY id DESC
    ");

    if (!$result) {
        die("Project query error: " . $conn->error);
    }
}


/* =========================================
   DELETE PROJECT
========================================= */

if (isset($_GET['delete'])) {

    $project_id = (int)$_GET['delete'];

    if ($project_id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM projects
            WHERE id = ?
        ");

        if (!$stmt) {
            die("Delete query error: " . $conn->error);
        }

        $stmt->bind_param(
            "i",
            $project_id
        );

        if ($stmt->execute()) {

            $stmt->close();

            header("Location: projects.php?deleted=1");
            exit();

        } else {

            $error = "Unable to delete project.";

            $stmt->close();
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

    <title>Admin - Projects</title>


    <!-- ADMIN CSS -->

    <link
        rel="stylesheet"
        href="style.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >


    <style>

        /* =========================================
           PAGE HEADER
        ========================================= */

        .page-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

        }


        .page-header h1 {

            margin: 0;

        }


        /* =========================================
           SEARCH
        ========================================= */

        .search-box {

            display: flex;

            gap: 10px;

            margin-bottom: 20px;

        }


        .search-box input {

            padding: 11px 15px;

            width: 320px;

            border: 1px solid #ddd;

            border-radius: 6px;

        }


        .search-box button {

            padding: 11px 18px;

            border: none;

            border-radius: 6px;

            background: #0077ff;

            color: white;

            cursor: pointer;

        }


        .search-box button:hover {

            background: #005fcc;

        }


        /* =========================================
           PROJECT TABLE
        ========================================= */

        .projects-table {

            width: 100%;

            border-collapse: collapse;

            background: white;

            border-radius: 10px;

            overflow: hidden;

        }


        .projects-table th,
        .projects-table td {

            padding: 14px;

            border-bottom: 1px solid #eee;

            text-align: left;

        }


        .projects-table th {

            background: #0d1b2a;

            color: white;

        }


        /* =========================================
           PROJECT NAME
        ========================================= */

        .project-name {

            font-weight: 600;

            color: #111827;

        }


        /* =========================================
           VIEW BUTTON
        ========================================= */

        .view-btn {

            background: #0077ff;

            color: white;

            padding: 7px 11px;

            border-radius: 5px;

            text-decoration: none;

            margin-right: 5px;

        }


        .view-btn:hover {

            background: #005fcc;

        }


        /* =========================================
           DELETE BUTTON
        ========================================= */

        .delete-btn {

            background: #dc3545;

            color: white;

            padding: 7px 11px;

            border-radius: 5px;

            text-decoration: none;

        }


        .delete-btn:hover {

            background: #bb2d3b;

        }


        /* =========================================
           SUCCESS MESSAGE
        ========================================= */

        .success {

            background: #d4edda;

            color: #155724;

            padding: 12px;

            margin-bottom: 15px;

            border-radius: 6px;

        }


        /* =========================================
           ERROR MESSAGE
        ========================================= */

        .error {

            background: #f8d7da;

            color: #842029;

            padding: 12px;

            margin-bottom: 15px;

            border-radius: 6px;

        }

    </style>

</head>


<body>


<div class="dashboard-container">


    <!-- =========================================
         SIDEBAR
    ========================================= -->

    <?php include "sidebar.php"; ?>


    <!-- =========================================
         MAIN CONTENT
    ========================================= -->

    <main class="main-content">


        <!-- =====================================
             HEADER
        ====================================== -->

        <div class="page-header">

            <h1>

                <i class="fa-solid fa-folder"></i>

                Projects

            </h1>

        </div>


        <!-- =====================================
             SUCCESS MESSAGE
        ====================================== -->

        <?php if (isset($_GET['deleted'])): ?>

            <div class="success">

                <i class="fa-solid fa-circle-check"></i>

                Project deleted successfully.

            </div>

        <?php endif; ?>


        <!-- =====================================
             ERROR MESSAGE
        ====================================== -->

        <?php if (isset($error)): ?>

            <div class="error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- =====================================
             SEARCH
        ====================================== -->

        <form
            method="GET"
            class="search-box"
        >

            <input
                type="text"
                name="search"
                placeholder="Search project name or description"
                value="<?= htmlspecialchars($search); ?>"
            >


            <button type="submit">

                <i class="fa-solid fa-search"></i>

                Search

            </button>

        </form>


        <!-- =====================================
             PROJECT TABLE
        ====================================== -->

        <table class="projects-table">


            <thead>

                <tr>

                    <th>ID</th>

                    <th>Project Name</th>

                    <th>User ID</th>

                    <th>Description</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>


            <?php if ($result && $result->num_rows > 0): ?>


                <?php while ($project = $result->fetch_assoc()): ?>


                    <tr>


                        <!-- ID -->

                        <td>

                            <?= (int)$project['id']; ?>

                        </td>


                        <!-- PROJECT NAME -->

                        <td class="project-name">

                            <?php

                            echo htmlspecialchars(
                                $project['title'] ?? '-'
                            );

                            ?>

                        </td>


                        <!-- USER ID -->

                        <td>

                            <?= htmlspecialchars(
                                $project['user_id'] ?? '-'
                            ); ?>

                        </td>


                        <!-- DESCRIPTION -->

                        <td>

                            <?php

                            $description =
                                $project['description'] ?? '-';

                            echo htmlspecialchars(
                                mb_strimwidth(
                                    $description,
                                    0,
                                    80,
                                    "..."
                                )
                            );

                            ?>

                        </td>


                        <!-- ACTION -->

                        <td>


                            <!-- VIEW -->

                            <a
                                href="view_project.php?id=<?= (int)$project['id']; ?>"
                                class="view-btn"
                                title="View Project"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </a>


                            <!-- DELETE -->

                            <a
                                href="projects.php?delete=<?= (int)$project['id']; ?>"
                                class="delete-btn"
                                title="Delete Project"
                                onclick="return confirm('Are you sure you want to delete this project?');"
                            >

                                <i class="fa-solid fa-trash"></i>

                            </a>


                        </td>


                    </tr>


                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="5"
                        style="text-align:center;"
                    >

                        No projects found.

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>

        </table>


    </main>

</div>


</body>

</html>