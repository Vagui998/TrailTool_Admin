<?php
require_once 'shared/session.php';
require_once 'shared/database.php';
require_once 'tcpdf/tcpdf.php'; 

function log_error($message) {
    error_log($message);  // Log to the PHP error log
}

// Custom PDF class with footer implementation
class CustomPDF extends TCPDF {
    // Page footer
    public function Footer() {
        // Set position at 20 mm from bottom
        $this->SetY(-20);
        // Set a small font size for the footer content
        $this->SetFont('helvetica', '', 8);
        
        // Footer content using table layout
        $footerContent = '<table width="100%" style="border-top: 1px solid #ddd; font-size: 8px; padding-top: 5px;">
            <tr>
                <td width="70%" align="left">Trailtool © 2024 by Research Group: Transdisciplinary Collaboration in Education at Avan UaS.</td>
                <td width="30%" align="right">This work is licensed under a <a href="https://creativecommons.org/licenses/by-nc-nd/4.0/" target="_blank">Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License</a>.</td>
            </tr>
            <tr>
                <td colspan="2" align="right">Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . '</td>
            </tr>
        </table>';
        
        // Print the footer using HTML
        $this->writeHTML($footerContent, true, false, true, false, '');
    }
}

$db = getDbConnection();
$htmlFileId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$htmlFileId) {
    http_response_code(400);
    die("Error: Invalid or missing HTML file ID.");
}

// Fetch the file path from the database
$query = "SELECT path FROM html_files WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param("i", $htmlFileId);
$stmt->execute();
$stmt->bind_result($htmlFilePath);
$stmt->fetch();
$stmt->close();

if (!$htmlFilePath) {
    http_response_code(404);
    die("Error: No file path found for the given ID.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $newContent = $_POST['htmlContent'] ?? '';

    if (empty($newContent)) {
        $message = "Error: Content cannot be empty.";
        header("Location: edit.php?id=" . urlencode($htmlFileId) . "&message=" . urlencode($message));
        exit();
    }

    if (file_exists($htmlFilePath)) {
        $existingHtml = file_get_contents($htmlFilePath);

        // Perform rolling backups
        create_rolling_backups($htmlFilePath);

        // Check if <body> tag exists
        if (!preg_match('/<body[^>]*>.*?<\/body>/is', $existingHtml)) {
            // Rebuild the entire HTML structure if <body> is missing
            $updatedHtml = "<!DOCTYPE html>\n<html>\n<head>\n";
            if (!preg_match('/<title>.*?<\/title>/i', $existingHtml)) {
                // If <title> tag is missing, add it
                $updatedHtml .= "<title>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>\n";
            }
            $updatedHtml .= "</head>\n<body>\n" . $newContent . "\n</body>\n</html>";
        } else {
            // Update title if it's provided and the <title> tag exists or needs to be added
            if (!empty($title)) {
                if (preg_match('/<title>(.*?)<\/title>/', $existingHtml)) {
                    $updatedHtml = preg_replace('/<title>(.*?)<\/title>/', '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>', $existingHtml);
                } else {
                    $updatedHtml = preg_replace('/<head[^>]*>/', '$0<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>', $existingHtml, 1);
                }
            } else {
                $updatedHtml = preg_replace('/<title>(.*?)<\/title>/', '', $existingHtml);
            }

            // Replace the body content
            $updatedHtml = preg_replace('/<body[^>]*>.*?<\/body>/is', '<body>' . $newContent . '</body>', $updatedHtml);
        }

        // Save the updated HTML content
        if (file_put_contents($htmlFilePath, $updatedHtml) !== false) {
            generate_pdf($htmlFilePath, $updatedHtml); // Generate PDF
            $message = "File updated and PDF generated successfully!";
        } else {
            $message = "Failed to update the file.";
            log_error("Error: Could not write to $htmlFilePath");
        }

        header("Location: edit.php?id=" . urlencode($htmlFileId) . "&message=" . urlencode($message));
        exit();
    } else {
        log_error("Error: HTML file $htmlFilePath not found.");
        die("Error: HTML file not found.");
    }
} else {
    header("Location: edit_html.php?id=" . urlencode($htmlFileId));
    exit();
}

// Function to generate a PDF from the updated HTML content
function generate_pdf($filePath, $htmlContent) {
    // Resolve the directory path
    $directory = realpath(pathinfo($filePath, PATHINFO_DIRNAME));
    
    // Check if the directory exists; if not, create it
    if (!$directory) {
        if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
            log_error("Error: Failed to create directory $directory");
            die("Error: Unable to create directory for saving the PDF.");
        }
    }

    // Construct the absolute path for the PDF file
    $pdfFilePath = $directory . DIRECTORY_SEPARATOR . pathinfo($filePath, PATHINFO_FILENAME) . '.pdf';

    // Generate and save the PDF
    $pdf = new CustomPDF();
    $pdf->AddPage();

    // Ensure the font and size are explicitly set before writing the HTML
    $pdf->SetFont('helvetica', '', 12);

    // Modify HTML content for better TCPDF compatibility
    $adjustedHtmlContent = adjust_html_for_tcpdf($htmlContent);

    // Set the content for the main body
    $pdf->writeHTML($adjustedHtmlContent, true, false, true, false, '');

    // Save the PDF file
    if ($pdf->Output($pdfFilePath, 'F') === false) {
        log_error("TCPDF ERROR: Unable to create output file: $pdfFilePath");
        die("TCPDF ERROR: Unable to create output file: $pdfFilePath");
    }
}

// Function to adjust HTML content for TCPDF compatibility
function adjust_html_for_tcpdf($htmlContent) {
    // Remove nested <b> tags that could cause font size issues
    $htmlContent = preg_replace('/<b style="font-size: 2rem;">(.*?)<\/b>/', '<span style="font-size: 32px;"><strong>$1</strong></span>', $htmlContent);

    // Convert any rem sizes to explicit pixel values
    $htmlContent = str_replace('font-size: 2rem;', 'font-size: 32px;', $htmlContent);
    $htmlContent = str_replace('font-family: Roboto, sans-serif;', 'font-family: helvetica, arial, sans-serif;', $htmlContent);

    // Ensure h1 tag has the correct font size explicitly set
    $htmlContent = preg_replace('/<h1(.*?)>/', '<h1$1 style="font-size: 32px;">', $htmlContent);

    return $htmlContent;
}



// Function to create rolling backups
function create_rolling_backups($filePath) {
    // Shift existing backups down (e.g., .1.bak -> .2.bak)
    for ($i = 5; $i > 1; $i--) {
        $oldBackup = "{$filePath}." . ($i - 1) . ".bak";
        $newBackup = "{$filePath}." . $i . ".bak";
        if (file_exists($oldBackup)) {
            rename($oldBackup, $newBackup);
        }
    }

    // Create a new .1.bak from the current file
    $latestBackup = "{$filePath}.1.bak";
    copy($filePath, $latestBackup);
}
