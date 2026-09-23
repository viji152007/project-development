<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Personal Portfolio Website</title>


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Existing CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>

<style>
body { display:flex; justify-content:center; align-items:center; min-height:100vh; }
</style>
<div class="container">


    <div class="card">


        <!-- =========================================
             COMMON WEBSITE TITLE
        ========================================== -->

        <h1>

            <i class="fa-solid fa-user"></i>

            Personal Portfolio Website

        </h1>


        <!-- =========================================
             DESCRIPTION
        ========================================== -->

        <p class="title">

            Create your professional portfolio
            and share it with everyone.

        </p>


        <!-- =========================================
             REGISTER
        ========================================== -->

        <p class="text">

            New User? Create an account to build your portfolio.

        </p>


        <a
            href="register.php"
            class="btn"
        >

            <i class="fa-solid fa-user-plus"></i>

            Register

        </a>


        <!-- =========================================
             LOGIN
        ========================================== -->

        <p class="text2">

            Already have an account? Login here.

        </p>


        <a
            href="login.php"
            class="btn"
        >

            <i class="fa-solid fa-right-to-bracket"></i>

            Login

        </a>


    </div>


</div>


</body>

</html>

