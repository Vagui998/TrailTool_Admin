<?php
require_once 'shared/database.php';

// Function to log PHP errors to the console
function log_to_console($data) {
    echo "<script>console.log('PHP: " . addslashes(json_encode($data)) . "');</script>";
}

// Get the HTML file ID from the URL
$htmlFileId = $_GET['id'] ?? null;

// Check if the ID is provided
if (!$htmlFileId) {
    log_to_console("Error: No HTML file ID provided.");
    die("Error: No HTML file ID provided.");
}

// Fetch the file path from the database
try {
    $db = getDbConnection();
    $query = "SELECT path FROM html_files WHERE id = ?";
    $stmt = $db->prepare($query);
    if (!$stmt) {
        log_to_console("Database prepare error: " . $db->error);
        throw new Exception("Database error: " . $db->error);
    }
    $stmt->bind_param("i", $htmlFileId);
    $stmt->execute();
    $stmt->bind_result($htmlFilePath);
    $stmt->fetch();
    $stmt->close();
    
    // If no path found for the given ID, show an error
    if (!$htmlFilePath) {
        log_to_console("Error: No file path found for the given ID.");
        die("Error: No file path found for the given ID.");
    }
} catch (Exception $e) {
    log_to_console($e->getMessage());
    die("Error: A database error occurred.");
}

// Read the title of the HTML file
$title = '';
if (file_exists($htmlFilePath)) {
    $existingHtml = file_get_contents($htmlFilePath);
    preg_match('/<title>(.*?)<\/title>/', $existingHtml, $titleMatch);
    $title = $titleMatch[1] ?? 'No title found';
} else {
    log_to_console("File not found: " . $htmlFilePath);
    die("Error: HTML file not found.");
}

// Return the title as a JSON response
header('Content-Type: application/json');
echo json_encode(['title' => $title]);

?>
