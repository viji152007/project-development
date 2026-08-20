<?php
session_start();

require_once "config/db.php";

/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = trim($_SESSION['username']);


/* =====================================================
   MESSAGE
===================================================== */

$message = "";
$message_type = "";

if (isset($_GET['success'])) {

    if ($_GET['success'] === 'added') {
        $message = "Education added successfully.";
        $message_type = "success";
    }

    elseif ($_GET['success'] === 'updated') {
        $message = "Education updated successfully.";
        $message_type = "success";
    }

    elseif ($_GET['success'] === 'deleted') {
        $message = "Education deleted successfully.";
        $message_type = "success";
    }
}


/* =====================================================
   GET EDUCATION DETAILS
===================================================== */

$stmt = $conn->prepare("
    SELECT
        id,
        education_type,
        institution_name,
        course,
        department,
        start_year,
        end_year,
        percentage,
        grade
    FROM education
    WHERE username = ?
");

$stmt->bind_param("s", $username);

$stmt->execute();

$result = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Education</title>

    <link rel="stylesheet" href="css/style.css">

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<?php include "sidebar.php"; ?>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="content">

    <div class="education-page">


        <!-- =================================================
             HEADER
        ================================================== -->

        <div class="education-header">

            <div>

                <h2>Education</h2>

                <p>
                    Manage your academic qualifications
                </p>

            </div>


            <a
                href="add_education.php"
                class="add-education-btn"
            >
                + Add Education
            </a>

        </div>


        <!-- =================================================
             MESSAGE
        ================================================== -->

        <?php if ($message !== ""): ?>

            <div
                class="<?php echo ($message_type === 'success')
                    ? 'success'
                    : 'error-message'; ?>"
                style="
                    margin-bottom:20px;
                    padding:12px 15px;
                "
            >

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             EDUCATION LIST
        ================================================== -->

        <?php if ($result->num_rows > 0): ?>

            <div class="education-list">


                <?php while ($row = $result->fetch_assoc()): ?>


                    <div class="education-card">


                        <!-- EDUCATION TYPE -->

                        <span class="education-type">

                            <?php
                            echo htmlspecialchars(
                                $row['education_type']
                            );
                            ?>

                        </span>


                        <!-- INSTITUTION -->

                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $row['institution_name']
                            );
                            ?>

                        </h3>


                        <!-- COURSE -->

                        <?php if (!empty($row['course'])): ?>

                            <div class="course">

                                <?php
                                echo htmlspecialchars(
                                    $row['course']
                                );
                                ?>

                            </div>

                        <?php endif; ?>


                        <!-- DETAILS -->

                        <div class="education-info">


                            <?php if (!empty($row['department'])): ?>

                                <div>

                                    <strong>
                                        Department:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['department']
                                    );
                                    ?>

                                </div>

                            <?php endif; ?>


                            <?php
                            if (
                                !empty($row['start_year']) ||
                                !empty($row['end_year'])
                            ):
                            ?>

                                <div>

                                    <strong>
                                        Year:
                                    </strong>

                                    <?php

                                    if (!empty($row['start_year'])) {
                                        echo htmlspecialchars(
                                            $row['start_year']
                                        );
                                    }

                                    if (
                                        !empty($row['start_year']) &&
                                        !empty($row['end_year'])
                                    ) {
                                        echo " - ";
                                    }

                                    if (!empty($row['end_year'])) {
                                        echo htmlspecialchars(
                                            $row['end_year']
                                        );
                                    }

                                    ?>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($row['percentage'])): ?>

                                <div>

                                    <strong>
                                        Percentage / CGPA:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['percentage']
                                    );
                                    ?>

                                </div>

                            <?php endif; ?>


                            <?php if (!empty($row['grade'])): ?>

                                <div>

                                    <strong>
                                        Grade:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['grade']
                                    );
                                    ?>

                                </div>

                            <?php endif; ?>


                        </div>


                        <!-- =================================================
                             ACTION BUTTONS
                        ================================================== -->

                        <div class="education-actions">


                            <a
                                href="edit_education.php?id=<?php echo (int)$row['id']; ?>"
                                class="education-edit-btn"
                            >
                                Edit
                            </a>


                            <a
                                href="delete_education.php?id=<?php echo (int)$row['id']; ?>"
                                class="education-delete-btn"
                                onclick="return confirm('Are you sure you want to delete this education?');"
                            >
                                Delete
                            </a>


                        </div>


                    </div>


                <?php endwhile; ?>


            </div>


        <?php else: ?>


            <!-- =================================================
                 NO EDUCATION
            ================================================== -->

            <div class="no-education">

                <h3>No Education Added</h3>

                <p>
                    Add your 10th, 12th and college details
                    to display them here.
                </p>

                <br>

                <a
                    href="add_education.php"
                    class="add-education-btn"
                >
                    + Add Education
                </a>

            </div>


        <?php endif; ?>


    </div>

</div>


</body>
</html>


<?php

$stmt->close();

?>