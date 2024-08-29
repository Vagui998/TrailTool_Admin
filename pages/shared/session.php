<?php
// session.php

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // If the username is not set in the session, redirect to the login page
    header("Location: login.php");
    exit();
}

// Load session variables
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "Unknown User";
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
