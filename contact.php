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


/* =====================================================
   DATABASE CHECK
===================================================== */

if (!isset($conn) || $conn->connect_error) {

    die("Database connection failed.");

}


/* =====================================================
   GET LOGGED-IN USER
===================================================== */

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


if (!$user) {

    die("User not found.");

}


$user_id = (int)$user['id'];


/* =====================================================
   MESSAGE
===================================================== */

$message = "";
$message_type = "";


/* =====================================================
   DELETE CONTACT
   DELETE DIRECTLY FROM DATABASE
===================================================== */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_contact'])
) {


    /*
       Delete ONLY the logged-in user's
       contact record.
    */

    $stmt = $conn->prepare("
        DELETE FROM contact
        WHERE user_id = ?
    ");


    if (!$stmt) {

        die(
            "Delete query error: " .
            $conn->error
        );

    }


    $stmt->bind_param(
        "i",
        $user_id
    );


    if ($stmt->execute()) {

        $stmt->close();


        /*
           Redirect to avoid duplicate
           deletion on refresh.
        */

        header(
            "Location: contact.php?success=deleted"
        );

        exit();

    }


    $message =
        "Unable to delete contact details.";

    $message_type = "error";


    $stmt->close();

}


/* =====================================================
   SUCCESS MESSAGE
===================================================== */

if (isset($_GET['success'])) {


    if ($_GET['success'] === 'added') {

        $message =
            "Contact details added successfully.";

        $message_type = "success";

    }


    elseif ($_GET['success'] === 'updated') {

        $message =
            "Contact details updated successfully.";

        $message_type = "success";

    }


    elseif ($_GET['success'] === 'deleted') {

        $message =
            "Contact details deleted successfully.";

        $message_type = "success";

    }

}


/* =====================================================
   DEFAULT FORM DATA
===================================================== */

$form_data = [

    'id' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'pincode' => '',
    'alternate_email' => '',
    'linkedin' => '',
    'github' => '',
    'website' => ''

];


/* =====================================================
   GET EXISTING CONTACT
===================================================== */

$stmt = $conn->prepare("
    SELECT
        id,
        address,
        city,
        state,
        pincode,
        alternate_email,
        linkedin,
        github,
        website
    FROM contact
    WHERE user_id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Contact query error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$contact = $result->fetch_assoc();

$stmt->close();


/* =====================================================
   FORM OPEN
===================================================== */

$form_open = false;


/*
   Existing contact:
   Form opens only when edit=1
*/

if (
    isset($_GET['edit']) &&
    $_GET['edit'] === '1' &&
    $contact
) {

    $form_data = $contact;

    $form_open = true;

}


/*
   No contact:
   Form opens when add=1
*/

if (
    isset($_GET['add']) &&
    $_GET['add'] === '1' &&
    !$contact
) {

    $form_open = true;

}


/* =====================================================
   SAVE / UPDATE CONTACT
===================================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['save_contact'])
) {


    $contact_id = (int)(
        $_POST['contact_id'] ?? 0
    );


    $address = trim(
        $_POST['address'] ?? ''
    );


    $city = trim(
        $_POST['city'] ?? ''
    );


    $state = trim(
        $_POST['state'] ?? ''
    );


    $pincode = trim(
        $_POST['pincode'] ?? ''
    );


    $alternate_email = trim(
        $_POST['alternate_email'] ?? ''
    );


    $linkedin = trim(
        $_POST['linkedin'] ?? ''
    );


    $github = trim(
        $_POST['github'] ?? ''
    );


    $website = trim(
        $_POST['website'] ?? ''
    );


    /* =================================================
       UPDATE
    ================================================= */

    if ($contact_id > 0) {


        $stmt = $conn->prepare("
            UPDATE contact
            SET
                address = ?,
                city = ?,
                state = ?,
                pincode = ?,
                alternate_email = ?,
                linkedin = ?,
                github = ?,
                website = ?
            WHERE id = ?
              AND user_id = ?
        ");


        if (!$stmt) {

            die(
                "Update query error: " .
                $conn->error
            );

        }


        $stmt->bind_param(
            "ssssssssii",
            $address,
            $city,
            $state,
            $pincode,
            $alternate_email,
            $linkedin,
            $github,
            $website,
            $contact_id,
            $user_id
        );


        if ($stmt->execute()) {

            $stmt->close();


            header(
                "Location: contact.php?success=updated"
            );

            exit();

        }


        $message =
            "Contact update failed.";

        $message_type = "error";


        $stmt->close();

    }


    /* =================================================
       INSERT
    ================================================= */

    else {


        /*
           Check whether this user already
           has contact details.
        */

        $check_stmt = $conn->prepare("
            SELECT id
            FROM contact
            WHERE user_id = ?
            LIMIT 1
        ");


        if (!$check_stmt) {

            die(
                "Contact check error: " .
                $conn->error
            );

        }


        $check_stmt->bind_param(
            "i",
            $user_id
        );


        $check_stmt->execute();

        $check_result =
            $check_stmt->get_result();

        $already_exists =
            $check_result->fetch_assoc();

        $check_stmt->close();


        if ($already_exists) {

            header(
                "Location: contact.php?edit=1"
            );

            exit();

        }


        /* =============================================
           INSERT CONTACT
        ============================================= */

        $stmt = $conn->prepare("
            INSERT INTO contact
            (
                user_id,
                address,
                city,
                state,
                pincode,
                alternate_email,
                linkedin,
                github,
                website
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

            die(
                "Insert query error: " .
                $conn->error
            );

        }


        $stmt->bind_param(
            "issssssss",
            $user_id,
            $address,
            $city,
            $state,
            $pincode,
            $alternate_email,
            $linkedin,
            $github,
            $website
        );


        if ($stmt->execute()) {

            $stmt->close();


            header(
                "Location: contact.php?success=added"
            );

            exit();

        }


        $message =
            "Contact details could not be added.";

        $message_type = "error";


        $stmt->close();

    }

}


/* =====================================================
   REFRESH CONTACT AFTER SAVE / UPDATE / DELETE
===================================================== */

$stmt = $conn->prepare("
    SELECT
        id,
        address,
        city,
        state,
        pincode,
        alternate_email,
        linkedin,
        github,
        website
    FROM contact
    WHERE user_id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Contact refresh query error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();

$contact = $result->fetch_assoc();

$stmt->close();


/* =====================================================
   EDIT MODE
===================================================== */

if (
    isset($_GET['edit']) &&
    $_GET['edit'] === '1' &&
    $contact
) {

    $form_data = $contact;

    $form_open = true;

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

<title>Contact</title>


<!-- EXISTING WEBSITE CSS -->

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

/* =====================================================
   CONTACT PAGE
===================================================== */

.contact-page {

    width: 100%;

    max-width: 1050px;

    margin: 0 auto;

    padding: 35px 30px;

    box-sizing: border-box;

}


/* =====================================================
   HEADER
===================================================== */

.contact-header {

    margin-bottom: 22px;

}


.contact-title h2 {

    margin: 0 0 6px;

    font-size: 28px;

    color: #111827;

}


.contact-title p {

    margin: 0;

    font-size: 14px;

    color: #111827;

}


/* =====================================================
   MESSAGE
===================================================== */

.contact-message {

    width: 100%;

    padding: 12px 15px;

    border-radius: 8px;

    margin-bottom: 20px;

    box-sizing: border-box;

    font-size: 14px;

}


.contact-message.success {

    background: #dcfce7;

    color: #166534;

    border: 1px solid #bbf7d0;

}


.contact-message.error {

    background: #fee2e2;

    color: #991b1b;

    border: 1px solid #fecaca;

}


/* =====================================================
   ADD CONTACT EMPTY BOX
===================================================== */

.add-contact-box {

    width: 100%;

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 45px 30px;

    box-sizing: border-box;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.08);

    text-align: center;

}


.add-contact-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 18px;

    border-radius: 50%;

    background: #e3f2fd;

    color: #2196f3;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

}


.add-contact-box h3 {

    margin: 0 0 8px;

    font-size: 22px;

    color: #111827;

}


.add-contact-box p {

    margin: 0 auto 22px;

    max-width: 500px;

    font-size: 14px;

    color: #6b7280;

    line-height: 1.6;

}


.add-contact-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 11px 20px;

    background: #2196f3;

    color: #ffffff;

    border-radius: 7px;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

}


.add-contact-btn:hover {

    background: #1976d2;

}


/* =====================================================
   FORM BOX
===================================================== */

.contact-form-box {

    width: 100%;

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 30px;

    box-sizing: border-box;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.08);

}


.form-box-header {

    margin-bottom: 25px;

}


.form-box-header h3 {

    margin: 0;

    font-size: 21px;

    color: #111827;

}


/* =====================================================
   FORM
===================================================== */

.contact-form {

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
.form-group textarea {

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


.form-group textarea {

    min-height: 100px;

    resize: vertical;

}


.form-group input:focus,
.form-group textarea:focus {

    border-color: #2196f3;

    box-shadow:
        0 0 0 3px rgba(33,150,243,0.10);

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


.cancel-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    padding: 10px 18px;

    border-radius: 7px;

    background: #f3f4f6;

    color: #374151;

    border: 1px solid #d1d5db;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

}


.cancel-btn:hover {

    background: #e5e7eb;

}


.save-btn {

    border: none;

    padding: 10px 20px;

    border-radius: 7px;

    background: #2196f3;

    color: #ffffff;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

}


.save-btn:hover {

    background: #1976d2;

}


/* =====================================================
   SAVED CONTACT
===================================================== */

.saved-contact {

    width: 100%;

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 30px;

    box-sizing: border-box;

    box-shadow:
        0 4px 15px rgba(0,0,0,0.08);

}


.saved-contact-title {

    margin: 0 0 22px;

    font-size: 21px;

    color: #111827;

}


/* =====================================================
   DETAILS
===================================================== */

.contact-detail-list {

    display: flex;

    flex-direction: column;

    gap: 14px;

}


.contact-detail {

    padding: 13px 15px;

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    border-radius: 8px;

    font-size: 14px;

    color: #4b5563;

    line-height: 1.6;

}


.contact-detail strong {

    color: #111827;

}


.contact-detail a {

    color: #1976d2;

    text-decoration: none;

    word-break: break-all;

}


.contact-detail a:hover {

    text-decoration: underline;

}


/* =====================================================
   ACTION BUTTONS
===================================================== */

.contact-actions {

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    margin-top: 22px;

}


.update-contact-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 10px 18px;

    background: #2196f3;

    color: #ffffff;

    border-radius: 7px;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

}


.update-contact-btn:hover {

    background: #1976d2;

}


/* =====================================================
   DELETE BUTTON
===================================================== */

.delete-contact-form {

    margin: 0;

}


.delete-contact-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    padding: 10px 18px;

    background: #ef4444;

    color: #ffffff;

    border: none;

    border-radius: 7px;

    font-size: 14px;

    font-weight: 600;

    cursor: pointer;

}


.delete-contact-btn:hover {

    background: #dc2626;

}


/* =====================================================
   MOBILE
===================================================== */

@media (max-width:700px) {

    .contact-page {

        padding: 25px 18px;

    }


    .contact-form-box,
    .saved-contact,
    .add-contact-box {

        padding: 22px;

    }


    .form-actions {

        flex-direction: column;

    }


    .cancel-btn,
    .save-btn {

        width: 100%;

    }


    .contact-actions {

        flex-direction: column;

    }


    .update-contact-btn,
    .delete-contact-btn {

        width: 100%;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<?php include("sidebar.php"); ?>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="content">


<div class="contact-page">


<!-- =====================================================
     HEADER
===================================================== -->

<div class="contact-header">

    <div class="contact-title">

        <h2>
            Contact
        </h2>

        <p>
            Manage your contact details
        </p>

    </div>

</div>


<!-- =====================================================
     MESSAGE
===================================================== -->

<?php if ($message !== ""): ?>

    <div
        class="contact-message <?php echo
            ($message_type === 'success')
            ? 'success'
            : 'error';
        ?>"
    >

        <?php
        echo htmlspecialchars($message);
        ?>

    </div>

<?php endif; ?>


<!-- =====================================================
     FORM
===================================================== -->

<?php if ($form_open): ?>


<div class="contact-form-box">


    <div class="form-box-header">

        <h3>

            <?php

            echo $contact
                ? 'Update Contact Details'
                : 'Add Your Contact Details';

            ?>

        </h3>

    </div>


    <form
        action="contact.php"
        method="POST"
        class="contact-form"
    >


        <input
            type="hidden"
            name="contact_id"
            value="<?php
                echo htmlspecialchars(
                    $form_data['id']
                );
            ?>"
        >


        <!-- ADDRESS -->

        <div class="form-group">

            <label>
                Address
            </label>

            <textarea
                name="address"
                placeholder="Enter your address"
            ><?php
                echo htmlspecialchars(
                    $form_data['address']
                );
            ?></textarea>

        </div>


        <!-- CITY -->

        <div class="form-group">

            <label>
                City
            </label>

            <input
                type="text"
                name="city"
                placeholder="Enter city"
                value="<?php
                    echo htmlspecialchars(
                        $form_data['city']
                    );
                ?>"
            >

        </div>


        <!-- STATE -->

        <div class="form-group">

            <label>
                State
            </label>

            <input
                type="text"
                name="state"
                placeholder="Enter state"
                value="<?php
                    echo htmlspecialchars(
                        $form_data['state']
                    );
                ?>"
            >

        </div>


        <!-- PINCODE -->

        <div class="form-group">

            <label>
                Pincode
            </label>

            <input
                type="text"
                name="pincode"
                placeholder="Enter pincode"
                value="<?php
                    echo htmlspecialchars(
                        $form_data['pincode']
                    );
                ?>"
            >

        </div>


        <!-- ALTERNATE EMAIL -->

        <div class="form-group">

            <label>
                Alternate Email
            </label>

            <input
                type="email"
                name="alternate_email"
                placeholder="Enter alternate email"
                value="<?php
                    echo htmlspecialchars(
                        $form_data['alternate_email']
                    );
                ?>"
            >

        </div>


        <!-- LINKEDIN -->

        <div class="form-group">

            <label>
                LinkedIn
            </label>

            <input
                type="url"
                name="linkedin"
                placeholder="https://linkedin.com/in/username"
                value="<?php
                    echo htmlspecialchars(
                        $form_data['linkedin']
                    );
                ?>"
            >

        </div>


        <!-- GITHUB -->

        <div class="form-group">

            <label>
                GitHub
            </label>

            <input
                type="url"
                name="github"
                placeholder="https://github.com/username"
                value="<?php
                    echo htmlspecialchars(
                        $form_data['github']
                    );
                ?>"
            >

        </div>


        <!-- WEBSITE -->

        <div class="form-group">

            <label>
                Personal Website
            </label>

            <input
                type="url"
                name="website"
                placeholder="https://example.com"
                value="<?php
                    echo htmlspecialchars(
                        $form_data['website']
                    );
                ?>"
            >

        </div>


        <!-- BUTTONS -->

        <div class="form-actions">


            <a
                href="contact.php"
                class="cancel-btn"
            >

                <i class="fa-solid fa-xmark"></i>

                Cancel

            </a>


            <button
                type="submit"
                name="save_contact"
                class="save-btn"
            >

                <i class="fa-solid fa-save"></i>

                <?php

                echo $contact
                    ? 'Update'
                    : 'Save';

                ?>

            </button>


        </div>


    </form>


</div>


<!-- =====================================================
     NO CONTACT
===================================================== -->

<?php elseif (!$contact): ?>


<div class="add-contact-box">


    <div class="add-contact-icon">

        <i class="fa-solid fa-address-card"></i>

    </div>


    <h3>
        Add Your Contact Details
    </h3>


    <p>

        Add your address, alternate email,
        LinkedIn, GitHub and personal website
        to complete your portfolio.

    </p>


    <a
        href="contact.php?add=1"
        class="add-contact-btn"
    >

        <i class="fa-solid fa-plus"></i>

        Add Contact Details

    </a>


</div>


<!-- =====================================================
     SAVED CONTACT
===================================================== -->

<?php else: ?>


<div class="saved-contact">


    <h3 class="saved-contact-title">

        <i class="fa-solid fa-address-card"></i>

        Contact Details

    </h3>


    <div class="contact-detail-list">


        <!-- ADDRESS -->

        <?php if (!empty($contact['address'])): ?>

        <div class="contact-detail">

            <strong>
                Address:
            </strong>

            <br>

            <?php

            echo nl2br(
                htmlspecialchars(
                    $contact['address']
                )
            );

            ?>

        </div>

        <?php endif; ?>


        <!-- CITY -->

        <?php if (!empty($contact['city'])): ?>

        <div class="contact-detail">

            <strong>
                City:
            </strong>

            <?php

            echo htmlspecialchars(
                $contact['city']
            );

            ?>

        </div>

        <?php endif; ?>


        <!-- STATE -->

        <?php if (!empty($contact['state'])): ?>

        <div class="contact-detail">

            <strong>
                State:
            </strong>

            <?php

            echo htmlspecialchars(
                $contact['state']
            );

            ?>

        </div>

        <?php endif; ?>


        <!-- PINCODE -->

        <?php if (!empty($contact['pincode'])): ?>

        <div class="contact-detail">

            <strong>
                Pincode:
            </strong>

            <?php

            echo htmlspecialchars(
                $contact['pincode']
            );

            ?>

        </div>

        <?php endif; ?>


        <!-- ALTERNATE EMAIL -->

        <?php if (!empty($contact['alternate_email'])): ?>

        <div class="contact-detail">

            <strong>
                Alternate Email:
            </strong>

            <?php

            echo htmlspecialchars(
                $contact['alternate_email']
            );

            ?>

        </div>

        <?php endif; ?>


        <!-- LINKEDIN -->

        <?php if (!empty($contact['linkedin'])): ?>

        <div class="contact-detail">

            <strong>
                LinkedIn:
            </strong>

            <a
                href="<?php
                    echo htmlspecialchars(
                        $contact['linkedin']
                    );
                ?>"
                target="_blank"
                rel="noopener noreferrer"
            >

                <?php

                echo htmlspecialchars(
                    $contact['linkedin']
                );

                ?>

            </a>

        </div>

        <?php endif; ?>


        <!-- GITHUB -->

        <?php if (!empty($contact['github'])): ?>

        <div class="contact-detail">

            <strong>
                GitHub:
            </strong>

            <a
                href="<?php
                    echo htmlspecialchars(
                        $contact['github']
                    );
                ?>"
                target="_blank"
                rel="noopener noreferrer"
            >

                <?php

                echo htmlspecialchars(
                    $contact['github']
                );

                ?>

            </a>

        </div>

        <?php endif; ?>


        <!-- WEBSITE -->

        <?php if (!empty($contact['website'])): ?>

        <div class="contact-detail">

            <strong>
                Personal Website:
            </strong>

            <a
                href="<?php
                    echo htmlspecialchars(
                        $contact['website']
                    );
                ?>"
                target="_blank"
                rel="noopener noreferrer"
            >

                <?php

                echo htmlspecialchars(
                    $contact['website']
                );

                ?>

            </a>

        </div>

        <?php endif; ?>


    </div>


    <!-- =================================================
         UPDATE + DELETE
    ================================================= -->

    <div class="contact-actions">


        <!-- UPDATE -->

        <a
            href="contact.php?edit=1"
            class="update-contact-btn"
        >

            <i class="fa-solid fa-pen"></i>

            Update

        </a>


        <!-- DELETE DIRECTLY -->

        <form
            action="contact.php"
            method="POST"
            class="delete-contact-form"
            onsubmit="return confirm('Are you sure you want to delete your contact details permanently?');"
        >

            <button
                type="submit"
                name="delete_contact"
                class="delete-contact-btn"
            >

                <i class="fa-solid fa-trash"></i>

                Delete

            </button>

        </form>


    </div>


</div>


<?php endif; ?>


</div>

</div>


</body>

</html>