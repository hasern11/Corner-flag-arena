<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit();
}
require_once 'config/db.php';
require_once 'config/settings.php';

header('Content-Type: application/json');
echo json_encode(get_all_slot_prices());
