<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once "config/db.php";

$username = $_SESSION['username'];
$message = "";
$message_type = "";


/* Get logged-in user's ID */
$user_stmt = $conn->prepare(
    "SELECT id FROM users WHERE username = ? LIMIT 1"
);

$user_stmt->bind_param("s", $username);
$user_stmt->execute();

$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    header("Location: logout.php");
    exit();
}

$user = $user_result->fetch_assoc();
$user_id = $user['id'];

$user_stmt->close();


/* Add Project */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title'] ?? "");
    $description = trim($_POST['description'] ?? "");
    $technologies = trim($_POST['technologies'] ?? "");
    $demo_link = trim($_POST['demo_link'] ?? "");

    $image_name = "";


    /* Validation */

    if ($title === "" || $description === "") {

        $message = "Please enter project title and description.";
        $message_type = "error";

    } else {

        /* Image Upload */

        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {

                $upload_dir = "../../uploads/";

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $original_name = $_FILES['image']['name'];
                $tmp_name = $_FILES['image']['tmp_name'];

                $extension = strtolower(
                    pathinfo($original_name, PATHINFO_EXTENSION)
                );

                $allowed_extensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "gif",
                    "webp"
                ];

                if (!in_array($extension, $allowed_extensions)) {

                    $message = "Only JPG, JPEG, PNG, GIF and WEBP images are allowed.";
                    $message_type = "error";

                } else {

                    $image_name =
                        time() . "_" .
                        preg_replace(
                            "/[^a-zA-Z0-9._-]/",
                            "_",
                            $original_name
                        );

                    $image_path = $upload_dir . $image_name;

                    if (!move_uploaded_file($tmp_name, $image_path)) {

                        $message = "Failed to upload image.";
                        $message_type = "error";
                        $image_name = "";

                    }
                }

            } else {

                $message = "Image upload failed.";
                $message_type = "error";

            }
        }


        /* Insert Project */

        if ($message === "") {

            $stmt = $conn->prepare(
                "INSERT INTO projects
                (user_id, title, description, technologies, image, demo_link)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "isssss",
                $user_id,
                $title,
                $description,
                $technologies,
                $image_name,
                $demo_link
            );

            if ($stmt->execute()) {

                header("Location: projects.php");
                exit();

            } else {

                $message = "Failed to add project.";
                $message_type = "error";

            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Add Project</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<?php include "sidebar.php"; ?>


<div class="main-content">

    <div class="container">

        <div class="page-header">

            <div>
                <h2>Add Project</h2>

                <p>
                    Add a new project to your portfolio
                </p>
            </div>

            <div>

                <a
                    href="projects.php"
                    class="btn-secondary"
                >
                    ← Back to Projects
                </a>

            </div>

        </div>


        <?php if ($message !== ""): ?>

            <div class="card">

                <p>
                    <?php echo htmlspecialchars($message); ?>
                </p>

            </div>

        <?php endif; ?>


        <!-- COMPACT ADD PROJECT BOX -->

        <div class="card add-project-card">

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <div class="form-group">

                    <label for="title">
                        Project Title
                    </label>

                    <input
                        type="text"
                        id="title"
                        name="title"
                        placeholder="Enter project title"
                        value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="description">
                        Project Description
                    </label>

                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        placeholder="Describe your project"
                        required
                    ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>

                </div>


                <div class="form-group">

                    <label for="technologies">
                        Technologies
                    </label>

                    <input
                        type="text"
                        id="technologies"
                        name="technologies"
                        placeholder="Example: HTML, CSS, JavaScript, PHP, MySQL"
                        value="<?php echo htmlspecialchars($_POST['technologies'] ?? ''); ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="demo_link">
                        Demo Link
                    </label>

                    <input
                        type="url"
                        id="demo_link"
                        name="demo_link"
                        placeholder="https://example.com"
                        value="<?php echo htmlspecialchars($_POST['demo_link'] ?? ''); ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="image">
                        Project Image
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.gif,.webp"
                    >

                    <small>
                        Allowed: JPG, JPEG, PNG, GIF, WEBP
                    </small>

                </div>


                <div>

                    <button
                        type="submit"
                        class="btn-primary"
                    >
                        Add Project
                    </button>

                    <a
                        href="projects.php"
                        class="btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>

</html>

<?php
$conn->close();
?>