<?php
require_once 'config/security.php';
session_start();
send_security_headers();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'config/db.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($phone) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (!preg_match('/^[0-9]{8,15}$/', $phone)) {
        $error = 'Please enter a valid phone number (8-15 digits).';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if phone number already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        if ($stmt->fetch()) {
            $error = 'This phone number is already registered.';
        } else {
            // Register user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name, phone, password) VALUES (?, ?, ?)");
            try {
                $stmt->execute([$name, $phone, $hashed_password]);
                $_SESSION['registration_success'] = 'Registration successful! You can now log in.';
                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Corner Flag Arena</title>
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
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" class="active">Register</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <div class="auth-wrapper">
            <h2 class="auth-title">Create Account</h2>
            <p class="auth-subtitle">Join Corner Flag Arena today!</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <span>⚠️</span> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Enter your full name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 08123456789" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimum 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Log In</a>
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
