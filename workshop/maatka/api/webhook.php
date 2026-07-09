<?php
/**
 * Razorpay Webhook Handler
 * Handles asynchronous payment notifications from Razorpay
 */

require_once 'config.php';
require_once 'razorpay-config.php';
require_once 'error-handler.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ErrorHandler::handle('Method not allowed', 405);
}

try {
    // Get webhook payload
    $payload = file_get_contents('php://input');
    
    if (empty($payload)) {
        ErrorHandler::handle('Empty payload', 400);
    }
    
    // Verify webhook signature
    $webhookSecret = 'YOUR_WEBHOOK_SECRET'; // Set this in Razorpay Dashboard
    $webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';
    
    $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);
    
    if (!hash_equals($expectedSignature, $webhookSignature)) {
        ErrorHandler::log('Invalid webhook signature');
        ErrorHandler::handle('Invalid signature', 403);
    }
    
    // Parse payload
    $data = json_decode($payload, true);
    
    if (!$data) {
        ErrorHandler::handle('Invalid JSON', 400);
    }
    
    $event = $data['event'] ?? '';
    $paymentData = $data['payload']['payment']['entity'] ?? [];
    
    // Log webhook event
    $stmt = $pdo->prepare("
        INSERT INTO webhook_logs (event, payload, created_at) 
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$event, $payload]);
    
    // Handle different events
    switch ($event) {
        case 'payment.authorized':
            handlePaymentAuthorized($paymentData);
            break;
            
        case 'payment.captured':
            handlePaymentCaptured($paymentData);
            break;
            
        case 'payment.failed':
            handlePaymentFailed($paymentData);
            break;
            
        case 'order.paid':
            handleOrderPaid($paymentData);
            break;
            
        default:
            // Log unhandled event
            ErrorHandler::log("Unhandled webhook event: $event");
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    ErrorHandler::log('Webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error']);
}

function handlePaymentAuthorized($payment) {
    global $pdo;
    
    $paymentId = $payment['id'];
    $orderId = $payment['order_id'];
    
    // Update payment status
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET status = 'authorized' 
        WHERE razorpay_order_id = ? AND razorpay_payment_id = ?
    ");
    $stmt->execute([$orderId, $paymentId]);
}

function handlePaymentCaptured($payment) {
    global $pdo;
    
    $paymentId = $payment['id'];
    $orderId = $payment['order_id'];
    $amount = $payment['amount'] / 100; // Convert from paise
    
    // This is a backup in case verify.php didn't process it
    $stmt = $pdo->prepare("
        SELECT user_id FROM payments 
        WHERE razorpay_order_id = ? AND status != 'success'
        LIMIT 1
    ");
    $stmt->execute([$orderId]);
    $paymentRecord = $stmt->fetch();
    
    if ($paymentRecord) {
        $userId = $paymentRecord['user_id'];
        
        // Update payment
        $stmt = $pdo->prepare("
            UPDATE payments 
            SET status = 'success', razorpay_payment_id = ? 
            WHERE razorpay_order_id = ?
        ");
        $stmt->execute([$paymentId, $orderId]);
        
        // Credit benefits
        $stmt = $pdo->prepare("
            UPDATE users 
            SET payment_status = 'completed', kryta_credits = 100, sudarshana_coin = 1 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        
        // Send email if not sent
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            require_once 'email.php';
            send_confirmation_email($user);
        }
    }
}

function handlePaymentFailed($payment) {
    global $pdo;
    
    $paymentId = $payment['id'];
    $orderId = $payment['order_id'];
    $errorCode = $payment['error_code'] ?? 'UNKNOWN';
    $errorDescription = $payment['error_description'] ?? 'Payment failed';
    
    // Update payment status
    $stmt = $pdo->prepare("
        UPDATE payments 
        SET status = 'failed', 
            error_code = ?, 
            error_description = ?,
            razorpay_payment_id = ?
        WHERE razorpay_order_id = ?
    ");
    $stmt->execute([$errorCode, $errorDescription, $paymentId, $orderId]);
}

function handleOrderPaid($payment) {
    // Additional handling for order.paid event if needed
    handlePaymentCaptured($payment);
}
?>