<?php
session_start();

/* Admin Login Check */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../config/db.php";


/* ================= SEARCH ================= */

$search = $_GET['search'] ?? '';

if ($search !== '') {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT id, user_id, education_type, institution_name, course,
               department, start_year, end_year, percentage, grade
        FROM education
        WHERE education_type LIKE ?
           OR institution_name LIKE ?
           OR course LIKE ?
           OR department LIKE ?
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
        SELECT id, user_id, education_type, institution_name, course,
               department, start_year, end_year, percentage, grade
        FROM education
        ORDER BY id DESC
    ");
}


/* ================= DELETE EDUCATION ================= */

if (isset($_GET['delete'])) {

    $education_id = (int)$_GET['delete'];

    $stmt = $conn->prepare(
        "DELETE FROM education WHERE id = ?"
    );

    $stmt->bind_param("i", $education_id);

    if ($stmt->execute()) {

        header("Location: education.php?deleted=1");
        exit();

    } else {

        $error = "Unable to delete education record.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin - Education</title>

    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0;
        }

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

        .education-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .education-table th,
        .education-table td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .education-table th {
            background: #0d1b2a;
            color: white;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            padding: 7px 11px;
            border-radius: 5px;
            text-decoration: none;
        }

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
                <i class="fa-solid fa-graduation-cap"></i>
                Education
            </h1>

        </div>


        <!-- SUCCESS MESSAGE -->

        <?php if (isset($_GET['deleted'])): ?>

            <div class="success">
                Education record deleted successfully.
            </div>

        <?php endif; ?>


        <!-- ================= SEARCH ================= -->

        <form method="GET" class="search-box">

            <input
                type="text"
                name="search"
                placeholder="Search institution, course or department"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">

                <i class="fa-solid fa-search"></i>
                Search

            </button>

        </form>


        <!-- ================= EDUCATION TABLE ================= -->

        <table class="education-table">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>User ID</th>
                    <th>Education</th>
                    <th>Institution</th>
                    <th>Course</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($education = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo $education['id']; ?>
                        </td>


                        <td>
                            <?php echo $education['user_id']; ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $education['education_type'] ?? '-'
                            );
                            ?>
                        </td>


                        <!-- FIXED: institution_name -->

                        <td>
                            <?php
                            echo htmlspecialchars(
                                $education['institution_name'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $education['course'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $education['department'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>

                            <?php
                            echo htmlspecialchars(
                                ($education['start_year'] ?? '-') .
                                " - " .
                                ($education['end_year'] ?? '-')
                            );
                            ?>

                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $education['percentage'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $education['grade'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>

                            <a
                                href="education.php?delete=<?php echo $education['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this education record?');"
                            >

                                <i class="fa-solid fa-trash"></i>

                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>


            <?php else: ?>

                <tr>

                    <td colspan="10" style="text-align:center;">

                        No education records found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>


    </main>

</div>

</body>

</html>