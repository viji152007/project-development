<?php
session_start();

/* Admin Login Check */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../config/db.php";


/* ================= DELETE SKILL ================= */

if (isset($_GET['delete'])) {

    $skill_id = (int)$_GET['delete'];

    $stmt = $conn->prepare(
        "DELETE FROM skills WHERE id = ?"
    );

    $stmt->bind_param("i", $skill_id);

    if ($stmt->execute()) {

        header("Location: skills.php?deleted=1");
        exit();

    } else {

        $error = "Unable to delete skill record.";
    }

    $stmt->close();
}


/* ================= SEARCH ================= */

$search = $_GET['search'] ?? '';

if ($search !== '') {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT id, user_id, skill_name, category,
               skill_level, created_at, level, percentage
        FROM skills
        WHERE skill_name LIKE ?
           OR category LIKE ?
           OR skill_level LIKE ?
           OR level LIKE ?
        ORDER BY id DESC
    ");

    $stmt->bind_param(
        "ssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT id, user_id, skill_name, category,
               skill_level, created_at, level, percentage
        FROM skills
        ORDER BY id DESC
    ");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin - Skills</title>

    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


    <style>

        /* ================= PAGE HEADER ================= */

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0;
        }


        /* ================= SEARCH ================= */

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-box input {
            padding: 11px 15px;
            width: 350px;
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


        /* ================= WHITE TABLE BOX ================= */

        .skills-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .skills-table th,
        .skills-table td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .skills-table th {
            background: #0d1b2a;
            color: white;
        }

        .skills-table tr:last-child td {
            border-bottom: none;
        }


        /* ================= DELETE ================= */

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


        /* ================= SUCCESS ================= */

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }

    </style>

</head>


<body>

<div class="dashboard-container">


    <!-- ================= SIDEBAR ================= -->

    <?php include "sidebar.php"; ?>


    <!-- ================= MAIN CONTENT ================= -->

    <main class="main-content">


        <div class="page-header">

            <h1>
                <i class="fa-solid fa-code"></i>
                Skills
            </h1>

        </div>


        <!-- ================= SUCCESS MESSAGE ================= -->

        <?php if (isset($_GET['deleted'])): ?>

            <div class="success">
                Skill record deleted successfully.
            </div>

        <?php endif; ?>


        <!-- ================= SEARCH ================= -->

        <form method="GET" class="search-box">

            <input
                type="text"
                name="search"
                placeholder="Search skill, category or level"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">

                <i class="fa-solid fa-search"></i>
                Search

            </button>

        </form>


        <!-- ================= SKILLS TABLE ================= -->

        <table class="skills-table">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>User ID</th>
                    <th>Skill Name</th>
                    <th>Category</th>
                    <th>Skill Level</th>
                    <th>Level</th>
                    <th>Percentage</th>
                    <th>Created At</th>
                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($skill = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo $skill['id']; ?>
                        </td>


                        <td>
                            <?php echo $skill['user_id']; ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $skill['skill_name'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $skill['category'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $skill['skill_level'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $skill['level'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $skill['percentage'] ?? '0'
                            );
                            ?>%
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $skill['created_at'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>

                            <a
                                href="skills.php?delete=<?php echo $skill['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this skill record?');"
                            >

                                <i class="fa-solid fa-trash"></i>

                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>


            <?php else: ?>

                <tr>

                    <td colspan="9" style="text-align:center;">

                        No skill records found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>


    </main>

</div>

</body>

</html>