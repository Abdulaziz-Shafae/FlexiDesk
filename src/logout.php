<?php
session_start();  // Start the session to manage login state

// Destroy the session to log the user out
session_destroy();

// Redirect to the login page after logout
header('Location: about.html');
exit;
?>
