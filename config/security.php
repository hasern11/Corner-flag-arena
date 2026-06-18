<?php
/**
 * security.php — Shared security helpers.
 * Must be required BEFORE session_start() on every page.
 */

// Secure session cookie flags
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

/**
 * Generate (or retrieve) the CSRF token for this session.
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token against the session token.
 */
function validate_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Returns a hidden HTML input carrying the CSRF token.
 * Drop <?php echo csrf_field(); ?> inside every <form>.
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

/**
 * Abort with 403 if CSRF token is missing or invalid.
 */
function require_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!validate_csrf_token($token)) {
        http_response_code(403);
        die('403 Forbidden — invalid or missing CSRF token.');
    }
}

/**
 * Send standard security response headers.
 */
function send_security_headers(): void {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

/**
 * Database-backed, IP-bound rate limiter.
 * Call at the top of sensitive handlers.
 * Returns true if the user's IP is currently locked out for this action.
 */
function is_rate_limited(string $key, int $max_attempts = 5, int $lockout_seconds = 900): bool {
    global $pdo;
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $stmt = $pdo->prepare("SELECT attempts, lockout_until FROM rate_limits WHERE ip_address = ? AND action_key = ?");
    $stmt->execute([$ip, $key]);
    $rl = $stmt->fetch();

    if ($rl) {
        if ($rl['lockout_until'] > time()) {
            return true;
        }
        // Lockout expired — reset attempts in DB
        if ($rl['lockout_until'] > 0 && time() >= $rl['lockout_until']) {
            reset_rate_limit($key);
        }
    }

    return false;
}

function record_failed_attempt(string $key, int $max_attempts = 5, int $lockout_seconds = 900): void {
    global $pdo;
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    $stmt = $pdo->prepare("SELECT attempts FROM rate_limits WHERE ip_address = ? AND action_key = ?");
    $stmt->execute([$ip, $key]);
    $rl = $stmt->fetch();

    if ($rl) {
        $new_attempts = $rl['attempts'] + 1;
        if ($new_attempts >= $max_attempts) {
            $lockout_until = time() + $lockout_seconds;
            $stmt = $pdo->prepare("UPDATE rate_limits SET attempts = 0, lockout_until = ? WHERE ip_address = ? AND action_key = ?");
            $stmt->execute([$lockout_until, $ip, $key]);
        } else {
            $stmt = $pdo->prepare("UPDATE rate_limits SET attempts = ? WHERE ip_address = ? AND action_key = ?");
            $stmt->execute([$new_attempts, $ip, $key]);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO rate_limits (ip_address, action_key, attempts, lockout_until) VALUES (?, ?, 1, 0)");
        $stmt->execute([$ip, $key]);
    }
}

function reset_rate_limit(string $key): void {
    global $pdo;
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $pdo->prepare("DELETE FROM rate_limits WHERE ip_address = ? AND action_key = ?");
    $stmt->execute([$ip, $key]);
}

function get_lockout_minutes_remaining(string $key): int {
    global $pdo;
    if (!isset($pdo)) {
        require_once __DIR__ . '/db.php';
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $pdo->prepare("SELECT lockout_until FROM rate_limits WHERE ip_address = ? AND action_key = ?");
    $stmt->execute([$ip, $key]);
    $rl = $stmt->fetch();

    if ($rl && $rl['lockout_until'] > time()) {
        return (int) ceil(($rl['lockout_until'] - time()) / 60);
    }
    return 0;
}

