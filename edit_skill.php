<?php
session_start();

/* =========================================
   LOGIN CHECK
========================================= */

if (
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['user']) &&
    !isset($_SESSION['username'])
) {
    header("Location: login.php");
    exit();
}


/* =========================================
   DATABASE
========================================= */

require_once "config/db.php";


/* =========================================
   DATABASE CHECK
========================================= */

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}


/* =========================================
   GET USER ID
========================================= */

$user_id = 0;

if (
    isset($_SESSION['user_id']) &&
    is_numeric($_SESSION['user_id'])
) {

    $user_id = (int)$_SESSION['user_id'];

} elseif (
    isset($_SESSION['user']) &&
    is_numeric($_SESSION['user'])
) {

    $user_id = (int)$_SESSION['user'];

} elseif (isset($_SESSION['username'])) {

    $username = trim($_SESSION['username']);

    $stmt = $conn->prepare("
        SELECT id
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    if (!$stmt) {
        die("User query error: " . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt->close();

    if ($user) {
        $user_id = (int)$user['id'];
    }
}


/* =========================================
   USER CHECK
========================================= */

if ($user_id <= 0) {
    die("User not found.");
}


/* =========================================
   GET SKILL ID
========================================= */

$skill_id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;

if ($skill_id <= 0) {
    header("Location: skills.php");
    exit();
}


/* =========================================
   GET EXISTING SKILL
========================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        skill_name,
        percentage
    FROM skills
    WHERE id = ?
    AND user_id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Skill query error: " . $conn->error);
}

$stmt->bind_param(
    "ii",
    $skill_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();
$skill = $result->fetch_assoc();

$stmt->close();


/* =========================================
   SKILL NOT FOUND
========================================= */

if (!$skill) {
    header("Location: skills.php");
    exit();
}


/* =========================================
   CONVERT PERCENTAGE TO LEVEL
========================================= */

$current_level = "Beginner";

$percentage = (int)$skill['percentage'];

if ($percentage >= 90) {

    $current_level = "Expert";

} elseif ($percentage >= 75) {

    $current_level = "Advanced";

} elseif ($percentage >= 50) {

    $current_level = "Intermediate";

} else {

    $current_level = "Beginner";
}


/* =========================================
   UPDATE MESSAGE
========================================= */

$message = "";


/* =========================================
   UPDATE SKILL
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $skill_name = trim(
        $_POST['skill_name'] ?? ''
    );

    $level = $_POST['level'] ?? '';


    /* =====================================
       LEVEL + PERCENTAGE
    ===================================== */

    $level_percentage = [

        "Beginner"     => 30,

        "Intermediate" => 60,

        "Advanced"     => 80,

        "Expert"       => 95

    ];


    /* =====================================
       VALIDATION
    ===================================== */

    if ($skill_name === "") {

        $message = "Please enter skill name.";

    } elseif (!isset($level_percentage[$level])) {

        $message = "Please select skill level.";

    } else {

        $percentage = $level_percentage[$level];


        /* =================================
           UPDATE DATABASE
        ================================= */

        $stmt = $conn->prepare("
            UPDATE skills
            SET
                skill_name = ?,
                percentage = ?
            WHERE id = ?
            AND user_id = ?
        ");

        if (!$stmt) {

            $message = "Update query error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "siii",
                $skill_name,
                $percentage,
                $skill_id,
                $user_id
            );


            if ($stmt->execute()) {

                $stmt->close();

                header("Location: skills.php");
                exit();

            } else {

                $message = "Unable to update skill.";

                $stmt->close();
            }
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

    <title>Edit Skill</title>


    <!-- MAIN CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        /* =========================================
           EDIT SKILL PAGE
        ========================================= */

        .edit-skill-page {

            width: 100%;

            max-width: 650px;

            margin: 0 auto;

            padding: 35px 30px;

            box-sizing: border-box;

        }


        .edit-skill-page h2 {

            margin: 0 0 8px 0;

            font-size: 28px;

        }


        .edit-skill-page > p {

            margin: 0 0 25px 0;

            opacity: 0.7;

        }


        /* =========================================
           FORM CARD
        ========================================= */

        .skill-form {

            padding: 28px;

            border-radius: 14px;

            background: #ffffff;

            border: 1px solid #e5e7eb;

            box-shadow:
                0 4px 15px rgba(0, 0, 0, 0.08);

            box-sizing: border-box;

            color: #1f2937;

        }


        /* =========================================
           FORM GROUP
        ========================================= */

        .form-group {

            margin-bottom: 22px;

        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-weight: 600;

            color: #1f2937;

        }


        /* =========================================
           INPUT + SELECT
        ========================================= */

        .form-group input,
        .form-group select {

            width: 100%;

            height: 48px;

            padding: 0 14px;

            box-sizing: border-box;

            border-radius: 8px;

            border: 1px solid #d1d5db;

            background: #ffffff;

            color: #1f2937;

            outline: none;

            font-size: 15px;

        }


        .form-group input:focus,
        .form-group select:focus {

            border-color: #2196f3;

            box-shadow:
                0 0 0 3px rgba(33, 150, 243, 0.10);

        }


        .form-group select option {

            background: #ffffff;

            color: #1f2937;

        }


        /* =========================================
           BUTTONS
        ========================================= */

        .form-buttons {

            display: flex;

            gap: 12px;

            margin-top: 25px;

        }


        .update-btn,
        .back-btn {

            min-height: 44px;

            padding: 0 20px;

            border-radius: 8px;

            border: none;

            text-decoration: none;

            cursor: pointer;

            font-weight: 600;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            box-sizing: border-box;

        }


        /* UPDATE */

        .update-btn {

            background: #2196f3;

            color: #ffffff;

        }


        .update-btn:hover {

            background: #1976d2;

        }


        /* CANCEL */

        .back-btn {

            background: #f3f4f6;

            color: #374151;

            border: 1px solid #e5e7eb;

        }


        .back-btn:hover {

            background: #e5e7eb;

        }


        /* =========================================
           ERROR
        ========================================= */

        .error-message {

            margin-bottom: 20px;

            padding: 11px 14px;

            border-radius: 8px;

            background: #fff1f2;

            border: 1px solid #fecdd3;

            color: #dc2626;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 700px) {

            .edit-skill-page {

                padding: 25px 18px;

            }


            .skill-form {

                padding: 22px;

            }


            .form-buttons {

                flex-direction: column;

            }


            .update-btn,
            .back-btn {

                width: 100%;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     SIDEBAR
========================================= -->

<?php include("sidebar.php"); ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <div class="edit-skill-page">


        <h2>Edit Skill</h2>

        <p>
            Update your technical skill and skill level.
        </p>


        <div class="skill-form">


            <!-- ERROR MESSAGE -->

            <?php if ($message !== ""): ?>

                <div class="error-message">

                    <?= htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <!-- =================================
                 FORM
            ================================== -->

            <form
                method="POST"
                autocomplete="off"
            >


                <!-- SKILL NAME -->

                <div class="form-group">

                    <label for="skill_name">
                        Skill Name
                    </label>


                    <input
                        type="text"
                        id="skill_name"
                        name="skill_name"
                        value="<?= htmlspecialchars($skill['skill_name']); ?>"
                        placeholder="Enter skill name"
                        required
                    >

                </div>


                <!-- SKILL LEVEL -->

                <div class="form-group">

                    <label for="level">
                        Skill Level
                    </label>


                    <select
                        id="level"
                        name="level"
                        required
                    >

                        <option
                            value="Beginner"
                            <?= $current_level === "Beginner" ? "selected" : ""; ?>
                        >
                            Beginner
                        </option>


                        <option
                            value="Intermediate"
                            <?= $current_level === "Intermediate" ? "selected" : ""; ?>
                        >
                            Intermediate
                        </option>


                        <option
                            value="Advanced"
                            <?= $current_level === "Advanced" ? "selected" : ""; ?>
                        >
                            Advanced
                        </option>


                        <option
                            value="Expert"
                            <?= $current_level === "Expert" ? "selected" : ""; ?>
                        >
                            Expert
                        </option>

                    </select>

                </div>


                <!-- BUTTONS -->

                <div class="form-buttons">


                    <button
                        type="submit"
                        class="update-btn"
                    >

                        <i class="fa-solid fa-pen"></i>

                        &nbsp; Update Skill

                    </button>


                    <a
                        href="skills.php"
                        class="back-btn"
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