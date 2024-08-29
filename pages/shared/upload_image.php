<?php
// Path where you want to save the uploaded images
$uploadDir = '../../docs/' . $_GET['id'] . '/';

// Ensure the directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle the upload
if ($_FILES['upload']) {
    $fileName = basename($_FILES['upload']['name']);
    $uploadFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadFile)) {
        $url = $uploadDir . $fileName;

        // Return JSON response for CKEditor
        echo json_encode(array(
            "uploaded" => 1,
            "fileName" => $fileName,
            "url" => $url
        ));
    } else {
        echo json_encode(array(
            "uploaded" => 0,
            "error" => array(
                "message" => "Failed to move uploaded file."
            )
        ));
    }
}
?>
