<?php

session_start();

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


/* =========================================
   DATABASE
========================================= */

require_once "config/db.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}


/* =========================================
   GET LOGGED-IN USER
========================================= */

$username = $_SESSION['username'];

$stmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE username = ?
    LIMIT 1
");

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$user['id'];


/* =========================================
   GET SKILL ID
========================================= */

$skill_id = isset($_POST['skill_id'])
    ? (int)$_POST['skill_id']
    : 0;

if ($skill_id <= 0) {
    die("Invalid skill ID.");
}


/* =========================================
   CHECK SKILL EXISTS
========================================= */

$check = $conn->prepare("
    SELECT id
    FROM skills
    WHERE id = ?
    AND user_id = ?
    LIMIT 1
");

$check->bind_param(
    "ii",
    $skill_id,
    $user_id
);

$check->execute();

$check_result = $check->get_result();

if ($check_result->num_rows === 0) {
    $check->close();
    die("Skill not found or you do not have permission to delete it.");
}

$check->close();


/* =========================================
   DELETE SKILL
========================================= */

$delete = $conn->prepare("
    DELETE FROM skills
    WHERE id = ?
    AND user_id = ?
");

$delete->bind_param(
    "ii",
    $skill_id,
    $user_id
);

if (!$delete->execute()) {

    die(
        "Delete failed: " .
        htmlspecialchars($delete->error)
    );

}


/* =========================================
   CHECK WHETHER ACTUALLY DELETED
========================================= */

if ($delete->affected_rows > 0) {

    $delete->close();

    header("Location: skills.php");
    exit();

}


/* =========================================
   NOTHING DELETED
========================================= */

$delete->close();

die("Skill was not deleted. Skill ID or User ID did not match.");

?>