<?php
session_start();

$allowed_emails = [
    "viji152007@gmail.com",
    "kannamadhu07@gmail.com",
    "lingamlingam9706@gmail.com"
];

$error = "";

if (isset($_POST['login'])) {

    $email = strtolower(trim($_POST['email']));

    if (in_array($email, $allowed_emails, true)) {

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;

        header("Location: dashboard.php");
        exit();

    } else {

        $error = "Unauthorized Email ID";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login</title>

    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<div class="container">

    <h1>Admin Login</h1>

    <p>Enter your registered email</p>

    <?php if ($error != ""): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Enter Email Address"
            required
        >

        <button type="submit" name="login" class="btn login">
            Login
        </button>

    </form>

</div>

</body>
</html>
```
