<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u891386226_maatka');
define('DB_USER', 'u891386226_maatka');
define('DB_PASS', 'YOUR_DATABASE_PASSWORD'); // Get from Hostinger

// Site Configuration
define('SITE_URL', 'https://www.maatka.com');
define('SITE_NAME', 'MAATKA');
define('SITE_EMAIL', 'support@maatka.com');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'support@maatka.com');
define('SMTP_FROM_NAME', 'MAATKA Support');

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    error_log('Database Error: ' . $e->getMessage());
    exit;
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generate_invoice_number() {
    return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function generate_legacy_id($invoice_number) {
    return 'MTK-' . str_replace('INV-', '', $invoice_number);
}

function send_json_response($success, $message, $data = []) {
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

function log_error($message) {
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL, 3, __DIR__ . '/error.log');
}
?>