<?php
require_once 'config/security.php';
session_start();
send_security_headers();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php?view=settings");
    exit();
}

// CSRF validation
require_csrf();

require_once 'config/db.php';
require_once 'config/settings.php';

$slots = ['4PM-5PM','5PM-6PM','6PM-7PM','7PM-8PM','8PM-9PM','9PM-10PM','10PM-11PM'];
$errors = [];
$updated = 0;

foreach ($slots as $slot) {
    $field = 'price_' . $slot;
    $val = trim($_POST[$field] ?? '');
    if (!ctype_digit($val) || (int)$val <= 0) {
        $errors[] = "Invalid price for slot $slot.";
    } else {
        update_setting($field, $val);
        $updated++;
    }
}

// Also update the global fallback price if submitted
if (isset($_POST['booking_price'])) {
    $global = trim($_POST['booking_price']);
    if (ctype_digit($global) && (int)$global > 0) {
        update_setting('booking_price', $global);
    }
}

if (!empty($errors)) {
    $_SESSION['admin_error'] = implode(' ', $errors);
} else {
    $_SESSION['admin_success'] = "All $updated slot prices updated successfully.";
}

header("Location: admin_dashboard.php?view=settings");
exit();
