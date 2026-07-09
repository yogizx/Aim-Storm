<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Debug check
if (isset($_GET['debug'])) {
    echo json_encode(['success' => true, 'message' => 'PHP script is reachable!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once '../db_config.php';

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data.']);
    exit;
}

// 1. Server-side Validation
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');

if (empty($name) || empty($email) || empty($phone) || empty($address)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address format.']);
    exit;
}

// 2. Sanitize for Database
$name = mysqli_real_escape_string($conn, $name);
$email = mysqli_real_escape_string($conn, $email);
$phone = mysqli_real_escape_string($conn, $phone);
$address = mysqli_real_escape_string($conn, $address);

// 3. Generate Identity Data
$legacy_id = 'MTK' . strtoupper(substr(md5(time() . $email), 0, 8));
$invoice_num = 'INV-' . strtoupper(substr(uniqid(), 7));

// 4. Store User in MySQL (Status: Completed - Bypassing Razorpay for now)
$stmt = $conn->prepare("INSERT INTO users (legacy_id, name, email, mobile, address, payment_status, invoice_number) VALUES (?, ?, ?, ?, ?, 'completed', ?)");

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database prepare error (users): ' . $conn->error]);
    exit;
}

$stmt->bind_param("ssssss", $legacy_id, $name, $email, $phone, $address, $invoice_num);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // 5. Create Invoice Record
    $invoice_date = date('Y-m-d');
    $amount = 900.00;
    $stmt_inv = $conn->prepare("INSERT INTO invoices (user_id, invoice_number, invoice_date, amount) VALUES (?, ?, ?, ?)");
    if ($stmt_inv) {
        $stmt_inv->bind_param("issd", $user_id, $invoice_num, $invoice_date, $amount);
        $stmt_inv->execute();
    }

    // 6. Send Confirmation Email
    $subject = "Welcome to MAATKA - Membership Confirmed!";
    $to = $email;
    $headers = "From: MAATKA Support <support@maatka.com>\r\n";
    $headers .= "Reply-To: support@maatka.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $email_body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #FFD700; border-radius: 12px;'>
        <h2 style='color: #F4B400;'>Membership Activated, $name!</h2>
        <p>Your institutional profile has been verified and your membership is now active.</p>
        <div style='background: #f9f9f9; padding: 20px; border-radius: 10px; margin: 20px 0;'>
            <p><strong>Legacy ID:</strong> <span style='color: #F4B400;'>$legacy_id</span></p>
            <p><strong>Invoice No:</strong> $invoice_num</p>
            <p><strong>Assets:</strong> 100 KRITIKA Credits, 1 Sudarshana Coin</p>
        </div>
        <p>You can now access the premium collective portal.</p>
        <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
        <p style='font-size: 11px; color: #999;'>This is an automated verification receipt from MAATKA Operations.</p>
    </div>
    ";
    
    $mail_status = 'failed';
    if (@mail($to, $subject, $email_body, $headers)) {
        $mail_status = 'sent';
    }
    
    // Log Email
    $stmt_email = $conn->prepare("INSERT INTO email_logs (user_id, email_to, subject, status) VALUES (?, ?, ?, ?)");
    if ($stmt_email) {
        $stmt_email->bind_param("isss", $user_id, $email, $subject, $mail_status);
        $stmt_email->execute();
    }

    // 7. Fetch Tax and Company configuration for Invoice
    $gst_rate = floatval(getSetting('gst_percentage', $conn) ?: '18');
    $company_address = getSetting('company_address', $conn) ?: 'MAATKA Operations Center, Global District';
    $company_gst_no = getSetting('company_gst_no', $conn) ?: 'GSTIN_PENDING';
    
    // Calculations for Invoice (Inclusive of GST)
    $total_amount = 900.00;
    $base_amount = $total_amount / (1 + ($gst_rate / 100));
    $tax_amount = $total_amount - $base_amount;
    $cgst_sgst_amount = $tax_amount / 2;

    // 8. Return Success Data for Invoice UI
    echo json_encode([
        'success' => true,
        'userData' => [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'legacyId' => $legacy_id,
            'invoiceNum' => $invoice_num,
            'date' => date('d/m/Y'),
            'time' => date('H:i:s'),
            'payId' => 'DIRECT_VERIFIED_' . strtoupper(substr(md5(time()), 0, 6)),
            'gstRate' => $gst_rate,
            'baseAmount' => number_format($base_amount, 2),
            'cgst' => number_format($cgst_sgst_amount, 2),
            'sgst' => number_format($cgst_sgst_amount, 2),
            'totalAmount' => number_format($total_amount, 2),
            'companyAddress' => $company_address,
            'companyGstNo' => $company_gst_no
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database storage error: ' . $conn->error]);
}
?>
