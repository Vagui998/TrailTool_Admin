<?php
// Start session
session_start();

// Destroy session data
$_SESSION = [];
session_unset();
session_destroy();

// Respond with success
echo json_encode(['status' => 'success']);
exit;
?>
