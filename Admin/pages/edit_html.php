<?php
require_once 'shared/session.php';
require_once 'shared/database.php';
require_once 'tcpdf/tcpdf.php';

function log_error($message) {
    error_log($message);
}

class CustomPDF extends TCPDF {
    public function Footer() {
        $this->SetY(-20);
        $this->SetFont('helvetica', '', 8);
        $footerContent = '<table width="100%" style="border-top: 1px solid #ddd; font-size: 8px; padding-top: 5px;">
           <tr>
  <td width="60%" align="left">Trailtool © 2025 by Research Group: Transdisciplinary Collaboration in Education at Avans UAS.
  </td>
  
  <td width="35%" align="right">
    This work has a CC BY NC Licence
  </td>
  <td width="5%" align="center">
    <img src="https://trailtool.org/Images/creative_commons.jpg" alt="Creative Commons" style="height:20px; display:block;">

  </td>
</tr>
            <tr>
                <td colspan="2" align="right">Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . '</td>
            </tr>
        </table>';
        $this->writeHTML($footerContent, true, false, true, false, '');
    }
}

$db = getDbConnection();
$htmlFileId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$htmlFileId) {
    http_response_code(400);
    die("Error: Invalid or missing HTML file ID.");
}

// Fetch file path
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

// Build paths
$baseName = pathinfo($htmlFilePath, PATHINFO_FILENAME);
$dir = dirname($htmlFilePath);
$practicalPath = $dir . DIRECTORY_SEPARATOR . $baseName . '_practical.html';
$bibliographyPath = $dir . DIRECTORY_SEPARATOR . $baseName . '_bibliography.html';
$combinedPdfPath = $dir . DIRECTORY_SEPARATOR . $baseName . '_combined.pdf';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    log_error("POST request received with data: " . print_r($_POST, true));
    
    $title = trim($_POST['title'] ?? '');
    $mainContent = $_POST['htmlContent'] ?? '';
    $practicalContent = $_POST['practicalContent'] ?? '';
    $bibliographyContent = $_POST['bibliographyContent'] ?? '';

    log_error("Processing: title='" . $title . "', mainContent length=" . strlen($mainContent) . ", practicalContent length=" . strlen($practicalContent) . ", bibliographyContent length=" . strlen($bibliographyContent));

    // Validate main content
    if (empty($mainContent)) {
        $message = "Error: Main content cannot be empty.";
        header("Location: edit.php?id=" . urlencode($htmlFileId) . "&message=" . urlencode($message));
        exit();
    }

    // CREATE BACKUPS FOR ALL FILES FIRST (before any modifications)
    // This ensures all backups are synchronized
    $allPaths = [$htmlFilePath, $practicalPath, $bibliographyPath];
    foreach ($allPaths as $path) {
        if (file_exists($path)) {
            create_rolling_backups($path);
        }
    }

    $filesToUpdate = [
        ['path' => $htmlFilePath, 'content' => $mainContent, 'title' => $title, 'section' => 'Introduction'],
        ['path' => $practicalPath, 'content' => $practicalContent, 'title' => null, 'section' => 'Theory'],
        ['path' => $bibliographyPath, 'content' => $bibliographyContent, 'title' => null, 'section' => 'Practice']
    ];

    foreach ($filesToUpdate as $file) {
        $path = $file['path'];
        $content = $file['content'];
        $title = $file['title'];
        $section = $file['section'];

        // For practical and bibliography, if content is empty, create an empty HTML file
        if (($content === null || $content === '') && $title === null) {
            // Create an empty but valid HTML file to keep backups synchronized
            $emptyHtml = "<!DOCTYPE html>\n<html>\n<head>\n<title>" . htmlspecialchars($section, ENT_QUOTES, 'UTF-8') . "</title>\n</head>\n<body>\n</body>\n</html>";
            file_put_contents($path, $emptyHtml);
            log_error("Created empty HTML file for: " . $path);
            continue;
        }

        // Main content cannot be empty (already validated above)
        if ($content === null || $content === '') {
            continue;
        }

        $existingHtml = file_exists($path)
            ? file_get_contents($path)
            : "<!DOCTYPE html>\n<html>\n<head>\n<title>" . htmlspecialchars($title ?? $section, ENT_QUOTES, 'UTF-8') . "</title>\n</head>\n<body>\n</body>\n</html>";

        if (!preg_match('/<body[^>]*>.*?<\/body>/is', $existingHtml)) {
            // Build complete HTML structure
            $updatedHtml = "<!DOCTYPE html>\n<html>\n<head>\n";
            if ($title !== null) {
                $updatedHtml .= "<title>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>\n";
            } else {
                $updatedHtml .= "<title>" . htmlspecialchars($section, ENT_QUOTES, 'UTF-8') . "</title>\n";
            }
            $updatedHtml .= "</head>\n<body>\n" . $content . "\n</body>\n</html>";
        } else {
            $updatedHtml = $existingHtml;
            
            // Update title only for main file
            if ($title !== null) {
                if (preg_match('/<title>(.*?)<\/title>/', $existingHtml)) {
                    $updatedHtml = preg_replace('/<title>(.*?)<\/title>/', '<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>', $updatedHtml);
                } else {
                    $updatedHtml = preg_replace('/<head[^>]*>/', '$0<title>' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</title>', $updatedHtml, 1);
                }
            }

            // Always update body content
            $updatedHtml = preg_replace('/<body[^>]*>.*?<\/body>/is', '<body>' . $content . '</body>', $updatedHtml);
        }

        // Write the updated HTML
        $writeResult = file_put_contents($path, $updatedHtml);
        if ($writeResult === false) {
            log_error("Failed to write to file: " . $path);
        } else {
            log_error("Successfully wrote " . $writeResult . " bytes to: " . $path);
        }
    }

    // Combine all updated HTML contents WITHOUT forced page breaks
    $combinedHtml = '';
    $sectionTitles = ['Introduction', 'Theory', 'Practice'];
    $files = [$htmlFilePath, $practicalPath, $bibliographyPath];
    
    for ($i = 0; $i < count($files); $i++) {
        $file = $files[$i];
        if (file_exists($file)) {
            $html = file_get_contents($file);
            $bodyContent = extract_body($html);
            if (!empty(trim($bodyContent))) {
                // Add section header
                $combinedHtml .= '<h2>' . $sectionTitles[$i] . '</h2>' . $bodyContent;
                // No page breaks - let content flow naturally
            }
        }
    }

    // Generate combined PDF
    $pdfResult = generate_combined_pdf($combinedPdfPath, $combinedHtml);
    
    if ($pdfResult) {
        $message = "Files saved and combined PDF generated successfully!";
    } else {
        $message = "Files saved but PDF generation failed. Check error logs.";
    }
    
    header("Location: edit.php?id=" . urlencode($htmlFileId) . "&message=" . urlencode($message));
    exit();
}

// Load contents for form
$mainContent = file_exists($htmlFilePath) ? file_get_contents($htmlFilePath) : '';
$practicalContent = file_exists($practicalPath) ? file_get_contents($practicalPath) : '';
$bibliographyContent = file_exists($bibliographyPath) ? file_get_contents($bibliographyPath) : '';

function extract_body($html) {
    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
        return $matches[1];
    }
    return $html;
}

// Only show the simple HTML form if this is a GET request (for backward compatibility)
if ($_SERVER['REQUEST_METHOD'] !== 'POST'):
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit HTML Files</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>Edit HTML Sections</h1>
    <form method="post">
        <label>Title (main only):</label><br>
        <input type="text" name="title" style="width: 100%;"><br><br>

        <label>Introduction:</label><br>
        <textarea name="htmlContent" rows="10" style="width:100%;"><?= htmlspecialchars(extract_body($mainContent)) ?></textarea><br><br>

        <label>Theory:</label><br>
        <textarea name="practicalContent" rows="10" style="width:100%;"><?= htmlspecialchars(extract_body($practicalContent)) ?></textarea><br><br>

        <label>Practice:</label><br>
        <textarea name="bibliographyContent" rows="10" style="width:100%;"><?= htmlspecialchars(extract_body($bibliographyContent)) ?></textarea><br><br>

        <input type="submit" value="Save All">
    </form>

    <?php if (file_exists($combinedPdfPath)): ?>
        <?php 
          // Build URL path relative to Admin folder (docs is inside Admin)
          // Get the path from DB and make it relative to the Admin directory
          $relativePath = str_replace('\\', '/', $htmlFilePath);
          
          // Remove any leading path separators and ensure it starts with docs/
          if (strpos($relativePath, 'docs/') !== false) {
              $relativePath = substr($relativePath, strpos($relativePath, 'docs/'));
          }
          
          // Build PDF path by replacing .html with _combined.pdf
          $pdfRelativePath = str_replace('.html', '_combined.pdf', $relativePath);
          
          // Since docs is inside Admin, we just need the relative path from current location
          // We're in Admin/pages/ so we need to go up one level to Admin/, then to docs/
          $pdfUrl = '../' . $pdfRelativePath;
        ?>
        <p><a href="<?= htmlspecialchars($pdfUrl) ?>" target="_blank">View Combined PDF</a></p>
    <?php endif; ?>
</body>
</html>

<?php
endif; // End of HTML output for GET requests

function generate_combined_pdf($filePath, $combinedHtml) {
    try {
        // Normalize and validate the file path
        $cleanPath = normalize_file_path($filePath);
        
        // Resolve the directory path
        $directory = realpath(dirname($cleanPath));
        
        // If realpath fails, try to create the directory structure
        if (!$directory) {
            $directory = dirname($cleanPath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
                    log_error("Error: Failed to create directory $directory");
                    return false;
                }
            }
            $directory = realpath($directory);
        }
        
        // Check if directory is writable
        if (!$directory || !is_writable($directory)) {
            log_error("Error: Directory is not writable: $directory");
            return false;
        }
        
        // Construct the final PDF path
        $finalPdfPath = $directory . DIRECTORY_SEPARATOR . basename($cleanPath);
        
        // Generate the PDF
        $pdf = new CustomPDF();
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('dejavusans', '', 12);

        // ↓ tighter line spacing inside paragraphs
        $pdf->setCellHeightRatio(1.15);

        // Set margins
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 25);

        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 12);
        
        // Adjust HTML content for TCPDF compatibility
        $html = adjust_html_for_tcpdf($combinedHtml);
        
        // Check if there's actually content to render
        $strippedHtml = strip_tags($html);
        $strippedHtml = preg_replace('/\s+/', '', $strippedHtml);
        
        if (empty($strippedHtml)) {
            log_error("Warning: No renderable content found in HTML");
            $html = '<p>No content available.</p>';
        }
        
        // Write HTML content to PDF - disable automatic line break adjustment
        // Remove automatic paragraph indentation globally
        // Disable paragraph indentation & control line spacing
        $pdf->setFontSubsetting(true);
        $pdf->setCellPadding(0);
        
    // Clean up any non-breaking spaces or invisible characters that create ghost indents
    $html = preg_replace('/\xC2\xA0|\&nbsp;| /u', ' ', $html); // replaces all variants with normal spaces
    $html = preg_replace('/\s{2,}/', ' ', $html);              // collapse any double spaces
    $html = preg_replace('/^\s+/m', '', $html);                // remove leading spaces on each line



        // Write HTML to PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        
        
        // Output the PDF to file
        $result = $pdf->Output($finalPdfPath, 'F');
        
        if ($result === false) {
            log_error("TCPDF ERROR: Unable to create output file: $finalPdfPath");
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        log_error("PDF Generation Error: " . $e->getMessage());
        return false;
    }
}

function normalize_file_path($filePath) {
    // Remove any file:// protocol prefix
    $filePath = preg_replace('/^file:\/\//', '', $filePath);
    
    // Normalize path separators
    $filePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $filePath);
    
    // Clean up any double separators
    $filePath = preg_replace('/[' . preg_quote(DIRECTORY_SEPARATOR, '/') . ']+/', DIRECTORY_SEPARATOR, $filePath);
    
    return $filePath;
}

function adjust_html_for_tcpdf($htmlContent) {

    // --- A) Normalize sizes coming from editors --------------------------------
    // rem -> px (TCPDF treats px ~ pt; we’ll use CSS classes anyway)
    $htmlContent = preg_replace_callback(
        '/font-size:\s*([0-9]+(?:\.[0-9]+)?)rem\s*;?/i',
        function($m){ return 'font-size:' . ($m[1] * 16) . 'px;'; },
        $htmlContent
    );

    // Kill inline line-height that Word/Summernote inject (prevents weird spacing)
    $htmlContent = preg_replace('/\s*line-height\s*:\s*[\d.]+(?:px|pt|em|rem)\s*;?/i', '', $htmlContent);

    // Normalize &nbsp;/zero-width/multiple spaces
    $htmlContent = preg_replace('/(&nbsp;|\xC2\xA0)+/i', ' ', $htmlContent);
    $htmlContent = preg_replace('/\s{2,}/', ' ', $htmlContent);
    $htmlContent = preg_replace('/<\/span>\s+<span[^>]*>/', '</span> <span>', $htmlContent);

    // Remove empty <p> (including those with only &nbsp;)
    $htmlContent = preg_replace('/<p[^>]*>\s*(?:&nbsp;|\xC2\xA0|\s)*<\/p>/i', '', $htmlContent);


    // --- B) CRITICAL FIX: don’t let <p>/<li> carry a font-size inline ----------
    // TCPDF can ignore nested child sizes if parent block has inline font-size.
    // Strip font-size from <p> and <li> style attributes only.
    $htmlContent = preg_replace_callback(
        '/<(p|li)\b([^>]*)style="([^"]*)"/i',
        function($m) {
            $tag = $m[1];
            $attrs = $m[2];
            $style = $m[3];
            // remove any font-size: ...; from the style
            $style = preg_replace('/\bfont-size\s*:\s*[^;"]+;?/i', '', $style);
            // collapse leftover semicolons/whitespace
            $style = trim(preg_replace('/\s*;\s*;+/',';',$style));
            if ($style === '' || $style === ';') {
                // drop style attribute entirely if empty
                return '<'.$tag.$attrs.'>';
            }
            return '<'.$tag.$attrs.' style="'.$style.'">';
        },
        $htmlContent
    );


    // --- C) Map span inline font-sizes to CSS classes with !important ----------
    // We’ll support the common sizes you use (12 and 16). Add more if needed.
    // Convert: <span style="font-size:12px"> → <span class="fs12">
    //          <span style="font-size:16px"> → <span class="fs16">
    $map = [
        '10' => 'fs10',
        '11' => 'fs11',
        '12' => 'fs12',
        '13' => 'fs13',
        '14' => 'fs14',
        '15' => 'fs15',
        '16' => 'fs16',
        '17' => 'fs17',
        '18' => 'fs18',
    ];
    $htmlContent = preg_replace_callback(
        '/<span\b([^>]*)style="([^"]*?)"([^>]*)>/i',
        function($m) use ($map) {
            $before = $m[1];
            $style  = $m[2];
            $after  = $m[3];

            if (preg_match('/font-size\s*:\s*([0-9]+)(?:\.[0-9]+)?px/i', $style, $mm)) {
                $size = $mm[1];
                if (isset($map[$size])) {
                    // remove font-size from style
                    $style = preg_replace('/\bfont-size\s*:\s*[^;"]+;?/i', '', $style);
                    $style = trim(preg_replace('/\s*;\s*;+/',';',$style));
                    // attach/merge class
                    $classAdd = $map[$size];
                    // Check if span already has class=
                    if (preg_match('/\bclass="([^"]*)"/i', $before.$after, $cm)) {
                        // merge classes
                        $existing = $cm[1];
                        $merged = trim($existing.' '.$classAdd);
                        // replace the existing class attr
                        $before = preg_replace('/\bclass="[^"]*"/i', 'class="'.htmlspecialchars($merged,ENT_QUOTES,'UTF-8').'"', $before);
                    } else {
                        $before .= ' class="'.$classAdd.'"';
                    }
                    // rebuild style attribute only if something left
                    if ($style === '' || $style === ';') {
                        // drop style completely
                        return '<span'.$before.$after.'>';
                    } else {
                        return '<span'.$before.' style="'.$style.'"'.$after.'>';
                    }
                }
            }
            // no change
            return '<span'.$before.' style="'.$style.'"'.$after.'>';
        },
        $htmlContent
    );


    // --- D) Lists tidy (optional, keeps your earlier improvements) -------------
    // Flatten <li><p>…</p></li> to avoid extra spacing
    $htmlContent = preg_replace('/<li([^>]*)>\s*<p[^>]*>(.*?)<\/p>\s*<\/li>/is', '<li$1>$2</li>', $htmlContent);

    // If you had <li><p>Title<br>Body</p> → split title/body (keep if helpful)
    $htmlContent = preg_replace(
        '/<li([^>]*)>\s*<p[^>]*>(.*?)<br\s*\/?>\s*(.*?)<\/p>\s*<\/li>/is',
        '<li$1><div class="li-title">$2</div><div class="li-body">$3</div></li>',
        $htmlContent
    );

    // --- D.1) Remove stray '>' or '&gt;' that appear as text prefixes -------------
$htmlContent = preg_replace(
    '/(<(?:p|li)[^>]*>)\s*(?:&gt;|>)\s*/i',
    '$1',
    $htmlContent
);

// If a line contains ONLY a lone '>' (or '&gt;'), drop that line completely
$htmlContent = preg_replace('/^\s*(?:&gt;|>)\s*$/m', '', $htmlContent);



    // --- E) Minimal CSS that TCPDF will honor ---------------------------------
    // Default paragraph/LI size is 16; smaller text uses classes with !important.
    $styleHeader = '<style>
        /* Base text + defaults */
        body, p, span, li, td { font-family: DejaVu Sans, sans-serif; }
        p, li { font-size: 16px; margin: 0 0 8px 0; text-indent: 0; padding: 0; }

        /* Lists */
        ul, ol { margin: 0 0 6px 22px; padding: 0; }          /* left indent & tiny gap after a whole list */
        ul li, ol li { margin: 0; padding: 0 0 100px 0; line-height: 1.5; }  /* space between bullets via padding */
        ul li:last-child, ol li:last-child { padding-bottom: 0; }            /* no extra space after final item */
        li p, li div { margin: 0; padding: 0; line-height: inherit; }        /* kill nested block margins */


        a { color: #0066cc; text-decoration: underline; }

        /* Titles in list split */
        .li-title { font-weight: bold; margin: 0 0 4px 0; }
        .li-body  { margin: 0; }

        /* Font-size helpers (child must win vs parent) */
        .fs10 { font-size: 10px !important; }
        .fs11 { font-size: 11px !important; }
        .fs12 { font-size: 12px !important; }
        .fs13 { font-size: 13px !important; }
        .fs14 { font-size: 14px !important; }
        .fs15 { font-size: 15px !important; }
        .fs16 { font-size: 16px !important; }
        .fs17 { font-size: 17px !important; }
        .fs18 { font-size: 18px !important; }
</style>';




  

    // 6) VIDEO — Replace <iframe> with a clickable thumbnail (image is the link)
    $htmlContent = preg_replace_callback(
        '/<iframe[^>]*src=["\']([^"\']+)["\'][^>]*?(?:><\/iframe>|\/>)/is',
        function ($m) {
            $src = htmlspecialchars($m[1]);
            // YouTube
            if (preg_match('/(?:youtube\.com\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]+)/', $src, $id)) {
                $videoId  = $id[1];
                $thumb    = 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg';
                $watchUrl = 'https://www.youtube.com/watch?v=' . $videoId;
                return '
                <div style="margin:12px 0; text-align:center;">
                  <a href="' . $watchUrl . '" target="_blank">
                    <img src="' . $thumb . '" alt="Video" title="Open video"
                         style="width:520px; height:auto; border:1px solid #bbb; border-radius:6px; display:block; margin:0 auto;">
                  </a>
                </div>';
            }
            // Fallback: just a link if not YouTube
            return '
            <div style="margin:12px 0; text-align:center;">
              <a href="' . $src . '" target="_blank">Open video</a>
            </div>';
        },
        $htmlContent
    );

    // 7) <video src="..."> fallback → simple notice
    $htmlContent = preg_replace_callback(
        '/<video[^>]*src=["\']([^"\']+)["\'][^>]*>(.*?)<\/video>/is',
        function ($m) {
            $src = basename($m[1]);
            return '<div style="border:1px solid #ddd; padding:10px; text-align:center; margin:12px 0;">
                        <strong>Video:</strong> ' . htmlspecialchars($src) . '<br>
                        <em>(Video content is not supported in PDF)</em>
                    </div>';
        },
        $htmlContent
    );


    // Remove empty paragraphs (including &nbsp; / non-breaking spaces)
    $htmlContent = preg_replace(
    '/<p[^>]*>\s*(?:&nbsp;|\xC2\xA0|\s)*<\/p>/i',
    '',
    $htmlContent
    );


// --- F) Final gentle clean -----------------------------------------------
    $htmlContent = preg_replace('/^\s+/m', '', $htmlContent);
    return $styleHeader . $htmlContent;
}



function create_rolling_backups($filePath) {
    // Only create backups if the file exists
    if (!file_exists($filePath)) {
        log_error("Cannot create backup: file does not exist - " . $filePath);
        return;
    }
    
    log_error("Creating backup for: " . $filePath);
    
    // Roll existing backups
    for ($i = 5; $i > 1; $i--) {
        $old = "{$filePath}." . ($i - 1) . ".bak";
        $new = "{$filePath}." . $i . ".bak";
        if (file_exists($old)) {
            rename($old, $new);
        }
    }
    
    // Create new backup
    $backupResult = copy($filePath, "{$filePath}.1.bak");
    if ($backupResult) {
        log_error("Backup created successfully: {$filePath}.1.bak");
    } else {
        log_error("Failed to create backup: {$filePath}.1.bak");
    }
}
?>