<?php 
// Ensure this is the first thing in the file
require_once 'shared/session.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Trail Tool BackOffice | Welcome</title>

  <!-- Favicon -->
  <link rel="icon" href="../dist/img/TrailToolLogo.png" type="image/PNG">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
  <?php include 'shared/sidebar.php'; ?>
  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">BackOffice</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            <!-- Introduction Card -->
            <div class="card card-primary">
              <div class="card-header">
                <h3 class="card-title">Getting Started</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <p>Welcome to the Trail Tool BackOffice. Here, you can navigate to various sections of the platform to modify and update content using our advanced editors.</p>
                <p>To begin, select the content area you wish to modify from the sidebar. Then you can:</p>
                <ul>
                  <li>Use rich text inputs for formatting text, adding links, and embedding images.</li>
                  <li>Upload and manage images that will be directly included in your HTML files.</li>
                  <li>Preview changes in real-time before committing them to the live Trail Tool interactive platform.</li>
                </ul>
                <h4>Multiple Input Support</h4>
                <p>We support a wide range of inputs, including:</p>
                <ul>
                  <li><strong>Text Inputs:</strong> Easily add and update text content.</li>
                  <li><strong>Image Uploads:</strong> Upload images that are automatically integrated into your content.</li>
                  <li><strong>Code Editors:</strong> Modify HTML, CSS, or JavaScript directly within the browser.</li>
                </ul>
                <p>Once you’ve made your changes, use the "Submit" button to save your updates. The changes will be reflected immediately on the platform.</p>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Footer -->
  <footer class="main-footer">
    <strong>&copy; 2024 <a href="https://trailtool.org">Trail Tool</a>.</strong> All rights reserved.
    <div class="float-right d-none d-sm-inline">
      Transdisciplinary Innovative Learning
    </div>
  </footer>

</div>
<!-- /.wrapper -->

<!-- jQuery -->
<script src="../plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/adminlte.min.js"></script>
<!-- Logout Script -->
<script src="../dist/js/pages/shared/logout.js"></script>
</body>
</html>
