<?php
// Load environment variables from .env file (if using something like vlucas/phpdotenv)
// require_once __DIR__ . '/vendor/autoload.php';
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// Set session cookie to expire in 2 hours (7200 seconds)
session_set_cookie_params([
    'lifetime' => getenv('SESSION_LIFETIME') ?: 7200, // 2 hours in seconds
    'path' => getenv('COOKIE_PATH') ?: '/', // Use the environment variable or default to '/'
    'domain' => getenv('COOKIE_DOMAIN') ?: 'localhost', // Use the environment variable or default to 'localhost'
    'secure' => getenv('COOKIE_SECURE') === 'true', // Convert the secure flag to boolean based on the environment variable
    'httponly' => getenv('COOKIE_HTTPONLY') === 'true', // Convert to boolean based on the environment variable
    'samesite' => getenv('COOKIE_SAMESITE') ?: 'Strict' // Use the environment variable or default to 'Strict'
]);

session_start();
require_once './shared/database.php'; // Include the database connection file

// Check if the user is already logged in, if yes then redirect to intro.php
if (isset($_SESSION['username'])) {
    header('Location: intro.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the inputted password using SHA512
    $hashed_password = hash('sha512', $password);

    // Connect to the database
    $conn = getDbConnection();

    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ? AND password = ?");
    $stmt->bind_param("ss", $email, $hashed_password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Login successful, fetch user data
        $stmt->bind_result($user_id, $username);
        $stmt->fetch();

        // Set session variables
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;

        // Handle "Remember Me" functionality
        if (isset($_POST['remember'])) {
            // Set a cookie that expires in 30 days
            setcookie('remembered_email', $email, time() + (30 * 24 * 60 * 60), getenv('COOKIE_PATH') ?: '/', getenv('COOKIE_DOMAIN') ?: 'localhost', getenv('COOKIE_SECURE') === 'true', true);
        } else {
            // If "Remember Me" is not checked, clear the cookie
            setcookie('remembered_email', '', time() - 3600, getenv('COOKIE_PATH') ?: '/', getenv('COOKIE_DOMAIN') ?: 'localhost', getenv('COOKIE_SECURE') === 'true', true);
        }

        // Redirect to intro.php
        header("Location: intro.php");
        exit();
    } else {
        // Invalid login attempt
        echo "<script>alert('Invalid email or password. Please try again.'); window.location.href='login.php';</script>";
    }

    $stmt->close();
    $conn->close();
    exit();
}

// Check if a remembered email exists
$remembered_email = isset($_COOKIE['remembered_email']) ? $_COOKIE['remembered_email'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Trail Tool BackOffice | Login</title>

  <!-- Favicon -->
  <link rel="icon" href="../dist/img/TrailToolLogo.png" type="image/PNG">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../dist/css/adminlte.min.css">
</head>
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="../index2.html"><b>Trail</b>Tool</a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Sign in to start your session</p>

      <form action="login.php" method="post">
        <div class="input-group mb-3">
            <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo htmlspecialchars($remembered_email); ?>" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>
        <div class="input-group mb-3">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-8">
                <div class="icheck-primary">
                    <input type="checkbox" id="remember" name="remember" <?php if ($remembered_email) echo 'checked'; ?>>
                    <label for="remember">
                        Remember Me
                    </label>
                </div>
            </div>
            <!-- /.col -->
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>
            <!-- /.col -->
        </div>
      </form>

      <p class="mb-1">
        <a href="forgot-password.html">I forgot my password</a>
      </p>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

</body>
</html>
