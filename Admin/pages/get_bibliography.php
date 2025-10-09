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

// Build bibliography file path
$baseName = pathinfo($htmlFilePath, PATHINFO_FILENAME);
$dir = dirname($htmlFilePath);
$bibliographyPath = $dir . DIRECTORY_SEPARATOR . $baseName . '_bibliography.html';

// Read and return the bibliography HTML content
if (file_exists($bibliographyPath)) {
    // Set appropriate content type for HTML
    header('Content-Type: text/html; charset=utf-8');
    
    // Read and output the HTML file directly
    $htmlContent = file_get_contents($bibliographyPath);
    echo $htmlContent;
} else {
    log_to_console("Bibliography file not found: " . $bibliographyPath);
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>No Bibliography</title></head><body><h1>No Bibliography</h1><p>Bibliography content is not available for this document.</p></body></html>";
}
?>
