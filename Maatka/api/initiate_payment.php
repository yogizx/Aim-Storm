<?php
header('Content-Type: application/json');
require_once '../db_config.php';

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
    exit;
}

$name = mysqli_real_escape_string($conn, $data['name'] ?? '');
$email = mysqli_real_escape_string($conn, $data['email'] ?? '');
$phone = mysqli_real_escape_string($conn, $data['phone'] ?? '');
$address = mysqli_real_escape_string($conn, $data['address'] ?? '');

if (empty($name) || empty($email) || empty($phone) || empty($address)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

// 1. Fetch Razorpay Keys from DB or Config
$keyId = getSetting('razorpay_key', $conn) ?: RAZORPAY_KEY_ID;
$keySecret = getSetting('razorpay_secret', $conn) ?: RAZORPAY_KEY_SECRET;

// 2. Create Razorpay Order via cURL
$amountInPaise = 90000; // 900 INR
$receiptId = 'msg_' . time();

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'amount' => $amountInPaise,
    'currency' => 'INR',
    'receipt' => $receiptId,
    'payment_capture' => 1
]));
curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$order = json_decode($response, true);
curl_close($ch);

if (isset($order['id'])) {
    $razorpay_order_id = $order['id'];
    
    // 3. Store User in MySQL (Status: Pending)
    $legacy_id = 'MTK' . strtoupper(substr(md5(time() . $email), 0, 6));
    
    $stmt = $conn->prepare("INSERT INTO users (legacy_id, name, email, mobile, address, payment_status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssss", $legacy_id, $name, $email, $phone, $address);
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // 4. Record Payment Attempt
        $stmt_pay = $conn->prepare("INSERT INTO payments (user_id, razorpay_order_id, amount, status) VALUES (?, ?, ?, 'created')");
        $amountDecimal = 900.00;
        $stmt_pay->bind_param("isd", $user_id, $razorpay_order_id, $amountDecimal);
        $stmt_pay->execute();
        
        // 5. Return success and checkout data to frontend
        echo json_encode([
            'success' => true,
            'checkout_data' => [
                'key' => $keyId,
                'amount' => $amountInPaise,
                'order_id' => $razorpay_order_id,
                'name' => 'MAATKA Premium',
                'description' => 'Digital Membership Onboarding',
                'prefill' => [
                    'name' => $name,
                    'email' => $email,
                    'contact' => $phone
                ],
                'notes' => [
                    'user_id' => $user_id,
                    'legacy_id' => $legacy_id
                ]
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    }
} else {
    $errorDesc = $order['error']['description'] ?? 'Gateway connection failed.';
    echo json_encode(['success' => false, 'error' => $errorDesc]);
}
?>
