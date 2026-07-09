<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u891386226_maatka');
define('DB_USER', 'u891386226_maatka');
define('DB_PASS', 'YOUR_DATABASE_PASSWORD'); // Same as above

// Admin Configuration
define('ADMIN_EMAIL', 'admin@maatka.com');
define('ITEMS_PER_PAGE', 20);

// Site Configuration
define('SITE_URL', 'https://www.maatka.com');
define('ADMIN_URL', SITE_URL . '/admin');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Helper Functions
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function format_currency($amount) {
    return '₹' . number_format($amount, 2);
}

function format_date($date) {
    return date('d M Y, h:i A', strtotime($date));
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>