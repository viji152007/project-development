<?php
session_start();

/* Admin Login Check */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once "../config/db.php";

/* Search */
$search = $_GET['search'] ?? '';

if ($search !== '') {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare("
        SELECT id, fullname, username, email, phone, is_verified, created_at
        FROM users
        WHERE fullname LIKE ?
           OR username LIKE ?
           OR email LIKE ?
        ORDER BY id DESC
    ");

    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query("
        SELECT id, fullname, username, email, phone, is_verified, created_at
        FROM users
        ORDER BY id DESC
    ");
}


/* Delete User */
if (isset($_GET['delete'])) {

    $user_id = (int)$_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: users.php?deleted=1");
        exit();
    } else {
        $error = "Unable to delete user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin - Users</title>

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
            width: 300px;
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

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .users-table th,
        .users-table td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        .users-table th {
            background: #0d1b2a;
            color: white;
        }

        .status-verified {
            color: green;
            font-weight: bold;
        }

        .status-not {
            color: red;
            font-weight: bold;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            padding: 7px 11px;
            border-radius: 5px;
            text-decoration: none;
        }

        .view-btn {
            background: #0077ff;
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

    <!-- SIDEBAR -->

    <?php include "sidebar.php"; ?>


    <!-- MAIN CONTENT -->

    <main class="main-content">

        <div class="page-header">

            <h1>
                <i class="fa-solid fa-users"></i>
                Users
            </h1>

        </div>


        <?php if (isset($_GET['deleted'])): ?>

            <div class="success">
                User deleted successfully.
            </div>

        <?php endif; ?>


        <!-- SEARCH -->

        <form method="GET" class="search-box">

            <input
                type="text"
                name="search"
                placeholder="Search by name, username or email"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">
                <i class="fa-solid fa-search"></i>
                Search
            </button>

        </form>


        <!-- USERS TABLE -->

        <table class="users-table">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($user = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo $user['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['fullname']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['phone'] ?? '-'); ?>
                        </td>

                        <td>

                            <?php if ($user['is_verified']): ?>

                                <span class="status-verified">
                                    Verified
                                </span>

                            <?php else: ?>

                                <span class="status-not">
                                    Not Verified
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?php echo htmlspecialchars($user['created_at']); ?>
                        </td>

                        <td>

                            <a
                                href="view_user.php?id=<?php echo $user['id']; ?>"
                                class="view-btn"
                            >
                                <i class="fa-solid fa-eye"></i>
                            </a>

                            <a
                                href="users.php?delete=<?php echo $user['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this user?');"
                            >
                                <i class="fa-solid fa-trash"></i>
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="8" style="text-align:center;">
                        No users found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </main>

</div>

</body>

</html>