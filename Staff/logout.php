<?php
// Start the session
session_start();

// Delete all session data - logs staff out
session_destroy();

// Send staff back to login page
header('Location: login.php');
exit;
?>