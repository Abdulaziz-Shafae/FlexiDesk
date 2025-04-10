<?php
session_start();  // Start the session
session_destroy();  // Destroy the session
header('Location: homePage.html');  // Redirect to home page or login page
?>
