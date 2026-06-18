<?php
/**
 * settings.php — Shared helper for reading/writing app settings from the DB.
 */

if (!isset($pdo)) {
    require_once __DIR__ . '/db.php';
}

function get_setting(string $key, string $default = ''): string {
    global $pdo;
    $stmt = $pdo->prepare("SELECT value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['value'] : $default;
}

function update_setting(string $key, string $value): void {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, value) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE value = VALUES(value)");
    $stmt->execute([$key, $value]);
}

/**
 * Returns the price for a specific time slot.
 * Falls back to the global booking_price if no slot-specific price exists.
 */
function get_slot_price(string $slot): int {
    $key = 'price_' . $slot;
    $val = get_setting($key, '');
    if ($val !== '' && ctype_digit($val) && (int)$val > 0) {
        return (int)$val;
    }
    // Fallback to global price
    $fallback = get_setting('booking_price', '15000');
    return (int)$fallback;
}

/**
 * Returns an associative array of all slot prices: ['4PM-5PM' => 15000, ...]
 */
function get_all_slot_prices(): array {
    $slots = ['4PM-5PM','5PM-6PM','6PM-7PM','7PM-8PM','8PM-9PM','9PM-10PM','10PM-11PM'];
    $prices = [];
    foreach ($slots as $slot) {
        $prices[$slot] = get_slot_price($slot);
    }
    return $prices;
}
