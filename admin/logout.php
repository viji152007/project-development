<?php

session_start();

/* Destroy Admin Session */
$_SESSION = array();

session_destroy();

/* Redirect to Admin Login */
header("Location: login.php");
exit();

?>