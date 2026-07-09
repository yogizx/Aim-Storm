<?php
require_once 'config.php';
require_once 'razorpay-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Invalid request method');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $razorpay_payment_id = $input['razorpay_payment_id'] ?? '';
    $razorpay_order_id = $input['razorpay_order_id'] ?? '';
    $razorpay_signature = $input['razorpay_signature'] ?? '';
    $user_id = $input['user_id'] ?? '';
    
    if (empty($razorpay_payment_id) || empty($razorpay_order_id) || empty($razorpay_signature) || empty($user_id)) {
        send_json_response(false, 'Missing payment details');
    }
    
    // Verify signature
    $api = get_razorpay_api();
    $credentials = get_razorpay_credentials();
    
    $generated_signature = hash_hmac(
        'sha256',
        $razorpay_order_id . '|' . $razorpay_payment_id,
        $credentials['key_secret']
    );
    
    if ($generated_signature !== $razorpay_signature) {
        // Invalid signature
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET status = 'failed', error_description = 'Invalid signature' 
            WHERE razorpay_order_id = ?
        ");
        $stmt->execute([$razorpay_order_id]);
        
        send_json_response(false, 'Payment verification failed');
    }
    
    // Fetch payment details from Razorpay
    $payment = $api->payment->fetch($razorpay_payment_id);
    
    // Begin transaction
    $pdo->beginTransaction();
    
    try {
        // Update payment record
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET 
                razorpay_payment_id = ?,
                razorpay_signature = ?,
                status = 'success',
                payment_method = ?
            WHERE razorpay_order_id = ? AND user_id = ?
        ");
        $stmt->execute([
            $razorpay_payment_id,
            $razorpay_signature,
            $payment['method'] ?? 'unknown',
            $razorpay_order_id,
            $user_id
        ]);
        
        // Update user status and credit benefits
        $stmt = $pdo->prepare("
            UPDATE users 
            SET 
                payment_status = 'completed',
                kryta_credits = 100,
                sudarshana_coin = 1
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        
        // Get user details
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        // Create invoice record
        $stmt = $pdo->prepare("
            INSERT INTO invoices (user_id, invoice_number, invoice_date, amount) 
            VALUES (?, ?, CURDATE(), ?)
        ");
        $stmt->execute([$user_id, $user['invoice_number'], MEMBERSHIP_AMOUNT_INR]);
        
        // Commit transaction
        $pdo->commit();
        
        // Send confirmation email
        require_once 'email.php';
        send_confirmation_email($user);
        
        send_json_response(true, 'Payment verified successfully', [
            'legacy_id' => $user['legacy_id'],
            'invoice_number' => $user['invoice_number']
        ]);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    log_error('Payment verification error: ' . $e->getMessage());
    send_json_response(false, 'Payment verification failed. Please contact support.');
}
?>