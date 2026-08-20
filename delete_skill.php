<?php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once "config/db.php";

$username = $_SESSION['username'];


/* Get logged-in user's ID */

$user_stmt = $conn->prepare(
    "SELECT id FROM users WHERE username = ? LIMIT 1"
);

$user_stmt->bind_param("s", $username);
$user_stmt->execute();

$user_result = $user_stmt->get_result();

$user = $user_result->fetch_assoc();

$user_stmt->close();


if (!$user) {
    die("Logged-in user not found.");
}

$user_id = (int)$user['id'];


/* Get project ID */

if (!isset($_GET['id'])) {
    die("Project ID is missing.");
}

$project_id = (int)$_GET['id'];

if ($project_id <= 0) {
    die("Invalid project ID: " . htmlspecialchars($_GET['id']));
}


/* Delete only logged-in user's project */

$delete_stmt = $conn->prepare(
    "DELETE FROM projects
     WHERE id = ?
     AND user_id = ?"
);

$delete_stmt->bind_param(
    "ii",
    $project_id,
    $user_id
);

$delete_stmt->execute();


/* Check delete */

if ($delete_stmt->affected_rows > 0) {

    $delete_stmt->close();

    header("Location: projects.php");
    exit();

} else {

    $delete_stmt->close();

    die(
        "Delete failed. Project ID = " .
        $project_id .
        " | Logged-in User ID = " .
        $user_id
    );
}

?>