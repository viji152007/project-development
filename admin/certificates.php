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
        SELECT id, user_id, certificate_name, issuer, certificate_url, created_at
        FROM certificates
        WHERE certificate_name LIKE ?
           OR issuer LIKE ?
        ORDER BY id DESC
    ");

    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT id, user_id, certificate_name, issuer, certificate_url, created_at
        FROM certificates
        ORDER BY id DESC
    ");
}


/* ================= DELETE CERTIFICATE ================= */

if (isset($_GET['delete'])) {

    $certificate_id = (int)$_GET['delete'];

    $stmt = $conn->prepare(
        "DELETE FROM certificates WHERE id = ?"
    );

    $stmt->bind_param("i", $certificate_id);

    if ($stmt->execute()) {

        header("Location: certificates.php?deleted=1");
        exit();

    } else {

        $error = "Unable to delete certificate.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin - Certificates</title>

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

        .certificate-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .certificate-table th,
        .certificate-table td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .certificate-table th {
            background: #0d1b2a;
            color: white;
        }

        .view-btn {
            background: #0077ff;
            color: white;
            padding: 7px 11px;
            border-radius: 5px;
            text-decoration: none;
            margin-right: 5px;
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
                <i class="fa-solid fa-trophy"></i>
                Certificates
            </h1>

        </div>


        <!-- SUCCESS MESSAGE -->

        <?php if (isset($_GET['deleted'])): ?>

            <div class="success">
                Certificate deleted successfully.
            </div>

        <?php endif; ?>


        <!-- ================= SEARCH ================= -->

        <form method="GET" class="search-box">

            <input
                type="text"
                name="search"
                placeholder="Search certificate or issuer"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">

                <i class="fa-solid fa-search"></i>
                Search

            </button>

        </form>


        <!-- ================= CERTIFICATE TABLE ================= -->

        <table class="certificate-table">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>User ID</th>
                    <th>Certificate Name</th>
                    <th>Issuer</th>
                    <th>Certificate</th>
                    <th>Created</th>
                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($certificate = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo $certificate['id']; ?>
                        </td>


                        <td>
                            <?php echo $certificate['user_id']; ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $certificate['certificate_name']
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $certificate['issuer'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>

                            <?php if (!empty($certificate['certificate_url'])): ?>

                                <a
                                    href="<?php echo htmlspecialchars($certificate['certificate_url']); ?>"
                                    target="_blank"
                                    class="view-btn"
                                >
                                    <i class="fa-solid fa-link"></i>
                                    View
                                </a>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $certificate['created_at']
                            );
                            ?>
                        </td>


                        <td>

                            <a
                                href="certificates.php?delete=<?php echo $certificate['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this certificate?');"
                            >

                                <i class="fa-solid fa-trash"></i>

                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>


            <?php else: ?>

                <tr>

                    <td colspan="7" style="text-align:center;">

                        No certificates found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>


    </main>

</div>


</body>
</html>