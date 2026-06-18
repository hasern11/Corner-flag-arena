<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$date = $_GET['date'] ?? '';
$slot = $_GET['slot'] ?? '';

if (empty($date) || empty($slot)) {
    header("Location: dashboard.php");
    exit();
}

$formatted_date = date('l, F d, Y', strtotime($date));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Submitted - Corner Flag Arena</title>
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
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <div class="success-card">
            <div class="success-icon-wrapper">
                <span>✓</span>
            </div>
            
            <h2 class="success-title">Booking Submitted!</h2>
            <p class="success-subtitle">Your request has been received and is awaiting administrator verification.</p>

            <div class="details-list">
                <div class="details-row">
                    <span class="details-label">Selected Date</span>
                    <span class="details-value"><?php echo htmlspecialchars($formatted_date); ?></span>
                </div>
                <div class="details-row">
                    <span class="details-label">Time Slot</span>
                    <span class="details-value"><?php echo htmlspecialchars($slot); ?></span>
                </div>
                <div class="details-row">
                    <span class="details-label">Payment Status</span>
                    <span class="details-value">
                        <span class="badge badge-pending">Pending Verification</span>
                    </span>
                </div>
            </div>

            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2rem;">
                Administrators will review your uploaded receipt shortly. You can check the approval status anytime in your dashboard history.
            </p>

            <a href="dashboard.php" class="btn btn-primary btn-block">Back to Dashboard</a>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Corner Flag Arena. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
