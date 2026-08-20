<?php
session_start();

/* =========================================
   LOGIN CHECK
========================================= */

if (!isset($_SESSION['username'])) {
    header("Location:login.php");
    exit();
}


/* =========================================
   DATABASE
========================================= */

include("config/db.php");

$username = $_SESSION['username'];


/* =========================================
   GET LOGGED-IN USER ID
========================================= */

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
    die("User not found");
}

$user_id = $user['id'];


/* =========================================
   GET USER SKILLS
========================================= */

$stmt = $conn->prepare("
    SELECT id, skill_name, percentage
    FROM skills
    WHERE user_id = ?
    ORDER BY id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$skills = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Skills</title>


    <!-- EXISTING WEBSITE THEME -->

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
           SKILLS PAGE
        ========================================= */

        .skills-page {

            width: 100%;

            padding: 25px 30px;

            box-sizing: border-box;

        }


        /* =========================================
           HEADER
        ========================================= */

        .skills-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 25px;

        }


        .skills-header h2 {

            margin: 0;

            font-size: 28px;

        }


        .skills-header p {

            margin: 6px 0 0;

            opacity: 0.7;

        }


        /* =========================================
           ADD SKILL
        ========================================= */

        .add-skill-btn {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 10px 17px;

            border-radius: 8px;

            text-decoration: none;

            font-weight: 600;

        }


        /* =========================================
           SKILLS GRID
        ========================================= */

        .skills-container {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 20px;

        }


        /* =========================================
           SKILL CARD
        ========================================= */

        .skill-card {

            padding: 20px;

            border-radius: 14px;

            background: rgba(255,255,255,0.06);

            border: 1px solid rgba(255,255,255,0.08);

            box-sizing: border-box;

            transition: 0.2s ease;

        }


        .skill-card:hover {

            transform: translateY(-2px);

        }


        /* =========================================
           SKILL TOP
        ========================================= */

        .skill-top {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 12px;

        }


        .skill-name {

            font-size: 17px;

            font-weight: 600;

        }


        .skill-percent {

            font-size: 14px;

            font-weight: 600;

            opacity: 0.85;

        }


        /* =========================================
           SKILL BAR
        ========================================= */

        .skill-bar {

            width: 100%;

            height: 9px;

            border-radius: 20px;

            background: rgba(255,255,255,0.12);

            overflow: hidden;

        }


        .skill-progress {

            height: 100%;

            border-radius: 20px;

            background:
                linear-gradient(
                    90deg,
                    #4facfe,
                    #00f2fe
                );

        }


        /* =========================================
           EDIT + DELETE
        ========================================= */

        .skill-actions {

            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 13px;

            margin-top: 13px;

        }


        /* EDIT PEN */

        .skill-edit-icon {

            color: #60a5fa;

            font-size: 14px;

            text-decoration: none;

            cursor: pointer;

            transition: 0.2s;

        }


        .skill-edit-icon:hover {

            color: #93c5fd;

            transform: scale(1.1);

        }


        /* DELETE */

        .skill-delete-form {

            margin: 0;

            padding: 0;

        }


        .skill-delete-icon {

            border: none;

            background: transparent;

            padding: 0;

            margin: 0;

            color: #ef4444;

            font-size: 14px;

            cursor: pointer;

            transition: 0.2s;

        }


        .skill-delete-icon:hover {

            color: #f87171;

            transform: scale(1.1);

        }


        /* =========================================
           NO SKILLS
        ========================================= */

        .no-skills {

            grid-column: 1 / -1;

            text-align: center;

            padding: 50px 20px;

            opacity: 0.7;

        }


        .no-skills h3 {

            margin-bottom: 8px;

        }


        .no-skills p {

            margin: 0;

        }


        /* =========================================
           MOBILE
        ========================================= */

        @media (max-width: 800px) {

            .skills-container {

                grid-template-columns: 1fr;

            }


            .skills-header {

                flex-direction: column;

                align-items: flex-start;

                gap: 15px;

            }

        }

    </style>

</head>


<body>


<!-- =========================================
     EXISTING SIDEBAR
========================================= -->

<?php include("sidebar.php"); ?>


<!-- =========================================
     MAIN CONTENT
========================================= -->

<div class="main-content">


    <div class="skills-page">


        <!-- =====================================
             HEADER
        ====================================== -->

        <div class="skills-header">


            <div>

                <h2>My Skills</h2>

                <p>
                    Showcase your technical expertise
                    and professional abilities.
                </p>

            </div>


            <!-- ADD SKILL -->

            <a
                href="add_skill.php"
                class="add-skill-btn"
            >

                <i class="fa-solid fa-plus"></i>

                Add Skill

            </a>


        </div>



        <!-- =====================================
             SKILLS
        ====================================== -->

        <div class="skills-container">


            <?php if ($skills->num_rows > 0): ?>


                <?php while ($skill = $skills->fetch_assoc()): ?>


                    <?php

                    $percentage =
                        (int)$skill['percentage'];


                    if ($percentage < 0) {

                        $percentage = 0;

                    }


                    if ($percentage > 100) {

                        $percentage = 100;

                    }

                    ?>


                    <!-- =================================
                         SKILL CARD
                    ================================== -->

                    <div class="skill-card">


                        <!-- NAME + PERCENTAGE -->

                        <div class="skill-top">


                            <span class="skill-name">

                                <?= htmlspecialchars(
                                    $skill['skill_name']
                                ); ?>

                            </span>


                            <span class="skill-percent">

                                <?= $percentage; ?>%

                            </span>


                        </div>



                        <!-- SKILL BAR -->

                        <div class="skill-bar">

                            <div
                                class="skill-progress"
                                style="width: <?= $percentage; ?>%;"
                            ></div>

                        </div>



                        <!-- EDIT + DELETE -->

                        <div class="skill-actions">


                            <!-- EDIT -->

                            <a
                                href="edit_skill.php?id=<?= $skill['id']; ?>"
                                class="skill-edit-icon"
                                title="Edit Skill"
                            >

                                <i class="fa-solid fa-pen"></i>

                            </a>



                            <!-- DELETE -->

                            <form
                                action="delete_skill.php"
                                method="POST"
                                class="skill-delete-form"
                                onsubmit="return confirm('Delete this skill?');"
                            >

                                <input
                                    type="hidden"
                                    name="skill_id"
                                    value="<?= $skill['id']; ?>"
                                >


                                <button
                                    type="submit"
                                    class="skill-delete-icon"
                                    title="Delete Skill"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                </button>

                            </form>


                        </div>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <!-- =================================
                     NO SKILLS
                ================================== -->

                <div class="no-skills">

                    <h3>No Skills Added Yet</h3>

                    <p>
                        Add your first professional skill
                        to your portfolio.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </div>


</div>


</body>

</html>