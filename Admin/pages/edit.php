<?php
require_once 'shared/session.php';
require_once 'shared/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function log_to_console($data) {
    echo "<script>console.log('PHP: " . addslashes(json_encode($data)) . "');</script>";
}

$message = '';
$messageType = 'info';

if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
    if (strpos($message, 'Error:') !== false) {
        $messageType = 'danger';
    } else {
        $messageType = 'success';
    }
}

$db = getDbConnection();
$htmlFileId = $_GET['id'] ?? null;
$currentBackupIndex = isset($_GET['backup_index']) ? (int) $_GET['backup_index'] : 1;

if (!$htmlFileId) {
    log_to_console("Error: No HTML file ID provided.");
    die("Error: No HTML file ID provided.");
}

$query = "SELECT path FROM html_files WHERE id = ?";
$stmt = $db->prepare($query);
if (!$stmt) {
    log_to_console("Database prepare error: " . $db->error);
    die("Database error: " . $db->error);
}
$stmt->bind_param("i", $htmlFileId);
$stmt->execute();
$stmt->bind_result($htmlFilePath);
$stmt->fetch();
$stmt->close();

if (!$htmlFilePath) {
    log_to_console("Error: No file path found for the given ID.");
    die("Error: No file path found for the given ID.");
}

$baseName = pathinfo($htmlFilePath, PATHINFO_FILENAME);
$dir = dirname($htmlFilePath);
$practicalPath = $dir . DIRECTORY_SEPARATOR . $baseName . '_practical.html';
$bibliographyPath = $dir . DIRECTORY_SEPARATOR . $baseName . '_bibliography.html';
$pdfFilePath = $dir . DIRECTORY_SEPARATOR . $baseName . '_combined.pdf';

$backupPaths = [];
for ($i = 1; $i <= 5; $i++) {
    $backupPath = "{$htmlFilePath}.{$i}.bak";
    if (file_exists($backupPath)) {
        $backupPaths[] = $backupPath;
    } else {
        break;
    }
}

$title = '';
$mainContent = '';
$practicalContent = '';
$bibliographyContent = '';

function get_backup_description($index) {
    switch ($index) {
        case 1: return "latest backup";
        case 2: return "second latest backup";
        case 3: return "third latest backup";
        case 4: return "fourth latest backup";
        case 5: return "fifth latest backup";
        default: return "backup version";
    }
}

function extract_body($html) {
    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
        return $matches[1];
    }
    return $html;
}

function extract_title($html) {
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
        return $matches[1];
    }
    return '';
}

if (isset($_GET['restore']) && !empty($backupPaths)) {
    if ($currentBackupIndex <= count($backupPaths)) {
        $backupContent = file_get_contents($backupPaths[$currentBackupIndex - 1]);
        if ($backupContent !== false) {
            $title = extract_title($backupContent);
            $mainContent = extract_body($backupContent);
            
            $practicalBackupPath = str_replace('.html.', '_practical.html.', $backupPaths[$currentBackupIndex - 1]);
            $bibliographyBackupPath = str_replace('.html.', '_bibliography.html.', $backupPaths[$currentBackupIndex - 1]);
            
            if (file_exists($practicalBackupPath)) {
                $practicalBackupContent = file_get_contents($practicalBackupPath);
                $practicalContent = extract_body($practicalBackupContent);
            }
            
            if (file_exists($bibliographyBackupPath)) {
                $bibliographyBackupContent = file_get_contents($bibliographyBackupPath);
                $bibliographyContent = extract_body($bibliographyBackupContent);
            }
            
            $message = "Loaded content from " . get_backup_description($currentBackupIndex);
            $messageType = 'success';
            $currentBackupIndex++;
        } else {
            $message = "Failed to load the backup file.";
            $messageType = 'danger';
        }
    } else {
        $message = "No more backups available.";
        $messageType = 'warning';
    }
} else {
    if (file_exists($htmlFilePath)) {
        $existingHtml = file_get_contents($htmlFilePath);
        $title = extract_title($existingHtml);
        $mainContent = extract_body($existingHtml);
    } else {
        $message = "Main HTML file not found.";
        $messageType = 'danger';
        log_to_console("File not found: " . $htmlFilePath);
    }

    if (file_exists($practicalPath)) {
        $practicalHtml = file_get_contents($practicalPath);
        $practicalContent = extract_body($practicalHtml);
    }

    if (file_exists($bibliographyPath)) {
        $bibliographyHtml = file_get_contents($bibliographyPath);
        $bibliographyContent = extract_body($bibliographyHtml);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Location: edit_html.php?id=" . urlencode($htmlFileId));
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Edit Content</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro">
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.css">
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
  <link rel="icon" href="../dist/img/TrailToolLogo.png" type="image/PNG">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <?php include 'shared/sidebar.php'; ?>

    <div class="content-wrapper">
      <div class="content-header">
        <div class="container-fluid">
          <h1 class="m-0">Content Manager</h1>
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <div class="card card-primary">
            <div class="card-header">
              <h3 class="card-title">Edit HTML Sections</h3>
            </div>

            <form method="POST" action="edit_html.php?id=<?php echo htmlspecialchars($htmlFileId); ?>">
              <div class="card-body">
                <?php if (!empty($message)) : ?>
                  <div class="alert alert-<?php echo $messageType; ?> alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <?php echo htmlspecialchars($message); ?>
                  </div>
                <?php endif; ?>

                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>">
                </div>

                <div class="form-group">
                  <label>Introduction</label>
                  <textarea id="mainEditor" name="htmlContent" class="form-control" rows="10"><?php echo htmlspecialchars($mainContent); ?></textarea>
                </div>

                <div class="form-group">
                  <label>Theory</label>
                  <textarea id="practicalEditor" name="practicalContent" class="form-control" rows="10"><?php echo htmlspecialchars($practicalContent); ?></textarea>
                </div>

                <div class="form-group">
                  <label>Practice</label>
                  <textarea id="bibliographyEditor" name="bibliographyContent" class="form-control" rows="10"><?php echo htmlspecialchars($bibliographyContent); ?></textarea>
                </div>
              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save"></i> Save All Changes
                </button>

                <?php if (file_exists($pdfFilePath)): ?>
                  <?php 
                    $relativePath = str_replace('\\', '/', $htmlFilePath);
                    
                    if (strpos($relativePath, 'docs/') !== false) {
                        $relativePath = substr($relativePath, strpos($relativePath, 'docs/'));
                    }
                    
                    $pdfRelativePath = str_replace('.html', '_combined.pdf', $relativePath);
                    $pdfUrl = '../' . $pdfRelativePath;
                  ?>
                  <a href="<?php echo htmlspecialchars($pdfUrl); ?>" target="_blank" class="btn btn-secondary float-right">
                    <i class="fas fa-file-pdf"></i> View Combined PDF
                  </a>
                <?php else: ?>
                  <span class="btn btn-secondary float-right disabled">
                    <i class="fas fa-file-pdf"></i> PDF Not Available
                  </span>
                <?php endif; ?>

                <?php if (!empty($backupPaths) && $currentBackupIndex <= count($backupPaths)): ?>
                  <a href="edit.php?id=<?php echo htmlspecialchars($htmlFileId); ?>&restore=1&backup_index=<?php echo $currentBackupIndex; ?>"
                    class="btn btn-warning float-right mr-2">
                    <i class="fas fa-undo"></i> Restore Previous Version (<?php echo get_backup_description($currentBackupIndex); ?>)
                  </a>
                <?php else: ?>
                  <span class="btn btn-warning float-right mr-2 disabled">
                    <i class="fas fa-undo"></i> No More Backups
                  </span>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
      </section>
    </div>

    <footer class="main-footer">
      <strong>&copy; 2024 <a href="#">Trail Tool</a>.</strong>
      All rights reserved.
      <div class="float-right d-none d-sm-inline">Transdisciplinary Innovative Learning</div>
    </footer>
  </div>

  <script src="../plugins/jquery/jquery.min.js"></script>
  <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote-bs4.min.js"></script>
  <script src="../plugins/list/summernote-list-space.js"></script>
  <script src="../dist/js/adminlte.min.js"></script>
  <script src="../dist/js/pages/shared/logout.js"></script>

  <script>
    $(document).ready(function() {
      $('#mainEditor, #practicalEditor, #bibliographyEditor').summernote({
        height: 300,
        fontSizes: ['8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '20', '22', '24', '26', '28', '30', '32', '36', '40', '44', '48', '54', '60', '66', '72'],
        fontNames: ['Arial', 'Times New Roman', 'Verdana', 'Georgia', 'Courier New', 'Trebuchet MS', 'Comic Sans MS', 'Impact'],
        toolbar: [
          ['fontname', ['fontname']],
          ['font', ['bold', 'italic', 'underline', 'clear']],
          ['fontsize', ['fontsize']],
          ['height', ['height']],
          ['lists', ['listSpace']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['insert', ['link', 'picture', 'video']],
          ['mybutton', ['cleaner']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
        buttons: {
            cleaner: function(context) {
            const ui = $.summernote.ui;
            const button = ui.button({
              contents: '<i class="fas fa-broom"></i>',
              tooltip: 'Clean HTML',
              click: function() {
                const $editor = context.layoutInfo.editable;
                let html = $editor.html();
                
                // Remove class attributes
                html = html.replace(/\s+class="[^"]*"/gi, '');
                
                // Remove data- attributes
                html = html.replace(/\s+data-[^=]*="[^"]*"/gi, '');
                
                // Remove xml:lang (but keep lang)
                html = html.replace(/\s+xml:lang="[^"]*"/gi, '');
                
                // Remove paraid and paraeid attributes
                html = html.replace(/\s+(paraid|paraeid)="[^"]*"/gi, '');
                
                // Clean style attributes - keep margin, font-family, font-size, font-weight, font-style, text-decoration, color, line-height
                // Also convert pt to px for font-size
                html = html.replace(/style="([^"]*)"/gi, function(match, styles) {
                  const keepStyles = [];
                  const styleArray = styles.split(';');
                  
                  styleArray.forEach(function(style) {
                    let trimmed = style.trim();
                    if (trimmed) {
                      // Convert font-size from pt to px
                      if (trimmed.indexOf('font-size') === 0) {
                        const sizeMatch = trimmed.match(/font-size:\s*(\d+(?:\.\d+)?)pt/i);
                        if (sizeMatch) {
                          const ptValue = parseFloat(sizeMatch[1]);
                          trimmed = 'font-size: ' + ptValue + 'px';
                        }
                      }
                      
                      // Keep only the styles we want
                     // Keep only typographic styles; drop margin/line-height so we control spacing later
                              if (
              trimmed.indexOf('font-family') === 0 ||
              trimmed.indexOf('font-size') === 0 ||
              trimmed.indexOf('font-weight') === 0 ||
              trimmed.indexOf('font-style') === 0 ||
              trimmed.indexOf('text-decoration') === 0 ||
              trimmed.indexOf('color') === 0 ||
              trimmed.indexOf('line-height') === 0 ||
              trimmed.indexOf('margin-bottom') === 0
            ) {
              keepStyles.push(trimmed);
            }

                    // NOTE: intentionally NOT keeping margin or line-height
                    }
                  });
                  
                  if (keepStyles.length > 0) {
                    return 'style="' + keepStyles.join('; ') + '"';
                  }
                  return '';
                });
                
                // Remove MSO styles
                html = html.replace(/mso-[^:;]+(:[^;"]+)?;?/gi, '');
                
                // Remove empty style attributes
                html = html.replace(/\s+style=""\s*/gi, ' ');
                
                // Remove style|script|meta|link|title|head tags
                html = html.replace(/<(style|script|meta|link|title|head)[^>]*>.*?<\/\1>/gi, '');
                
                // === B. DOM cleanup for spans/nbsp before injecting back ===
              (function() {
                // Work in a detached DOM
                var $tmp = $('<div>').html(html);

                // 1) Unwrap meaningless <span> wrappers (Word loves these)
                $tmp.find('span').each(function () {
                  var attrs = this.getAttributeNames ? this.getAttributeNames() : [];
                  var style = (this.getAttribute && this.getAttribute('style') || '').trim();

                  // unwrap if: no attributes, or only empty style=""
                  if (attrs.length === 0 || (attrs.length === 1 && attrs[0] === 'style' && !style)) {
                    $(this).replaceWith($(this).contents());
                  }
                });

                // 2) Normalize whitespace in block elements
                $tmp.find('p, li, h1, h2, h3, h4, h5, h6').each(function () {
                  var htmlText = $(this).html();

                  // convert &nbsp; to normal spaces
                  htmlText = htmlText.replace(/\u00a0/g, ' ');

                  // trim leading/trailing spaces
                  htmlText = htmlText.replace(/^\s+/, '').replace(/\s+$/, '');

                  // collapse runs of spaces
                  htmlText = htmlText.replace(/\s{2,}/g, ' ');

                  $(this).html(htmlText);
                });

                // 3) If a <p> starts with stray spaces after tags, trim them
                html = $tmp.html()
                  .replace(/(<p[^>]*>)\s+/gi, '$1')
                  .replace(/(<li[^>]*>)\s+/gi, '$1');
              })();


                $editor.html(html);
                context.invoke('editor.saveRange');
                $(context.layoutInfo.note).summernote('triggerEvent', 'change', html);
              }
            });
            return button.render();
          }
        }
      });
      

      setTimeout(function() {
        $('.alert').fadeOut('slow');
      }, 5000);
    });
  </script>
</body>
</html>