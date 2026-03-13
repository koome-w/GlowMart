<?php
// admin/php/logout.php
session_start();

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: ../html/login.html');
exit;
?>
