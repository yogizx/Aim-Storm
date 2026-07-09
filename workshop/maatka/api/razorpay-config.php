<?php
// Razorpay Configuration

// Require Composer autoloader (install Razorpay PHP SDK via composer)
// composer require razorpay/razorpay

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config.php';

use Razorpay\Api\Api;

// Get Razorpay credentials from settings
function get_razorpay_credentials() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('razorpay_key', 'razorpay_secret')");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    return [
        'key_id' => $settings['razorpay_key'] ?? '',
        'key_secret' => $settings['razorpay_secret'] ?? ''
    ];
}

// Initialize Razorpay API
function get_razorpay_api() {
    $credentials = get_razorpay_credentials();
    return new Api($credentials['key_id'], $credentials['key_secret']);
}

// Amount in paise (₹900 = 90000 paise)
define('MEMBERSHIP_AMOUNT', 90000); // ₹900 in paise
define('MEMBERSHIP_AMOUNT_INR', 900); // ₹900

?>