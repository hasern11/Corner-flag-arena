<?php
require_once 'config/security.php';
session_start();
send_security_headers();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

require_once 'config/db.php';

$error = '';
$rate_key = 'admin_login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    require_csrf();

    // Rate limiting — 5 attempts, 15-min lockout
    if (is_rate_limited($rate_key)) {
        $mins = get_lockout_minutes_remaining($rate_key);
        $error = "Too many failed attempts. Please wait {$mins} minute(s) before trying again.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Fetch admin from DB
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            reset_rate_limit($rate_key);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $admin['username'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            record_failed_attempt($rate_key);
            $error = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Corner Flag Arena</title>
    <link rel="icon" href="assets/logo.png" type="image/png">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo-wrapper">
                <img src="assets/logo.png" alt="Corner Flag Arena Logo">
                <div class="logo-text">
                    CORNER FLAG
                    <span>ARENA</span>
                </div>
            </a>
            <ul class="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="admin_login.php" class="active">Admin Portal</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <div class="auth-wrapper" style="border-top-color: var(--accent-yellow);">
            <h2 class="auth-title">Admin Login</h2>
            <p class="auth-subtitle">Corner Flag Arena Management</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="admin_login.php" method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn btn-yellow btn-block">Log In as Admin</button>
            </form>

            <div class="auth-footer">
                <a href="index.php">Back to Landing Page</a>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Corner Flag Arena. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
