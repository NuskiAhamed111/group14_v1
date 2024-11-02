<?php
// Enable error reporting (optional for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to the login page
header('Location: public/login.php');
exit();
?>
