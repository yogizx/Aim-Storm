<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'maatka');

// Establish connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Establish connection if not already active
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Razorpay configuration (Loaded from DB)
$razorpay_key = getSetting('razorpay_key', $conn) ?: 'rzp_test_XXXXXXXXXXXXXX';
$razorpay_secret = getSetting('razorpay_secret', $conn) ?: 'XXXXXXXXXXXXXXXXXXXXXXXX';

define('RAZORPAY_KEY_ID', $razorpay_key);
define('RAZORPAY_KEY_SECRET', $razorpay_secret);

// Function to get setting from database
function getSetting($key, $conn) {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    if ($stmt) {
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['setting_value'];
        }
    }
    return null;
}

// Function to update or insert setting
function setSetting($key, $value, $conn) {
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    if ($stmt) {
        $stmt->bind_param("sss", $key, $value, $value);
        return $stmt->execute();
    }
    return false;
}
?>
