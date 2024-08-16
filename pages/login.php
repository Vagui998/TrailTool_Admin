<?php
session_start();
require_once './shared/database.php'; // Include the database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the inputted password using SHA512
    $hashed_password = hash('sha512', $password);

    // Connect to the database
    $conn = getDbConnection();

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $hashed_password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Login successful, fetch user data
        $stmt->bind_result($user_id, $username);
        $stmt->fetch();

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;

        // Redirect to dashboard or another secure page
        header("Location: ./intro.php");
        exit();
    } else {
        // Invalid login attempt
        echo "<script>alert('Invalid email or password. Please try again.'); window.location.href='login.html';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: login.html"); // Redirect back to login if accessed directly
    exit();
}
