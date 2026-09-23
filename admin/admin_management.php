<?php
session_start();

/* ================= ADMIN LOGIN CHECK ================= */

if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    header("Location: login.php");
    exit();
}

require_once "../config/db.php";

$message = "";
$error = "";


/* ================= ADD NEW ADMIN ================= */

if (isset($_POST['add_admin'])) {

    $name = trim($_POST['name']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($name === "" || $email === "" || $password === "") {

        $error = "Please fill all required fields.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } elseif (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    } else {

        /* Check email already exists */

        $check = $conn->prepare(
            "SELECT id FROM admins WHERE email = ? LIMIT 1"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $error = "This admin email already exists.";

        } else {

            /* Secure password */

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO admins
                (name, email, password, role, status)
                VALUES (?, ?, ?, 'Administrator', 'Active')"
            );

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "New admin added successfully.";

            } else {

                $error = "Unable to add admin.";
            }

            $stmt->close();
        }

        $check->close();
    }
}


/* ================= DELETE ADMIN ================= */

if (isset($_GET['delete'])) {

    $delete_id = (int) $_GET['delete'];

    $current_admin_id = (int) (
        $_SESSION['admin_id'] ?? 0
    );

    /* Do not allow current admin to delete themselves */

    if ($delete_id === $current_admin_id) {

        $error = "You cannot delete your own admin account.";

    } else {

        $stmt = $conn->prepare(
            "DELETE FROM admins WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $delete_id
        );

        if ($stmt->execute()) {

            $message = "Admin deleted successfully.";

        } else {

            $error = "Unable to delete admin.";
        }

        $stmt->close();
    }
}


/* ================= FETCH ALL ADMINS ================= */

$admins = $conn->query(
    "SELECT id, name, email, role, status, created_at
     FROM admins
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Management</title>

    <!-- Existing Admin CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
    >


    <!-- ================= EXTRA CSS ONLY ================= -->

    <style>

        .page-title {
            background: #ffffff;
            padding: 25px 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            margin-bottom: 25px;
        }

        .page-title h1 {
            color: #0b1d51;
            margin: 0;
        }


        /* ================= ADD ADMIN ================= */

        .admin-form {
            background: #ffffff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
            margin-bottom: 25px;
        }

        .admin-form h2 {
            color: #0b1d51;
            margin-bottom: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            color: #0b1d51;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .form-group input {
            padding: 12px;
            border: 1px solid #d5dbe3;
            border-radius: 8px;
            outline: none;
            font-size: 14px;
            background: #ffffff;
        }

        .form-group input:focus {
            border-color: #2563eb;
        }

        .add-btn {
            margin-top: 20px;
            padding: 12px 22px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #ffffff;
            font-weight: bold;
            cursor: pointer;
        }

        .add-btn:hover {
            background: #0b1d51;
        }


        /* ================= MESSAGES ================= */

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }


        /* ================= ADMIN TABLE ================= */

        .admin-table-box {
            background: #ffffff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
            overflow-x: auto;
        }

        .admin-table-box h2 {
            color: #0b1d51;
            margin-bottom: 20px;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        .admin-table th {
            background: #0b1d51;
            color: #ffffff;
            padding: 13px;
            text-align: left;
        }

        .admin-table td {
            padding: 13px;
            border-bottom: 1px solid #e5e7eb;
            color: #333333;
        }

        .admin-table tr:hover {
            background: #f8fafc;
        }


        /* ================= STATUS ================= */

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status.active {
            background: #dcfce7;
            color: #166534;
        }

        .status.inactive {
            background: #fee2e2;
            color: #b91c1c;
        }


        /* ================= DELETE ================= */

        .delete-btn {
            display: inline-block;
            padding: 7px 12px;
            background: #dc2626;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
        }

        .delete-btn:hover {
            background: #991b1b;
        }


        /* ================= CURRENT ADMIN ================= */

        .current-admin {
            color: #2563eb;
            font-weight: bold;
        }


        /* ================= MOBILE ================= */

        @media (max-width: 700px) {

            .form-grid {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>


<body>


<!-- ================= EXISTING SIDEBAR ================= -->

<?php include "sidebar.php"; ?>


<!-- ================= MAIN CONTENT ================= -->

<div class="main-content">


    <!-- PAGE TITLE -->

    <div class="page-title">

        <h1>
            <i class="fa-solid fa-user-shield"></i>
            Admin Management
        </h1>

    </div>


    <!-- SUCCESS MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="success-message">

            <i class="fa-solid fa-circle-check"></i>

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- ERROR MESSAGE -->

    <?php if ($error !== ""): ?>

        <div class="error-message">

            <i class="fa-solid fa-circle-exclamation"></i>

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <!-- ================= ADD NEW ADMIN ================= -->

    <div class="admin-form">

        <h2>

            <i class="fa-solid fa-user-plus"></i>

            Add New Admin

        </h2>


        <form method="POST">


            <div class="form-grid">


                <!-- NAME -->

                <div class="form-group">

                    <label>
                        Admin Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Enter admin name"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter admin email"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label>
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Enter password"
                        minlength="6"
                        required
                    >

                </div>


                <!-- CONFIRM PASSWORD -->

                <div class="form-group">

                    <label>
                        Confirm Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        minlength="6"
                        required
                    >

                </div>


            </div>


            <button
                type="submit"
                name="add_admin"
                class="add-btn"
            >

                <i class="fa-solid fa-plus"></i>

                Add Admin

            </button>


        </form>

    </div>


    <!-- ================= EXISTING ADMINS ================= -->

    <div class="admin-table-box">


        <h2>

            <i class="fa-solid fa-users-gear"></i>

            Existing Admins

        </h2>


        <table class="admin-table">


            <thead>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Email</th>

                    <th>Role</th>

                    <th>Status</th>

                    <th>Created At</th>

                    <th>Action</th>

                </tr>

            </thead>


            <tbody>


            <?php if ($admins && $admins->num_rows > 0): ?>


                <?php while ($admin = $admins->fetch_assoc()): ?>


                    <tr>


                        <!-- ID -->

                        <td>

                            <?php
                            echo (int) $admin['id'];
                            ?>

                        </td>


                        <!-- NAME -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $admin['name']
                            );
                            ?>


                            <?php
                            if (
                                isset($_SESSION['admin_id']) &&
                                (int)$_SESSION['admin_id']
                                === (int)$admin['id']
                            ):
                            ?>

                                <span class="current-admin">
                                    (You)
                                </span>

                            <?php endif; ?>


                        </td>


                        <!-- EMAIL -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $admin['email']
                            );
                            ?>

                        </td>


                        <!-- ROLE -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $admin['role']
                            );
                            ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <span class="status <?php
                                echo strtolower(
                                    $admin['status']
                                );
                            ?>">

                                <?php
                                echo htmlspecialchars(
                                    $admin['status']
                                );
                                ?>

                            </span>

                        </td>


                        <!-- CREATED -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $admin['created_at']
                            );
                            ?>

                        </td>


                        <!-- ACTION -->

                        <td>


                            <?php
                            if (
                                isset($_SESSION['admin_id']) &&
                                (int)$_SESSION['admin_id']
                                === (int)$admin['id']
                            ):
                            ?>

                                <span class="current-admin">

                                    Current Admin

                                </span>


                            <?php else: ?>


                                <a
                                    href="admin_management.php?delete=<?php echo (int)$admin['id']; ?>"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete this admin?');"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                    Delete

                                </a>


                            <?php endif; ?>


                        </td>


                    </tr>


                <?php endwhile; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="7"
                        style="text-align:center;"
                    >

                        No admins found.

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>


        </table>


    </div>


</div>


</body>

</html>