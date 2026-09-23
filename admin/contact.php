<?php
session_start();

/* Admin Login Check */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../config/db.php";


/* ================= DELETE CONTACT ================= */

if (isset($_GET['delete'])) {

    $contact_id = (int)$_GET['delete'];

    $stmt = $conn->prepare(
        "DELETE FROM contact WHERE id = ?"
    );

    $stmt->bind_param("i", $contact_id);

    if ($stmt->execute()) {

        header("Location: contact.php?deleted=1");
        exit();

    } else {

        $error = "Unable to delete contact record.";
    }

    $stmt->close();
}


/* ================= SEARCH ================= */

$search = $_GET['search'] ?? '';

if ($search !== '') {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT id, user_id, address, city, state, pincode,
               alternate_email, linkedin, github, website, created_at
        FROM contact
        WHERE address LIKE ?
           OR city LIKE ?
           OR state LIKE ?
           OR pincode LIKE ?
           OR alternate_email LIKE ?
           OR linkedin LIKE ?
           OR github LIKE ?
           OR website LIKE ?
        ORDER BY id DESC
    ");

    $stmt->bind_param(
        "ssssssss",
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT id, user_id, address, city, state, pincode,
               alternate_email, linkedin, github, website, created_at
        FROM contact
        ORDER BY id DESC
    ");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin - Contact</title>

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

        .contact-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .contact-table th,
        .contact-table td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .contact-table th {
            background: #0d1b2a;
            color: white;
        }

        .contact-table tr:last-child td {
            border-bottom: none;
        }


        /* ================= LINKS ================= */

        .contact-table a {
            color: #0077ff;
            text-decoration: none;
        }

        .contact-table a:hover {
            text-decoration: underline;
        }


        /* ================= DELETE ================= */

        .delete-btn {
            background: #dc3545;
            color: white !important;
            padding: 7px 11px;
            border-radius: 5px;
            text-decoration: none !important;
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
                <i class="fa-solid fa-envelope"></i>
                Contact
            </h1>

        </div>


        <!-- ================= SUCCESS MESSAGE ================= -->

        <?php if (isset($_GET['deleted'])): ?>

            <div class="success">
                Contact record deleted successfully.
            </div>

        <?php endif; ?>


        <!-- ================= SEARCH ================= -->

        <form method="GET" class="search-box">

            <input
                type="text"
                name="search"
                placeholder="Search city, state, email or social link"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">

                <i class="fa-solid fa-search"></i>
                Search

            </button>

        </form>


        <!-- ================= CONTACT TABLE ================= -->

        <table class="contact-table">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>User ID</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Pincode</th>
                    <th>Alternate Email</th>
                    <th>LinkedIn</th>
                    <th>GitHub</th>
                    <th>Website</th>
                    <th>Created At</th>
                    <th>Action</th>

                </tr>

            </thead>


            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($contact = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo $contact['id']; ?>
                        </td>


                        <td>
                            <?php echo $contact['user_id']; ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $contact['address'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $contact['city'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $contact['state'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $contact['pincode'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $contact['alternate_email'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>

                            <?php if (!empty($contact['linkedin'])): ?>

                                <a
                                    href="<?php echo htmlspecialchars($contact['linkedin']); ?>"
                                    target="_blank"
                                >
                                    <i class="fa-brands fa-linkedin"></i>
                                    LinkedIn
                                </a>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php if (!empty($contact['github'])): ?>

                                <a
                                    href="<?php echo htmlspecialchars($contact['github']); ?>"
                                    target="_blank"
                                >
                                    <i class="fa-brands fa-github"></i>
                                    GitHub
                                </a>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php if (!empty($contact['website'])): ?>

                                <a
                                    href="<?php echo htmlspecialchars($contact['website']); ?>"
                                    target="_blank"
                                >
                                    <i class="fa-solid fa-globe"></i>
                                    Website
                                </a>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>


                        <td>
                            <?php
                            echo htmlspecialchars(
                                $contact['created_at'] ?? '-'
                            );
                            ?>
                        </td>


                        <td>

                            <a
                                href="contact.php?delete=<?php echo $contact['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this contact record?');"
                            >

                                <i class="fa-solid fa-trash"></i>

                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>


            <?php else: ?>

                <tr>

                    <td colspan="12" style="text-align:center;">

                        No contact records found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>


    </main>

</div>

</body>

</html>