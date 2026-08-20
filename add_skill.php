<?php
session_start();

require_once "config/db.php";

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['username'])) {
    header("Location:login.php");
    exit();
}



/* =========================================
   CHECK DATABASE CONNECTION
========================================= */

if (!isset($conn) || !$conn) {
    die("Database connection failed. Please check config/db.php");
}


$username = $_SESSION['username'];


/* =========================================
   GET USER ID
========================================= */

$stmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE username = ?
    LIMIT 1
");

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {
    die("User not found");
}

$user_id = $user['id'];

$message = "";


/* =========================================
   ADD SKILL
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $skill_name = trim($_POST['skill_name'] ?? '');
    $level = $_POST['level'] ?? '';


    /* =====================================
       SKILL LEVEL PERCENTAGE
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

        $message = "Please select a skill level.";

    } else {

        $percentage = $level_percentage[$level];


        /* =================================
           INSERT SKILL
        ================================= */

        $stmt = $conn->prepare("
            INSERT INTO skills
            (user_id, skill_name, percentage)
            VALUES (?, ?, ?)
        ");

        if (!$stmt) {
            $message = "Database error: " . $conn->error;
        } else {

            $stmt->bind_param(
                "isi",
                $user_id,
                $skill_name,
                $percentage
            );


            if ($stmt->execute()) {

                $stmt->close();

                /* Go back to skills page */

                header("Location:skills.php");
                exit();

            } else {

                $message = "Unable to add skill: " . $stmt->error;

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

    <title>Add Professional Skill</title>


    <!-- =========================================
         EXISTING THEME CSS
    ========================================== -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- =========================================
         FONT AWESOME
    ========================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        /* =========================================
           ADD SKILL PAGE
        ========================================= */

        .add-skill-page {

            width: 100%;
            max-width: 650px;

            margin: 0 auto;

            padding: 35px 30px;

            box-sizing: border-box;

        }


        .add-skill-page h2 {

            margin: 0 0 8px 0;

            font-size: 28px;

        }


        .add-skill-page > p {

            margin: 0 0 25px 0;

            opacity: 0.7;

        }


        /* =========================================
           FORM CARD
        ========================================= */

        .skill-form {

            padding: 28px;

            border-radius: 14px;

            background: rgba(255,255,255,0.06);

            border: 1px solid rgba(255,255,255,0.08);

            box-sizing: border-box;

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

        }


        /* =========================================
           INPUT
        ========================================= */

        .form-group input,
        .form-group select {

            width: 100%;

            height: 48px;

            padding: 0 14px;

            box-sizing: border-box;

            border-radius: 8px;

            border: 1px solid rgba(255,255,255,0.15);

            background: rgba(255,255,255,0.05);

            color: inherit;

            outline: none;

            font-size: 15px;

        }


        .form-group input:focus,
        .form-group select:focus {

            border-color: #00d9ff;

        }


        /* =========================================
           DROPDOWN
        ========================================= */

        .form-group select option {

            background: #07111f;

            color: white;

        }


        /* =========================================
           BUTTONS
        ========================================= */

        .form-buttons {

            display: flex;

            gap: 12px;

            margin-top: 25px;

        }


        .save-btn,
        .back-btn {

            height: 44px;

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


        .save-btn {

            background: #2196f3;

            color: white;

        }


        .save-btn:hover {

            opacity: 0.9;

        }


        .back-btn {

            background: rgba(255,255,255,0.10);

            color: inherit;

        }


        .back-btn:hover {

            background: rgba(255,255,255,0.15);

        }


        /* =========================================
           ERROR MESSAGE
        ========================================= */

        .error-message {

            margin-bottom: 20px;

            padding: 11px 14px;

            border-radius: 8px;

            background: rgba(239,68,68,0.12);

            color: #ff6b6b;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 700px) {

            .add-skill-page {

                padding: 25px 18px;

            }


            .skill-form {

                padding: 22px;

            }


            .form-buttons {

                flex-direction: column;

            }


            .save-btn,
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


    <div class="add-skill-page">


        <h2>Add Professional Skill</h2>

        <p>
            Add your technical skills and professional expertise.
        </p>


        <div class="skill-form">


            <!-- ERROR MESSAGE -->

            <?php if ($message !== ""): ?>

                <div class="error-message">

                    <?= htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <!-- =================================
                 SKILL FORM
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
                        placeholder="Enter skill name"
                        autocomplete="off"
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
                            value=""
                            selected
                            disabled
                        >
                            Select skill level
                        </option>

                        <option value="Beginner">
                            Beginner
                        </option>

                        <option value="Intermediate">
                            Intermediate
                        </option>

                        <option value="Advanced">
                            Advanced
                        </option>

                        <option value="Expert">
                            Expert
                        </option>

                    </select>

                </div>


                <!-- BUTTONS -->

                <div class="form-buttons">


                    <button
                        type="submit"
                        class="save-btn"
                    >

                        <i class="fa-solid fa-plus"></i>

                        &nbsp; Add Skill

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