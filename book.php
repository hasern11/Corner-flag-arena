<?php
require_once 'config/security.php';
session_start();
send_security_headers();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $user_id = $_SESSION['user_id'];
    $booking_date = $_POST['booking_date'] ?? '';
    $time_slot = $_POST['time_slot'] ?? '';
    $upload_err = '';

    // Validate fields
    if (empty($booking_date) || empty($time_slot)) {
        $_SESSION['booking_error'] = 'All fields are required.';
        header("Location: dashboard.php");
        exit();
    }

    // Verify valid time slot
    $valid_slots = ['4PM-5PM', '5PM-6PM', '6PM-7PM', '7PM-8PM', '8PM-9PM', '9PM-10PM', '10PM-11PM'];
    if (!in_array($time_slot, $valid_slots)) {
        $_SESSION['booking_error'] = 'Invalid time slot selected.';
        header("Location: dashboard.php");
        exit();
    }

    // Verify date is not in the past
    $today = date('Y-m-d');
    if ($booking_date < $today) {
        $_SESSION['booking_error'] = 'You cannot book a slot in the past.';
        header("Location: dashboard.php");
        exit();
    }

    // Check if slot is already booked
    $stmt = $pdo->prepare("SELECT id FROM bookings WHERE booking_date = ? AND time_slot = ? AND status IN ('pending', 'approved')");
    $stmt->execute([$booking_date, $time_slot]);
    if ($stmt->fetch()) {
        $_SESSION['booking_error'] = 'This time slot is already booked for the selected date.';
        header("Location: dashboard.php");
        exit();
    }

    // Validate and process payment proof upload
    if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['booking_error'] = 'Please upload a valid payment proof.';
        header("Location: dashboard.php");
        exit();
    }

    $file = $_FILES['payment_proof'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    // Check file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Validate image MIME type
    $fileMime = mime_content_type($fileTmpName);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($fileExt, $allowedExts) || !in_array($fileMime, $allowedMimes)) {
        $_SESSION['booking_error'] = 'Only image files (JPG, JPEG, PNG, GIF) are allowed for payment proof.';
        header("Location: dashboard.php");
        exit();
    }

    // Verify image size details to ensure it is a valid image (prevents polyglot payloads)
    $imageInfo = @getimagesize($fileTmpName);
    if ($imageInfo === false) {
        $_SESSION['booking_error'] = 'Invalid image file provided.';
        header("Location: dashboard.php");
        exit();
    }

    // Limit file size to 5MB
    if ($fileSize > 5 * 1024 * 1024) {
        $_SESSION['booking_error'] = 'The uploaded file is too large. Max size is 5MB.';
        header("Location: dashboard.php");
        exit();
    }

    // Create uploads folder if not exists
    $upload_dir = 'uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Save image with unique name
    $newFileName = uniqid('proof_', true) . '.' . $fileExt;
    $destPath = $upload_dir . $newFileName;

    if (move_uploaded_file($fileTmpName, $destPath)) {
        // Insert booking into database
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, booking_date, time_slot, payment_proof, status) VALUES (?, ?, ?, ?, 'pending')");
        try {
            $stmt->execute([$user_id, $booking_date, $time_slot, $destPath]);
            header("Location: booking_success.php?date=" . urlencode($booking_date) . "&slot=" . urlencode($time_slot));
            exit();
        } catch (PDOException $e) {
            // Delete file if DB insert fails
            if (file_exists($destPath)) {
                unlink($destPath);
            }
            // Check if duplicate key violation occurred despite check
            if ($e->getCode() == 23000) {
                $_SESSION['booking_error'] = 'This slot has just been booked by another user.';
            } else {
                $_SESSION['booking_error'] = 'An error occurred during booking. Please try again.';
            }
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $_SESSION['booking_error'] = 'Failed to save uploaded file. Please try again.';
        header("Location: dashboard.php");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>
