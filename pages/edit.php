<?php
require_once 'shared/session.php';
require_once 'shared/database.php';

// Start the session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to log PHP errors to the console
function log_to_console($data) {
    echo "<script>console.log('PHP: " . addslashes(json_encode($data)) . "');</script>";
}

// Initialize messages
$message = '';
$messageType = 'info'; // Default message type

// Check if a message is passed via URL
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    if (strpos($message, 'Error:') !== false) {
        $messageType = 'danger'; // Set to 'danger' if it's an error
    }
}

$db = getDbConnection();

// Get the HTML file ID from the URL
$htmlFileId = $_GET['id'] ?? null;

// Get the backup index from the URL (if restoring)
$currentBackupIndex = isset($_GET['backup_index']) ? (int) $_GET['backup_index'] : 1;

// Check if the ID is provided
if (!$htmlFileId) {
    log_to_console("Error: No HTML file ID provided.");
    die("Error: No HTML file ID provided.");
}

// Fetch the file path from the database
try {
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

// Calculate the PDF file path
$pdfFilePath = preg_replace('/\.html$/', '.pdf', $htmlFilePath);

// Determine the available backup files
$backupPaths = [];
for ($i = 1; $i <= 5; $i++) {
    $backupPath = "{$htmlFilePath}.{$i}.bak";
    if (file_exists($backupPath)) {
        $backupPaths[] = $backupPath;
    } else {
        break;
    }
}

// Initialize variables to store content
$title = '';
$currentContent = '';

// Function to generate ordinal description
function get_backup_description($index) {
    switch ($index) {
        case 1:
            return "latest version";
        case 2:
            return "second latest version";
        case 3:
            return "third latest version";
        case 4:
            return "fourth latest version";
        case 5:
            return "fifth latest version";
        default:
            return "Version";
    }
}

// Handle restoring previous version
if (isset($_GET['restore']) && !empty($backupPaths)) {
    if ($currentBackupIndex <= count($backupPaths)) {
        $nextBackupPath = $backupPaths[$currentBackupIndex - 1]; // Adjust for zero-based index
        $backupContent = file_get_contents($nextBackupPath);
        if ($backupContent !== false) {
            preg_match('/<title>(.*?)<\/title>/', $backupContent, $titleMatch);
            preg_match('/<body.*?>(.*?)<\/body>/is', $backupContent, $bodyMatch);
            $title = $titleMatch[1] ?? '';
            $currentContent = $bodyMatch[1] ?? '';
            $message = "Loaded content from " . get_backup_description($currentBackupIndex);

            // Increment the backup index for the next potential restore action
            $currentBackupIndex++;
        } else {
            $message = "Failed to load the backup file.";
            $messageType = 'danger'; // Set to error message type
        }
    } else {
        $message = "No more backups available.";
        $messageType = 'info'; // Set to info message type
    }
}

// If not restoring, read the existing content of the HTML file
if (!isset($_GET['restore']) && file_exists($htmlFilePath)) {
    $existingHtml = file_get_contents($htmlFilePath);
    preg_match('/<title>(.*?)<\/title>/', $existingHtml, $titleMatch);
    preg_match('/<body.*?>(.*?)<\/body>/is', $existingHtml, $bodyMatch);
    $title = $titleMatch[1] ?? '';
    $currentContent = $bodyMatch[1] ?? '';
} else if (!file_exists($htmlFilePath)) {
    $message = "HTML file not found.";
    $messageType = 'danger'; // Set to error message type
    log_to_console("File not found: " . $htmlFilePath);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $newContent = $_POST['htmlContent'] ?? '';

    if (empty($newContent)) {
        $message = "Error: Content cannot be empty.";
        $messageType = 'danger';
        header("Location: edit.php?id=" . urlencode($htmlFileId) . "&message=" . urlencode($message));
        exit();
    }

    // Replace the <title> tag content in the HTML file
    $existingHtml = file_get_contents($htmlFilePath);
    $updatedHtml = preg_replace('/<title>(.*?)<\/title>/', '<title>' . htmlspecialchars($title) . '</title>', $existingHtml);

    // Replace the content inside the <body> tag
    $updatedHtml = preg_replace('/<body.*?>(.*?)<\/body>/is', '<body>' . $newContent . '</body>', $updatedHtml);

    // Save the updated HTML content
    if (file_put_contents($htmlFilePath, $updatedHtml)) {
        $message = "File updated successfully!";
        $messageType = 'success'; // Set to success message type
        header("Location: edit.php?id=" . urlencode($htmlFileId) . "&message=" . urlencode($message));
        exit();
    } else {
        $message = "Failed to update the file. Please check the file permissions.";
        $messageType = 'danger'; // Set to error message type
        log_to_console("File write error: Could not write to " . $htmlFilePath);
        header("Location: edit.php?id=" . urlencode($htmlFileId) . "&message=" . urlencode($message));
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Content</title>

  <!-- Favicon -->
  <link rel="icon" href="../dist/img/TrailToolLogo.png" type="image/PNG">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- SummerNote -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Sidebar -->
    <?php include 'shared/sidebar.php'; ?>
    <!-- Content Wrapper -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Content Manager</h1>
            </div>
          </div>
        </div>
      </div>

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              <!-- Card for editing HTML -->
              <div class="card card-primary">
                <div class="card-header">
                  <h3 class="card-title">Edit Content</h3>
                </div>

                <!-- Form to edit HTML content -->
                <form method="POST" action="edit_html.php?id=<?php echo htmlspecialchars($htmlFileId); ?>"
                  enctype="multipart/form-data">
                  <div class="card-body">
                    <?php if (!empty($message)) { ?>
                      <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
                    <?php } ?>
                    <div class="form-group">
                      <label for="title">Title</label>
                      <input type="text" id="title" name="title" class="form-control"
                        value="<?php echo htmlspecialchars($title); ?>">
                    </div>
                    <div class="form-group">
                      <label for="htmlContent">Body</label>
                      <textarea id="htmlContent" name="htmlContent" class="form-control"
                        rows="10"><?php echo htmlspecialchars($currentContent); ?></textarea>
                    </div>
                  </div>

                  <div class="card-footer">
                    <!-- Save Changes Button -->
                    <button type="submit" class="btn btn-primary">Save Changes</button>

                    <!-- View PDF Button -->
                    <a href="<?php echo htmlspecialchars($pdfFilePath); ?>" target="_blank"
                      class="btn btn-secondary float-right">View PDF</a>

                    <!-- Restore Previous Version Button -->
                    <a href="edit.php?id=<?php echo htmlspecialchars($htmlFileId); ?>&restore=1&backup_index=<?php echo $currentBackupIndex; ?>"
                      class="btn btn-warning float-right mr-2 <?php echo count($backupPaths) >= $currentBackupIndex ? '' : 'disabled'; ?>">
                      Restore Previous Version
                    </a>
                  </div>
                </form>

              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
      <strong>&copy; 2024 <a href="https://trailtool.org">Trail Tool</a>.</strong> All rights reserved.
      <div class="float-right d-none d-sm-inline">
        Transdisciplinary Innovative Learning
      </div>
    </footer>

  </div>

  <!-- jQuery -->
  <script src="../plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- SummerNote -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>
  <!-- AdminLTE App -->
  <script src="../dist/js/adminlte.min.js"></script>

  <!-- LogOut script -->
  <script src="../dist/js/pages/shared/logout.js"></script>

  <script>
    $(document).ready(function () {
      $('#htmlContent').summernote({
        height: 500, // Set height for the editor
        toolbar: [
          ['style', ['style']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['fontname', ['fontname']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['table', ['table']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ]
      });
    });
  </script>
</body>

</html>
