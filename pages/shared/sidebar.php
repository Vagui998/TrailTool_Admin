<?php
require_once 'session.php';

// Get the current page URL and the ID parameter
$current_page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ".php");
$current_id = $_GET['id'] ?? null;

// Define ID ranges for English and Dutch sections
$english_ids = range(1, 70); // Adjust as necessary for English items
$dutch_ids = range(71, 140); // Adjust as necessary for Dutch items

// Determine if the current ID is within the English or Dutch sections
$english_open = in_array($current_id, $english_ids);
$dutch_open = in_array($current_id, $dutch_ids);

// Define subsections and final levels based on IDs for both languages
$sections = [
  'on_boarding' => [
    'english' => [
      'main' => [1],
      'objects' => [2 => 'Campfire Description', 3 => 'Torch Description', 4 => 'Marker Description'],
      'information' => [5 => 'Header', 6 => 'Knowledge', 7 => 'Practical Tools', 8 => 'Examples', 9 => 'Campfire Sessions']
    ],
    'dutch' => [
      'main' => [71],
      'objects' => [72 => 'Campfire Description', 73 => 'Torch Description', 74 => 'Marker Description'],
      'information' => [75 => 'Header', 76 => 'Knowledge', 77 => 'Practical Tools', 78 => 'Examples', 79 => 'Campfire Sessions']
    ]
  ],
  'individual_trails' => [
    'english' => [
      'student' => [10 => '1st Torch', 11 => '2nd Torch', 12 => '3rd Torch', 13 => '4th Torch', 14 => '5th Torch'],
      'teacher' => [15 => '1st Torch', 16 => '2nd Torch', 17 => '3rd Torch', 18 => '4th Torch', 19 => '5th Torch'],
      'researcher' => [20 => '1st Torch', 21 => '2nd Torch', 22 => '3rd Torch', 23 => '4th Torch', 24 => '5th Torch'],
      'partner' => [25 => '1st Torch', 26 => '2nd Torch', 27 => '3rd Torch', 28 => '4th Torch', 29 => '5th Torch']
    ],
    'dutch' => [
      'student' => [80 => '1st Torch', 81 => '2nd Torch', 82 => '3rd Torch', 83 => '4th Torch', 84 => '5th Torch'],
      'teacher' => [85 => '1st Torch', 86 => '2nd Torch', 87 => '3rd Torch', 88 => '4th Torch', 89 => '5th Torch'],
      'researcher' => [90 => '1st Torch', 91 => '2nd Torch', 92 => '3rd Torch', 93 => '4th Torch', 94 => '5th Torch'],
      'partner' => [95 => '1st Torch', 96 => '2nd Torch', 97 => '3rd Torch', 98 => '4th Torch', 99 => '5th Torch']
    ]
  ],
  'main_trail' => [
    'english' => [
      'design' => [30 => '1st Torch', 31 => '2nd Torch', 32 => '3rd Torch', 33 => '4th Torch', 34 => '5th Torch', 35 => '6th Torch', 36 => '7th Torch', 37 => '8th Torch'],
      'participation' => [38 => '1st Torch', 39 => '2nd Torch', 40 => '3rd Torch', 41 => '4th Torch', 42 => '5th Torch'],
      'summit' => [43 => '1st Torch', 44 => '2nd Torch', 45 => '3rd Torch', 46 => '4th Torch'],
      'reflection' => [47 => '1st Torch', 48 => '2nd Torch', 49 => '3rd Torch']
    ],
    'dutch' => [
      'design' => [100 => '1st Torch', 101 => '2nd Torch', 102 => '3rd Torch', 103 => '4th Torch', 104 => '5th Torch', 105 => '6th Torch', 106 => '7th Torch', 107 => '8th Torch'],
      'participation' => [108 => '1st Torch', 109 => '2nd Torch', 110 => '3rd Torch', 111 => '4th Torch', 112 => '5th Torch'],
      'summit' => [113 => '1st Torch', 114 => '2nd Torch', 115 => '3rd Torch', 116 => '4th Torch'],
      'reflection' => [117 => '1st Torch', 118 => '2nd Torch', 119 => '3rd Torch']
    ]
  ],
  'camps' => [
    'english' => [
      'base' => [50 => 'Welcome', 51 => 'Student Introduction', 52 => 'Teacher Introduction', 53 => 'Researcher Introduction', 54 => 'Partner Introduction', 55 => 'Networking', 56 => 'Fertile Ground', 57 => 'Silent Voices', 58 => 'Common Vision', 59 => 'Findings', 60 => 'Design Trail Info', 61 => 'Participation Trail Info'],
      'lower' => [62 => 'Welcome', 63 => 'Information'],
      'upper' => [64 => 'Welcome', 65 => 'Information'],
      'summit' => [66 => 'Welcome', 67 => 'Information'],
      'reflection' => [68 => 'Welcome', 69 => 'Information']
    ],
    'dutch' => [
      'base' => [120 => 'Welcome', 121 => 'Student Introduction', 122 => 'Teacher Introduction', 123 => 'Researcher Introduction', 124 => 'Partner Introduction', 125 => 'Networking', 126 => 'Fertile Ground', 127 => 'Silent Voices', 128 => 'Common Vision', 129 => 'Findings', 130 => 'Design Trail Info', 131 => 'Participation Trail Info'],
      'lower' => [132 => 'Welcome', 133 => 'Information'],
      'upper' => [134 => 'Welcome', 135 => 'Information'],
      'summit' => [136 => 'Welcome', 137 => 'Information'],
      'reflection' => [138 => 'Welcome', 139 => 'Information']
    ]
  ],
  'credits' => [
    'english' => [70 => 'Credits'],
    'dutch' => [140 => 'Credits']
  ]
];

// Helper function to determine if a menu item or submenu should be open
function is_menu_open($section, $current_id, $subsection, $language)
{
  global $sections;
  return isset($sections[$section][$language][$subsection]) && in_array($current_id, array_keys($sections[$section][$language][$subsection]));
}

// Helper function to determine if a parent section should be open
function is_parent_open($section, $current_id, $language)
{
  global $sections;
  foreach ($sections[$section][$language] as $subsection => $items) {
    if (is_array($items) && in_array($current_id, array_keys($items))) {
      return true;
    }
  }
  return false;
}

// Determine if specific sections should be open
$on_boarding_open = ($english_open && ($current_id == 1 || is_menu_open('on_boarding', $current_id, 'objects', 'english') || is_menu_open('on_boarding', $current_id, 'information', 'english'))) ||
  ($dutch_open && ($current_id == 71 || is_menu_open('on_boarding', $current_id, 'objects', 'dutch') || is_menu_open('on_boarding', $current_id, 'information', 'dutch')));


$individual_trails_open = $english_open && is_parent_open('individual_trails', $current_id, 'english') ||
  $dutch_open && is_parent_open('individual_trails', $current_id, 'dutch');

$main_trail_open = $english_open && is_parent_open('main_trail', $current_id, 'english') ||
  $dutch_open && is_parent_open('main_trail', $current_id, 'dutch');

$camps_open = $english_open && is_parent_open('camps', $current_id, 'english') ||
  $dutch_open && is_parent_open('camps', $current_id, 'dutch');

$credits_open = in_array($current_id, $sections['credits']['english']) || in_array($current_id, $sections['credits']['dutch']);
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>

<body class="hold-transition sidebar-mini">
  <div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Logout Button -->
        <li class="nav-item">
          <a href="#" class="nav-link" id="logoutButton">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </li>
      </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="https://trailtool.org" class="brand-link">
        <img src="../dist/img/TrailToolLogo.png" alt="TrailTool Logo" class="brand-image img-circle elevation-3"
          style="opacity: .8">
        <span class="brand-text font-weight-light">Trail Tool</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="../dist/img/logoefiwh.png" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a href="#" class="d-block"><?php echo htmlspecialchars($username); ?></a>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <!-- Content Manager -->
            <li class="nav-item has-treeview <?php echo ($english_open || $dutch_open) ? 'menu-open' : ''; ?>">
              <a class="nav-link">
                <i class="nav-icon fas fa-globe"></i>
                <p>
                  Content Manager
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <!-- English Section -->
              <ul class="nav nav-treeview">
                <li class="nav-item has-treeview <?php echo $english_open ? 'menu-open' : ''; ?>">
                  <a class="nav-link">
                    <i class="nav-icon fas fa-flag-usa"></i>
                    <p>
                      English
                      <i class="right fas fa-angle-left"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <!-- On Boarding -->
                    <li class="nav-item has-treeview <?php echo ($on_boarding_open && $english_open) ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-book"></i>
                        <p>
                          On Boarding
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <!-- Introduction -->
                        <li class="nav-item">
                          <a href="edit.php?id=1"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 1) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Introduction</p>
                          </a>
                        </li>
                        <!-- Objects -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('on_boarding', $current_id, 'objects', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i>
                            <p>
                              Objects
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=2"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 2) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Campfire Description</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=3"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 3) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Torch Description</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=4"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 4) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Marker Description</p>
                              </a>
                            </li>
                          </ul>
                        </li>
                        <!-- Information -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('on_boarding', $current_id, 'information', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-info-circle"></i>
                            <p>
                              Information
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=5"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 5) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Header</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=6"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 6) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Knowledge</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=7"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 7) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Practical Tools</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=8"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 8) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Examples</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=9"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 9) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Campfire Sessions</p>
                              </a>
                            </li>
                          </ul>
                        </li>
                      </ul>
                    </li>
                    <!-- Individual Trails -->
                    <li class="nav-item has-treeview <?php echo $individual_trails_open ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-hiking"></i>
                        <p>
                          Individual Trails
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <!-- Student Trail -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('individual_trails', $current_id, 'student', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-user"></i>
                            <p>
                              Student Trail
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=10"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 10) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>1st Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=11"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 11) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>2nd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=12"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 12) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>3rd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=13"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 13) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>4th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=14"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 14) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>5th Torch</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Teacher Trail -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('individual_trails', $current_id, 'teacher', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-chalkboard-teacher"></i>
                            <p>
                              Teacher Trail
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=15"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 15) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>1st Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=16"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 16) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>2nd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=17"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 17) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>3rd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=18"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 18) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>4th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=19"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 19) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>5th Torch</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Researcher Trail -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('individual_trails', $current_id, 'researcher', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-flask"></i>
                            <p>
                              Researcher Trail
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=20"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 20) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>1st Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=21"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 21) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>2nd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=22"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 22) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>3rd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=23"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 23) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>4th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=24"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 24) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>5th Torch</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Partner Trail -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('individual_trails', $current_id, 'partner', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-handshake"></i>
                            <p>
                              Partner Trail
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=25"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 25) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>1st Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=26"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 26) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>2nd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=27"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 27) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>3rd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=28"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 28) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>4th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=29"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 29) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>5th Torch</p>
                              </a>
                            </li>
                          </ul>
                        </li>
                      </ul>
                    </li>


                    <!-- Main Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_parent_open('main_trail', $current_id, 'english') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-mountain"></i>
                        <p>
                          Main Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <!-- Design Trail -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('main_trail', $current_id, 'design', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-drafting-compass"></i>
                            <p>
                              Design Trail
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=30"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 30) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>1st Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=31"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 31) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>2nd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=32"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 32) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>3rd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=33"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 33) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>4th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=34"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 34) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>5th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=35"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 35) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>6th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=36"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 36) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>7th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=37"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 37) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>8th Torch</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Participation Trail -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('main_trail', $current_id, 'participation', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-user-friends"></i>
                            <p>
                              Participation Trail
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=38"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 38) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>1st Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=39"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 39) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>2nd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=40"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 40) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>3rd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=41"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 41) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>4th Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=42"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 42) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>5th Torch</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Summit Trail -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('main_trail', $current_id, 'summit', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-flag-checkered"></i>
                            <p>
                              Summit Trail
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=43"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 43) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>1st Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=44"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 44) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>2nd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=45"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 45) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>3rd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=46"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 46) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>4th Torch</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Reflection Trail -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('main_trail', $current_id, 'reflection', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-comments"></i>
                            <p>
                              Reflection Trail
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=47"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 47) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>1st Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=48"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 48) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>2nd Torch</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=49"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 49) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>3rd Torch</p>
                              </a>
                            </li>
                          </ul>
                        </li>
                      </ul>
                    </li>


                    <!-- Camps -->
                    <li
                      class="nav-item has-treeview <?php echo is_parent_open('camps', $current_id, 'english') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-campground"></i>
                        <p>
                          Camps
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <!-- Base Camp -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'base', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-campground"></i>
                            <p>
                              Base Camp
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=50"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 50) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Welcome</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=51"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 51) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Student Introduction</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=52"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 52) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Teacher Introduction</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=53"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 53) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Researcher Introduction</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=54"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 54) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Partner Introduction</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=55"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 55) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Networking</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=56"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 56) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Fertile Ground</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=57"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 57) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Silent Voices</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=58"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 58) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Common Vision</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=59"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 59) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Findings</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=60"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 60) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Design Trail Info</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=61"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 61) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Participation Trail Info</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Lower Camp -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'lower', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-campground"></i>
                            <p>
                              Lower Camp
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=62"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 62) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Welcome</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=63"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 63) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Information</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Upper Camp -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'upper', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-campground"></i>
                            <p>
                              Upper Camp
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=64"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 64) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Welcome</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=65"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 65) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Information</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Summit Camp -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'summit', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-campground"></i>
                            <p>
                              Summit Camp
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=66"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 66) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Welcome</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=67"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 67) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Information</p>
                              </a>
                            </li>
                          </ul>
                        </li>

                        <!-- Reflection Camp -->
                        <li
                          class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'reflection', 'english') ? 'menu-open' : ''; ?>">
                          <a class="nav-link">
                            <i class="nav-icon fas fa-campground"></i>
                            <p>
                              Reflection Camp
                              <i class="right fas fa-angle-left"></i>
                            </p>
                          </a>
                          <ul class="nav nav-treeview">
                            <li class="nav-item">
                              <a href="edit.php?id=68"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 68) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Welcome</p>
                              </a>
                            </li>
                            <li class="nav-item">
                              <a href="edit.php?id=69"
                                class="nav-link <?php echo ($current_page == 'edit' && $current_id == 69) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-circle"></i>
                                <p>Information</p>
                              </a>
                            </li>
                          </ul>
                        </li>
                      </ul>
                    </li>
                    <!-- Credits -->
                    <li class="nav-item">
                      <a href="edit.php?id=70"
                        class="nav-link <?php echo ($current_page == 'edit' && $current_id == 70) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-book"></i>
                        <p>Credits</p>
                      </a>
                    </li>
                </li>
              </ul>

              <!-- Dutch Section -->
            <li class="nav-item has-treeview <?php echo $dutch_open ? 'menu-open' : ''; ?>">
              <a class="nav-link">
                <i class="nav-icon fas fa-flag"></i>
                <p>
                  Dutch
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <!-- On Boarding -->
                <li
                class="nav-item has-treeview <?php echo ($on_boarding_open && $dutch_open) ? 'menu-open' : ''; ?>">
                  <a class="nav-link">
                    <i class="nav-icon fas fa-book"></i>
                    <p>
                      On Boarding
                      <i class="right fas fa-angle-left"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="edit.php?id=71"
                        class="nav-link <?php echo ($current_page == 'edit' && $current_id == 71) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-circle"></i>
                        <p>Introduction</p>
                      </a>
                    </li>
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('on_boarding', $current_id, 'objects', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                          Objects
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=72"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 72) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Campfire Description</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=73"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 73) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Torch Description</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=74"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 74) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Marker Description</p>
                          </a>
                        </li>
                      </ul>
                    </li>
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('on_boarding', $current_id, 'information', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-info-circle"></i>
                        <p>
                          Information
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=75"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 75) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Header</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=76"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 76) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Knowledge</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=77"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 77) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Practical Tools</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=78"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 78) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Examples</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=79"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 79) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Campfire Sessions</p>
                          </a>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </li>

                <!-- Individual Trails -->
                <li
                  class="nav-item has-treeview <?php echo is_parent_open('individual_trails', $current_id, 'dutch') ? 'menu-open' : ''; ?>">
                  <a class="nav-link">
                    <i class="nav-icon fas fa-hiking"></i>
                    <p>
                      Individual Trails
                      <i class="right fas fa-angle-left"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <!-- Student Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('individual_trails', $current_id, 'student', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                          Student Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=80"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 80) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>1st Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=81"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 81) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>2nd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=82"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 82) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>3rd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=83"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 83) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>4th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=84"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 84) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>5th Torch</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Teacher Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('individual_trails', $current_id, 'teacher', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-chalkboard-teacher"></i>
                        <p>
                          Teacher Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=85"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 85) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>1st Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=86"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 86) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>2nd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=87"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 87) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>3rd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=88"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 88) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>4th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=89"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 89) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>5th Torch</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Researcher Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('individual_trails', $current_id, 'researcher', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-flask"></i>
                        <p>
                          Researcher Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=90"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 90) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>1st Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=91"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 91) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>2nd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=92"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 92) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>3rd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=93"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 93) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>4th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=94"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 94) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>5th Torch</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Partner Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('individual_trails', $current_id, 'partner', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-handshake"></i>
                        <p>
                          Partner Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=95"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 95) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>1st Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=96"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 96) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>2nd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=97"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 97) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>3rd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=98"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 98) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>4th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=99"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 99) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>5th Torch</p>
                          </a>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </li>

                <!-- Main Trail -->
                <li
                  class="nav-item has-treeview <?php echo is_parent_open('main_trail', $current_id, 'dutch') ? 'menu-open' : ''; ?>">
                  <a class="nav-link">
                    <i class="nav-icon fas fa-mountain"></i>
                    <p>
                      Main Trail
                      <i class="right fas fa-angle-left"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <!-- Design Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('main_trail', $current_id, 'design', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-drafting-compass"></i>
                        <p>
                          Design Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=100"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 100) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>1st Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=101"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 101) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>2nd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=102"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 102) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>3rd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=103"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 103) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>4th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=104"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 104) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>5th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=105"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 105) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>6th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=106"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 106) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>7th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=107"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 107) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>8th Torch</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Participation Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('main_trail', $current_id, 'participation', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-user-friends"></i>
                        <p>
                          Participation Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=108"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 108) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>1st Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=109"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 109) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>2nd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=110"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 110) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>3rd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=111"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 111) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>4th Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=112"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 112) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>5th Torch</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Summit Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('main_trail', $current_id, 'summit', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-flag-checkered"></i>
                        <p>
                          Summit Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=113"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 113) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>1st Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=114"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 114) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>2nd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=115"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 115) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>3rd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=116"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 116) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>4th Torch</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Reflection Trail -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('main_trail', $current_id, 'reflection', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-comments"></i>
                        <p>
                          Reflection Trail
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=117"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 117) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>1st Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=118"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 118) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>2nd Torch</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=119"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 119) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>3rd Torch</p>
                          </a>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </li>

                <!-- Camps -->
                <li
                  class="nav-item has-treeview <?php echo is_parent_open('camps', $current_id, 'dutch') ? 'menu-open' : ''; ?>">
                  <a class="nav-link">
                    <i class="nav-icon fas fa-campground"></i>
                    <p>
                      Camps
                      <i class="right fas fa-angle-left"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <!-- Base Camp -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'base', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-campground"></i>
                        <p>
                          Base Camp
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=120"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 120) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Welcome</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=121"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 121) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Student Introduction</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=122"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 122) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Teacher Introduction</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=123"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 123) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Researcher Introduction</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=124"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 124) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Partner Introduction</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=125"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 125) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Networking</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=126"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 126) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Fertile Ground</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=127"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 127) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Silent Voices</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=128"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 128) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Common Vision</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=129"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 129) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Findings</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=130"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 130) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Design Trail Info</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=131"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 131) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Participation Trail Info</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Lower Camp -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'lower', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-campground"></i>
                        <p>
                          Lower Camp
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=132"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 132) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Welcome</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=133"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 133) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Information</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Upper Camp -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'upper', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-campground"></i>
                        <p>
                          Upper Camp
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=134"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 134) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Welcome</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=135"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 135) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Information</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Summit Camp -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'summit', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-campground"></i>
                        <p>
                          Summit Camp
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=136"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 136) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Welcome</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=137"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 137) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Information</p>
                          </a>
                        </li>
                      </ul>
                    </li>

                    <!-- Reflection Camp -->
                    <li
                      class="nav-item has-treeview <?php echo is_menu_open('camps', $current_id, 'reflection', 'dutch') ? 'menu-open' : ''; ?>">
                      <a class="nav-link">
                        <i class="nav-icon fas fa-campground"></i>
                        <p>
                          Reflection Camp
                          <i class="right fas fa-angle-left"></i>
                        </p>
                      </a>
                      <ul class="nav nav-treeview">
                        <li class="nav-item">
                          <a href="edit.php?id=138"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 138) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Welcome</p>
                          </a>
                        </li>
                        <li class="nav-item">
                          <a href="edit.php?id=139"
                            class="nav-link <?php echo ($current_page == 'edit' && $current_id == 139) ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-circle"></i>
                            <p>Information</p>
                          </a>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </li>

                <!-- Credits -->
                <li class="nav-item">
                  <a href="edit.php?id=140"
                    class="nav-link <?php echo ($current_page == 'edit' && $current_id == 140) ? 'active' : ''; ?>">
                    <i class="nav-icon fas fa-book"></i>
                    <p>Credits</p>
                  </a>
                </li>
              </ul>
            </li>

          </ul>

        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>
  </div>
</body>

</html>