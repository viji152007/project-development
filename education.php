<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once "config/db.php";

if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed.");
}


/* =====================================================
   GET LOGGED-IN USER ID
===================================================== */

$session_username = trim($_SESSION['username']);

$stmt = $conn->prepare("
    SELECT id
    FROM users
    WHERE username = ?
    LIMIT 1
");

if (!$stmt) {
    die("User query error: " . $conn->error);
}

$stmt->bind_param("s", $session_username);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    die("User not found.");
}

$user_id = (int)$user['id'];


/* =====================================================
   DEFAULT EMPTY FORM
===================================================== */

$form_data = [
    'id' => '',
    'education_type' => '',
    'institution_name' => '',
    'course' => '',
    'department' => '',
    'start_year' => '',
    'end_year' => '',
    'percentage' => '',
    'grade' => ''
];

$form_open = false;


/* =====================================================
   DELETE EDUCATION
===================================================== */

if (isset($_GET['delete'])) {

    $delete_id = (int)$_GET['delete'];

    if ($delete_id > 0) {

        $stmt = $conn->prepare("
            DELETE FROM education
            WHERE id = ?
            AND user_id = ?
        ");

        if (!$stmt) {
            die("Delete error: " . $conn->error);
        }

        $stmt->bind_param(
            "ii",
            $delete_id,
            $user_id
        );

        if (!$stmt->execute()) {
            die("Delete failed: " . $stmt->error);
        }

        $stmt->close();
    }

    header("Location: education.php");
    exit();
}


/* =====================================================
   ADD NEW EDUCATION
   ALWAYS EMPTY
===================================================== */

if (isset($_GET['add'])) {

    $form_open = true;

    $form_data = [
        'id' => '',
        'education_type' => '',
        'institution_name' => '',
        'course' => '',
        'department' => '',
        'start_year' => '',
        'end_year' => '',
        'percentage' => '',
        'grade' => ''
    ];
}


/* =====================================================
   EDIT EDUCATION
===================================================== */

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    if ($edit_id > 0) {

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
            WHERE id = ?
            AND user_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            die("Edit query error: " . $conn->error);
        }

        $stmt->bind_param(
            "ii",
            $edit_id,
            $user_id
        );

        $stmt->execute();

        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        $stmt->close();

        if ($data) {

            $form_data = $data;
            $form_open = true;
        }
    }
}


/* =====================================================
   SAVE / UPDATE EDUCATION
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $education_id = (int)($_POST['education_id'] ?? 0);

    $education_type = trim(
        $_POST['education_type'] ?? ''
    );

    $institution_name = trim(
        $_POST['institution_name'] ?? ''
    );

    $course = trim(
        $_POST['course'] ?? ''
    );

    $department = trim(
        $_POST['department'] ?? ''
    );

    $start_year = trim(
        $_POST['start_year'] ?? ''
    );

    $end_year = trim(
        $_POST['end_year'] ?? ''
    );

    $percentage = trim(
        $_POST['percentage'] ?? ''
    );

    $grade = trim(
        $_POST['grade'] ?? ''
    );


    /* =================================================
       VALIDATION
    ================================================= */

    if (
        $education_type === '' ||
        $institution_name === ''
    ) {

        $form_open = true;

        $form_data = [
            'id' => $education_id,
            'education_type' => $education_type,
            'institution_name' => $institution_name,
            'course' => $course,
            'department' => $department,
            'start_year' => $start_year,
            'end_year' => $end_year,
            'percentage' => $percentage,
            'grade' => $grade
        ];

    } else {


        /* =================================================
           UPDATE EXISTING EDUCATION
        ================================================= */

        if ($education_id > 0) {

            $stmt = $conn->prepare("
                UPDATE education
                SET
                    education_type = ?,
                    institution_name = ?,
                    course = ?,
                    department = ?,
                    start_year = ?,
                    end_year = ?,
                    percentage = ?,
                    grade = ?
                WHERE id = ?
                AND user_id = ?
            ");

            if (!$stmt) {
                die("Update error: " . $conn->error);
            }

            $stmt->bind_param(
                "ssssssssii",
                $education_type,
                $institution_name,
                $course,
                $department,
                $start_year,
                $end_year,
                $percentage,
                $grade,
                $education_id,
                $user_id
            );

            if (!$stmt->execute()) {
                die("Update failed: " . $stmt->error);
            }

            $stmt->close();


        } else {


            /* =================================================
               INSERT NEW EDUCATION
            ================================================= */

            $stmt = $conn->prepare("
                INSERT INTO education
                (
                    user_id,
                    education_type,
                    institution_name,
                    course,
                    department,
                    start_year,
                    end_year,
                    percentage,
                    grade
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            if (!$stmt) {
                die("Insert error: " . $conn->error);
            }

            $stmt->bind_param(
                "issssssss",
                $user_id,
                $education_type,
                $institution_name,
                $course,
                $department,
                $start_year,
                $end_year,
                $percentage,
                $grade
            );

            if (!$stmt->execute()) {
                die("Save failed: " . $stmt->error);
            }

            $stmt->close();
        }


        /* =================================================
           AFTER SAVE
        ================================================= */

        header("Location: education.php");
        exit();
    }
}


/* =====================================================
   GET ALL EDUCATION FOR CURRENT USER
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
    WHERE user_id = ?
    ORDER BY id DESC
");

if (!$stmt) {
    die("Education query error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$education = [];

while ($row = $result->fetch_assoc()) {
    $education[] = $row;
}

$stmt->close();

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Education</title>


<link
    rel="stylesheet"
    href="css/style.css"
>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

/* =====================================================
   EDUCATION PAGE ONLY
===================================================== */

.education-page {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 30px 35px;
    box-sizing: border-box;
}


/* =====================================================
   HEADER
===================================================== */

.education-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 22px;
}

.education-title h2 {
    margin: 0 0 6px;
    font-size: 28px;
    color: #111827;
}

.education-title p {
    margin: 0;
    font-size: 14px;
    color: #111827;
}


/* =====================================================
   ADD BUTTON
===================================================== */

.add-education-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 17px;
    border: none;
    border-radius: 7px;
    background: #2196f3;
    color: #ffffff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: 0.2s;
}

.add-education-btn:hover {
    background: #1976d2;
}


/* =====================================================
   FORM BOX
===================================================== */

.education-form-box {
    width: 100%;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 30px;
    box-sizing: border-box;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    margin-bottom: 25px;

    /* Make sure form is visible */
    display: block;
    visibility: visible;
    opacity: 1;
}


/* =====================================================
   FORM HEADER
===================================================== */

.form-box-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.form-box-header h3 {
    display: block;
    visibility: visible;
    margin: 0;
    font-size: 21px;
    color: #111827;
}

.close-form {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 13px;
    border-radius: 7px;
    background: #f3f4f6;
    color: #374151;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}

.close-form:hover {
    background: #e5e7eb;
}


/* =====================================================
   FORM
===================================================== */

.education-form {
    display: flex;
    flex-direction: column;
    gap: 17px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 7px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 11px 13px;
    border: 1px solid #d1d5db;
    border-radius: 7px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #2196f3;
    box-shadow: 0 0 0 3px rgba(33,150,243,0.10);
}


/* =====================================================
   FORM BUTTONS
===================================================== */

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 8px;
}

.clear-btn {
    border: none;
    padding: 10px 17px;
    border-radius: 7px;
    background: #f3f4f6;
    color: #374151;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.clear-btn:hover {
    background: #e5e7eb;
}

.save-btn {
    border: none;
    padding: 10px 18px;
    border-radius: 7px;
    background: #2196f3;
    color: #ffffff;
    font-weight: 600;
    cursor: pointer;
}

.save-btn:hover {
    background: #1976d2;
}


/* =====================================================
   SAVED EDUCATION
===================================================== */

.saved-education {
    width: 100%;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 30px;
    box-sizing: border-box;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.saved-education-title {
    margin: 0 0 20px;
    font-size: 21px;
    color: #111827;
}


/* =====================================================
   EACH SAVED EDUCATION
===================================================== */

.education-row {
    width: 100%;
    padding: 22px 0;
    border-bottom: 1px solid #e5e7eb;
}

.education-row:first-of-type {
    padding-top: 5px;
}

.education-row:last-child {
    border-bottom: none;
    padding-bottom: 5px;
}


/* =====================================================
   ROW HEADER
===================================================== */

.education-row-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
}

.education-row h4 {
    margin: 0 0 6px;
    font-size: 18px;
    color: #111827;
}

.education-institution {
    margin: 0;
    font-size: 15px;
    color: #4b5563;
}


/* =====================================================
   SAVED DETAILS
===================================================== */

.education-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: 16px;
    font-size: 14px;
    color: #4b5563;
}

.education-info span {
    display: block;
    line-height: 1.5;
}

.education-info strong {
    color: #111827;
}


/* =====================================================
   EDIT DELETE
===================================================== */

.education-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.edit-btn,
.delete-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 7px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
}

.edit-btn {
    background: #e8f3ff;
    color: #1976d2;
    border: 1px solid #bfdbfe;
}

.delete-btn {
    background: #fff1f2;
    color: #dc2626;
    border: 1px solid #fecdd3;
}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 700px) {

    .education-page {
        padding: 25px 18px;
    }

    .education-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .add-education-btn {
        width: 100%;
    }

    .education-form-box,
    .saved-education {
        padding: 20px;
    }

    .education-row-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .education-actions {
        width: 100%;
    }

    .edit-btn,
    .delete-btn {
        flex: 1;
    }
}

</style>

</head>


<body>


<!-- SIDEBAR -->

<?php include("sidebar.php"); ?>


<!-- MAIN CONTENT -->

<div class="main-content">

<div class="education-page">


<!-- =====================================================
     PAGE HEADER
===================================================== -->

<div class="education-header">

    <div class="education-title">

        <h2>
            Education
        </h2>

        <p>
            Manage your education details
        </p>

    </div>


    <!-- ADD EDUCATION BUTTON -->

    <?php if (!$form_open): ?>

        <a
            href="education.php?add=1"
            class="add-education-btn"
        >

            <i class="fa-solid fa-plus"></i>

            Add Education

        </a>

    <?php endif; ?>

</div>


<!-- =====================================================
     ADD / EDIT FORM
===================================================== -->

<?php if ($form_open): ?>

<div class="education-form-box">


    <!-- FORM HEADER -->

    <div class="form-box-header">

        <h3>
            Education Details
        </h3>

        <a
            href="education.php"
            class="close-form"
        >

            <i class="fa-solid fa-xmark"></i>

            Close Form

        </a>

    </div>


    <!-- FORM -->

    <form
        action="education.php"
        method="POST"
        class="education-form"
    >


        <!-- HIDDEN EDUCATION ID -->

        <input
            type="hidden"
            name="education_id"
            value="<?= htmlspecialchars($form_data['id']); ?>"
        >


        <!-- EDUCATION TYPE -->

        <div class="form-group">

            <label>
                Education Type
            </label>

            <select
                name="education_type"
                required
            >

                <option value="">
                    Select Education
                </option>

                <option
                    value="10th"
                    <?= $form_data['education_type'] === '10th' ? 'selected' : ''; ?>
                >
                    10th
                </option>

                <option
                    value="12th"
                    <?= $form_data['education_type'] === '12th' ? 'selected' : ''; ?>
                >
                    12th
                </option>

                <option
                    value="College"
                    <?= $form_data['education_type'] === 'College' ? 'selected' : ''; ?>
                >
                    College
                </option>

            </select>

        </div>


        <!-- INSTITUTION -->

        <div class="form-group">

            <label>
                Institution
            </label>

            <input
                type="text"
                name="institution_name"
                placeholder="Enter school / college name"
                value="<?= htmlspecialchars($form_data['institution_name']); ?>"
                required
            >

        </div>


        <!-- COURSE -->

        <div class="form-group">

            <label>
                Course
            </label>

            <input
                type="text"
                name="course"
                placeholder="Enter course"
                value="<?= htmlspecialchars($form_data['course']); ?>"
            >

        </div>


        <!-- DEPARTMENT -->

        <div class="form-group">

            <label>
                Department
            </label>

            <input
                type="text"
                name="department"
                placeholder="Enter department"
                value="<?= htmlspecialchars($form_data['department']); ?>"
            >

        </div>


        <!-- START YEAR -->

        <div class="form-group">

            <label>
                Start Year
            </label>

            <input
                type="text"
                name="start_year"
                placeholder="Example: 2023"
                value="<?= htmlspecialchars($form_data['start_year']); ?>"
            >

        </div>


        <!-- END YEAR -->

        <div class="form-group">

            <label>
                End Year
            </label>

            <input
                type="text"
                name="end_year"
                placeholder="Example: 2026"
                value="<?= htmlspecialchars($form_data['end_year']); ?>"
            >

        </div>


        <!-- PERCENTAGE -->

        <div class="form-group">

            <label>
                Percentage / CGPA
            </label>

            <input
                type="text"
                name="percentage"
                placeholder="Example: 85% / 8.5 CGPA"
                value="<?= htmlspecialchars($form_data['percentage']); ?>"
            >

        </div>


        <!-- GRADE -->

        <div class="form-group">

            <label>
                Grade
            </label>

            <input
                type="text"
                name="grade"
                placeholder="Example: A+"
                value="<?= htmlspecialchars($form_data['grade']); ?>"
            >

        </div>


        <!-- BUTTONS -->

        <div class="form-actions">


            <!-- CLEAR / CLOSE -->

            <a
                href="education.php"
                class="clear-btn"
            >

                Clear

            </a>


            <!-- SAVE -->

            <button
                type="submit"
                class="save-btn"
            >

                <i class="fa-solid fa-save"></i>

                Save

            </button>

        </div>


    </form>

</div>

<?php endif; ?>


<!-- =====================================================
     SAVED EDUCATION
     
     IMPORTANT:
     SHOW ONLY WHEN FORM IS CLOSED
===================================================== -->

<?php if (!$form_open && !empty($education)): ?>

<div class="saved-education">


    <h3 class="saved-education-title">
        Education Details
    </h3>


    <?php foreach ($education as $edu): ?>


    <div class="education-row">


        <!-- ROW HEADER -->

        <div class="education-row-header">


            <div>

                <h4>
                    <?= htmlspecialchars($edu['education_type']); ?>
                </h4>

                <p class="education-institution">
                    <?= htmlspecialchars($edu['institution_name']); ?>
                </p>

            </div>


            <!-- EDIT / DELETE -->

            <div class="education-actions">


                <!-- EDIT -->

                <a
                    href="education.php?edit=<?= (int)$edu['id']; ?>"
                    class="edit-btn"
                >

                    <i class="fa-solid fa-pen"></i>

                    Edit

                </a>


                <!-- DELETE -->

                <a
                    href="education.php?delete=<?= (int)$edu['id']; ?>"
                    class="delete-btn"
                    onclick="return confirm('Are you sure you want to delete this education?');"
                >

                    <i class="fa-solid fa-trash"></i>

                    Delete

                </a>


            </div>

        </div>


        <!-- EDUCATION DETAILS -->

        <div class="education-info">


            <?php if (!empty($edu['course'])): ?>

                <span>

                    <strong>
                        Course:
                    </strong>

                    <?= htmlspecialchars($edu['course']); ?>

                </span>

            <?php endif; ?>


            <?php if (!empty($edu['department'])): ?>

                <span>

                    <strong>
                        Department:
                    </strong>

                    <?= htmlspecialchars($edu['department']); ?>

                </span>

            <?php endif; ?>


            <?php if (!empty($edu['start_year'])): ?>

                <span>

                    <strong>
                        Start Year:
                    </strong>

                    <?= htmlspecialchars($edu['start_year']); ?>

                </span>

            <?php endif; ?>


            <?php if (!empty($edu['end_year'])): ?>

                <span>

                    <strong>
                        End Year:
                    </strong>

                    <?= htmlspecialchars($edu['end_year']); ?>

                </span>

            <?php endif; ?>


            <?php if (!empty($edu['percentage'])): ?>

                <span>

                    <strong>
                        Percentage / CGPA:
                    </strong>

                    <?= htmlspecialchars($edu['percentage']); ?>

                </span>

            <?php endif; ?>


            <?php if (!empty($edu['grade'])): ?>

                <span>

                    <strong>
                        Grade:
                    </strong>

                    <?= htmlspecialchars($edu['grade']); ?>

                </span>

            <?php endif; ?>


        </div>


    </div>


    <?php endforeach; ?>


</div>

<?php endif; ?>


</div>

</div>


</body>

</html>
```
