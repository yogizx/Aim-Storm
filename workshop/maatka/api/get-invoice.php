<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json_response(false, 'Invalid request method');
}

try {
    $legacy_id = $_GET['id'] ?? '';
    
    if (empty($legacy_id)) {
        send_json_response(false, 'Legacy ID is required');
    }
    
    // Get user details
    $stmt = $pdo->prepare("
        SELECT u.*, p.razorpay_payment_id 
        FROM users u 
        LEFT JOIN payments p ON u.id = p.user_id AND p.status = 'success'
        WHERE u.legacy_id = ? AND u.payment_status = 'completed'
        LIMIT 1
    ");
    $stmt->execute([$legacy_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        send_json_response(false, 'Invoice not found');
    }
    
    send_json_response(true, 'Invoice retrieved successfully', [
        'invoice_number' => $user['invoice_number'],
        'legacy_id' => $user['legacy_id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'mobile' => $user['mobile'],
        'address' => $user['address'],
        'payment_id' => $user['razorpay_payment_id'] ?? 'N/A',
        'date' => date('d M Y', strtotime($user['created_at']))
    ]);
    
} catch (Exception $e) {
    log_error('Get invoice error: ' . $e->getMessage());
    send_json_response(false, 'Failed to retrieve invoice');
}
?>