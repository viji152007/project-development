<?php
session_start();

require_once "../config/db.php";

$error = "";

if (isset($_POST['login'])) {

    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role, status
         FROM admins
         WHERE email = ?
         LIMIT 1"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        if (
            $admin['status'] === 'Active' &&
            password_verify($password, $admin['password'])
        ) {

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Invalid Email or Password";
        }

    } else {

        $error = "Invalid Email or Password";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        /* ================= ADMIN LOGIN ================= */

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;

            background: #ffffff;

            padding: 35px;

            border-radius: 15px;

            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.20);

            text-align: center;
        }

        .login-container h1 {
            color: #0b1d51;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .login-container p {
            color: #666;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .login-container input {
            width: 100%;

            padding: 13px 15px;

            margin-bottom: 15px;

            border: 1px solid #d5dbe3;

            border-radius: 8px;

            outline: none;

            font-size: 15px;

            background: #f8fafc;
        }

        .login-container input:focus {
            border-color: #2563eb;
        }

        .login-btn {
            width: 100%;

            padding: 13px;

            border: none;

            border-radius: 8px;

            background: #2563eb;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            transition: 0.3s;
        }

        .login-btn:hover {
            background: #0b1d51;
        }

        .error {
            background: #ffe5e5;

            color: #d00000;

            padding: 10px;

            border-radius: 7px;

            margin-bottom: 18px;

            font-size: 14px;
        }

        @media (max-width: 500px) {

            .login-container {
                padding: 25px 20px;
            }

            .login-container h1 {
                font-size: 24px;
            }

        }

    </style>

</head>

<body>

    <div class="login-container">

        <h1>Admin Login</h1>

        <p>Enter your registered email and password</p>

        <?php if ($error != ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <input
                type="email"
                name="email"
                placeholder="Enter Email Address"
                autocomplete="off"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Enter Password"
                autocomplete="new-password"
                required
            >

            <button
                type="submit"
                name="login"
                class="login-btn"
            >
                Login
            </button>

        </form>

    </div>

</body>

</html>