<?php
require_once 'config.php';
require_once 'razorpay-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Invalid request method');
}

try {
    // Validate input
    $name = sanitize_input($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $mobile = sanitize_input($_POST['mobile'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Valid name is required';
    }
    
    if (!$email) {
        $errors[] = 'Valid email is required';
    }
    
    if (empty($mobile) || !preg_match('/^[6-9]\d{9}$/', $mobile)) {
        $errors[] = 'Valid 10-digit mobile number is required';
    }
    
    if (empty($address)) {
        $errors[] = 'Address is required';
    }
    
    // Check if email or mobile already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR mobile = ? LIMIT 1");
    $stmt->execute([$email, $mobile]);
    if ($stmt->fetch()) {
        $errors[] = 'Email or mobile number already registered';
    }
    
    if (!empty($errors)) {
        send_json_response(false, implode(', ', $errors));
    }
    
    // Generate invoice and legacy ID
    $invoice_number = generate_invoice_number();
    $legacy_id = generate_legacy_id($invoice_number);
    
    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, mobile, address, invoice_number, legacy_id, payment_status) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$name, $email, $mobile, $address, $invoice_number, $legacy_id]);
    $user_id = $pdo->lastInsertId();
    
    // Create Razorpay order
    $api = get_razorpay_api();
    $credentials = get_razorpay_credentials();
    
    $orderData = [
        'receipt' => $invoice_number,
        'amount' => MEMBERSHIP_AMOUNT, // Amount in paise
        'currency' => 'INR',
        'notes' => [
            'user_id' => $user_id,
            'legacy_id' => $legacy_id,
            'email' => $email
        ]
    ];
    
    $razorpayOrder = $api->order->create($orderData);
    
    // Save payment record
    $stmt = $pdo->prepare("
        INSERT INTO payments (user_id, razorpay_order_id, amount, status) 
        VALUES (?, ?, ?, 'created')
    ");
    $stmt->execute([$user_id, $razorpayOrder['id'], MEMBERSHIP_AMOUNT_INR]);
    
    // Return response
    send_json_response(true, 'Order created successfully', [
        'razorpay_key' => $credentials['key_id'],
        'order_id' => $razorpayOrder['id'],
        'amount' => MEMBERSHIP_AMOUNT,
        'user_id' => $user_id,
        'user_name' => $name,
        'user_email' => $email,
        'user_mobile' => $mobile,
        'legacy_id' => $legacy_id
    ]);
    
} catch (Exception $e) {
    log_error('Payment initiation error: ' . $e->getMessage());
    send_json_response(false, 'Failed to create order. Please try again.');
}
?>