<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['date']) || empty($_GET['date'])) {
    echo json_encode([]);
    exit();
}

require_once 'config/db.php';
$date = $_GET['date'];

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date format']);
    exit();
}

// Get all bookings on this date that are either pending or approved
// Rejections free up the slot.
$stmt = $pdo->prepare("SELECT time_slot FROM bookings WHERE booking_date = ? AND status IN ('pending', 'approved')");
$stmt->execute([$date]);
$booked_slots = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($booked_slots);
exit();
?>
