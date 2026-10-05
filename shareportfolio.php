<?php

session_start();

/* =====================================================
   LOGIN CHECK
===================================================== */

if (
    !isset($_SESSION['username']) &&
    !isset($_SESSION['user_id']) &&
    !isset($_SESSION['user'])
) {
    header("Location: login.php");
    exit();
}


/* =====================================================
   DATABASE
===================================================== */

include "config/db.php";


/* =====================================================
   GET USER
===================================================== */

$user = null;


/* USER ID */

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("
        SELECT id, username, fullname
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $user_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    $stmt->close();
}


/* USERNAME FALLBACK */

if (!$user && isset($_SESSION['username'])) {

    $username = $_SESSION['username'];

    $stmt = $conn->prepare("
        SELECT id, username, fullname
        FROM users
        WHERE username = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $username);

    $stmt->execute();

    $result = $stmt->get_result();

    $user = $result->fetch_assoc();

    $stmt->close();
}


/* USER NOT FOUND */

if (!$user) {

    session_destroy();

    header("Location: login.php");

    exit();
}


/* =====================================================
   USER DATA
===================================================== */

$username = $user['username'];

$fullname = $user['fullname'];


/* =====================================================
   PORTFOLIO URL
===================================================== */

$portfolio_url =
    "http://localhost:8080/vijiii/portfolio/"
    . rawurlencode($username);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Share Portfolio</title>


    <!-- MAIN CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<?php include 'sidebar.php'; ?>


<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<div class="content">


    <div class="dashboard-card">


        <h1>

            <i class="fa-solid fa-share-nodes"></i>

            Share Portfolio

        </h1>


        <p>

            Share your personal portfolio with others.

        </p>


        <br>


        <!-- PORTFOLIO URL -->

        <h3>

            Portfolio URL

        </h3>


        <input
            type="text"
            value="<?php echo htmlspecialchars($portfolio_url); ?>"
            readonly
        >


        <br><br>


        <!-- VIEW PORTFOLIO -->

        <a
            href="<?php echo htmlspecialchars($portfolio_url); ?>"
            target="_blank"
            rel="noopener noreferrer"
            class="btn"
        >

            <i class="fa-solid fa-eye"></i>

            View Portfolio

        </a>


        <!-- SHARE PORTFOLIO -->

        <button
            type="button"
            class="btn"
            onclick="sharePortfolio()"
        >

            <i class="fa-solid fa-share-nodes"></i>

            Share Portfolio

        </button>


    </div>


</div>


<script>

function sharePortfolio() {

    const url =
        <?php echo json_encode($portfolio_url); ?>;


    if (navigator.share) {

        navigator.share({

            title: "My Portfolio",

            text: "Check out my portfolio",

            url: url

        }).catch(function(error) {

            console.log("Share cancelled.");

        });

    } else {

        if (
            navigator.clipboard &&
            window.isSecureContext
        ) {

            navigator.clipboard.writeText(url)

                .then(function() {

                    alert(
                        "Portfolio URL copied successfully!"
                    );

                })

                .catch(function() {

                    copyPortfolioLink(url);

                });

        } else {

            copyPortfolioLink(url);

        }

    }

}


/* =====================================================
   COPY FALLBACK
===================================================== */

function copyPortfolioLink(url) {

    const textarea =
        document.createElement("textarea");

    textarea.value = url;

    textarea.style.position = "fixed";

    textarea.style.left = "-9999px";

    document.body.appendChild(textarea);

    textarea.focus();

    textarea.select();

    try {

        document.execCommand("copy");

        alert(
            "Portfolio URL copied successfully!"
        );

    } catch (error) {

        alert(
            "Please copy the Portfolio URL manually."
        );

    }

    document.body.removeChild(textarea);

}

</script>


</body>

</html>