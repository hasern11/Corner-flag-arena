<?php
require_once 'config/security.php';
session_start();
send_security_headers();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Must be POST — state-changing action must never respond to GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php");
    exit();
}

// CSRF validation
require_csrf();

require_once 'config/db.php';

$id     = $_POST['id']     ?? '';
$action = $_POST['action'] ?? '';

if (empty($id) || empty($action)) {
    $_SESSION['admin_error'] = 'Invalid request parameters.';
    header("Location: admin_dashboard.php");
    exit();
}

// Validate action type
if ($action !== 'approve' && $action !== 'reject') {
    $_SESSION['admin_error'] = 'Invalid action requested.';
    header("Location: admin_dashboard.php");
    exit();
}

// Check if booking exists
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['admin_error'] = 'Booking record not found.';
    header("Location: admin_dashboard.php");
    exit();
}

// Update status
$new_status  = ($action === 'approve') ? 'approved' : 'rejected';
$update_stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");

try {
    $update_stmt->execute([$new_status, $id]);
    $_SESSION['admin_success'] = 'Booking status successfully updated to ' . $new_status . '.';
} catch (PDOException $e) {
    $_SESSION['admin_error'] = 'Failed to update booking status. Please try again.';
}

header("Location: admin_dashboard.php");
exit();
