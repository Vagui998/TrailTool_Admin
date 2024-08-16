<?php
require_once 'database.php';
header('Content-Type: application/json');

// Connect to the database
$conn = getDbConnection();

// Function to fetch child items recursively
function fetchChildItems($conn, $parentId = NULL) {
    // Modify the query to check for NULL
    if ($parentId === NULL) {
        $stmt = $conn->prepare("SELECT id, name, icon_class, link FROM sidebar_items WHERE parent_id IS NULL ORDER BY position");
    } else {
        $stmt = $conn->prepare("SELECT id, name, icon_class, link FROM sidebar_items WHERE parent_id = ? ORDER BY position");
        $stmt->bind_param("i", $parentId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        // Recursively fetch children
        $row['children'] = fetchChildItems($conn, $row['id']);
        $items[] = $row;
    }
    $stmt->close();

    return $items;
}

// Fetch top-level items
$sidebarItems = fetchChildItems($conn, NULL);

echo json_encode($sidebarItems);
$conn->close();
