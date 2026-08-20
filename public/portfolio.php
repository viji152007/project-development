<?php
include("../config/db.php");

// Get username from URL
if (!isset($_GET['username']) || empty($_GET['username'])) {
    die("Portfolio not found.");
}

$username = trim($_GET['username']);

// Get user details
$stmt = $conn->prepare(
    "SELECT id, fullname, username, email, profile_photo
     FROM users
     WHERE username = ?"
);

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Portfolio not found.");
}

$user_id = $user['id'];

// Get skills of this user
$skill_stmt = $conn->prepare(
    "SELECT skill_name, skill_level
     FROM skills
     WHERE user_id = ?
     ORDER BY id DESC"
);

$skill_stmt->bind_param("i", $user_id);
$skill_stmt->execute();

$skills = $skill_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($user['fullname']); ?> - Portfolio
    </title>

</head>

<body>

    <h1>
        <?php echo htmlspecialchars($user['fullname']); ?>
    </h1>

    <p>
        @<?php echo htmlspecialchars($user['username']); ?>
    </p>

    <hr>

    <h2>Skills</h2>

    <?php if ($skills->num_rows > 0): ?>

        <?php while ($skill = $skills->fetch_assoc()): ?>

            <div>

                <h3>
                    <?php echo htmlspecialchars($skill['skill_name']); ?>
                </h3>

                <p>
                    Level:
                    <?php echo htmlspecialchars($skill['skill_level']); ?>
                </p>

            </div>

            <hr>

        <?php endwhile; ?>

    <?php else: ?>

        <p>No skills available.</p>

    <?php endif; ?>

</body>

</html>