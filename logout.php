<?php
require_once 'config.php';

// Logout the user
logout_user();

// Redirect to login page
header('Location: index.php');
exit();
?>
