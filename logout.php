<?php
// Start the session so we can access it
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the homepage
header("location: home.php");
exit;
?>