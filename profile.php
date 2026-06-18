<?php
require_once 'config/security.php';
session_start();
send_security_headers();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php';
$user_id = $_SESSION['user_id'];

$error_profile = '';
$success_profile = '';
$error_password = '';
$success_password = '';

// Fetch current user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: logout.php");
    exit();
}

// Handle Profile Details Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    require_csrf();
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($phone)) {
        $error_profile = 'All fields are required.';
    } elseif (!preg_match('/^[0-9]{8,15}$/', $phone)) {
        $error_profile = 'Please enter a valid phone number (8-15 digits).';
    } else {
        // Check if phone number is taken by another user
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND id != ?");
        $stmt_check->execute([$phone, $user_id]);
        if ($stmt_check->fetch()) {
            $error_profile = 'This phone number is already registered by another user.';
        } else {
            // Update details
            $stmt_update = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            try {
                $stmt_update->execute([$name, $phone, $user_id]);
                $_SESSION['user_name'] = $name;
                $success_profile = 'Profile details updated successfully!';
                
                // Refresh local user data
                $user['name'] = $name;
                $user['phone'] = $phone;
            } catch (PDOException $e) {
                $error_profile = 'An error occurred. Please try again.';
            }
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    require_csrf();
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_password = 'All password fields are required.';
    } elseif (strlen($new_password) < 6) {
        $error_password = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error_password = 'New passwords do not match.';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt_pass = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            try {
                $stmt_pass->execute([$hashed_password, $user_id]);
                $success_password = 'Password changed successfully!';
                
                // Refresh local password hash
                $user['password'] = $hashed_password;
            } catch (PDOException $e) {
                $error_password = 'An error occurred. Please try again.';
            }
        } else {
            $error_password = 'Current password is incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Corner Flag Arena</title>
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
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <!-- Profile Banner -->
        <div style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: var(--white); padding: 1.5rem 2rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid var(--accent-yellow);">
            <div>
                <h2 style="font-size: 1.8rem; font-weight: 700;">Account Settings</h2>
                <p style="opacity: 0.9; font-size: 0.95rem;">Update your name, phone number, or change your account password.</p>
            </div>
            <div style="font-size: 2rem;">👤</div>
        </div>

        <div class="dashboard-layout">
            <!-- Left Column: Profile Details -->
            <div class="panel">
                <h3 class="panel-title">Personal Details</h3>
                
                <?php if (!empty($success_profile)): ?>
                    <div class="alert alert-success">
                        <span>✅</span> <?php echo htmlspecialchars($success_profile); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_profile)): ?>
                    <div class="alert alert-danger">
                        <span>⚠️</span> <?php echo htmlspecialchars($error_profile); ?>
                    </div>
                <?php endif; ?>

                <form action="profile.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">Update Details</button>
                </form>
            </div>

            <!-- Right Column: Change Password -->
            <div class="panel">
                <h3 class="panel-title">Change Password</h3>
                
                <?php if (!empty($success_password)): ?>
                    <div class="alert alert-success">
                        <span>✅</span> <?php echo htmlspecialchars($success_password); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_password)): ?>
                    <div class="alert alert-danger">
                        <span>⚠️</span> <?php echo htmlspecialchars($error_password); ?>
                    </div>
                <?php endif; ?>

                <form action="profile.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat new password" required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-yellow btn-block" style="margin-top: 1.5rem;">Change Password</button>
                </form>
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
