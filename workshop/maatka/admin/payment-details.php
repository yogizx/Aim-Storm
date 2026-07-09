<?php
require_once 'includes/auth.php';

$payment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$payment_id) {
    header('Location: payments.php');
    exit;
}

try {
    // Get payment details
    $stmt = $pdo->prepare("
        SELECT p.*, u.name, u.email, u.mobile, u.legacy_id, u.address 
        FROM payments p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        header('Location: payments.php');
        exit;
    }
    
    // Get Razorpay payment details if available
    $razorpay_details = null;
    if ($payment['razorpay_payment_id']) {
        try {
            require_once __DIR__ . '/../api/razorpay-config.php';
            $api = get_razorpay_api();
            $razorpay_details = $api->payment->fetch($payment['razorpay_payment_id']);
        } catch (Exception $e) {
            // Silently fail - Razorpay details optional
        }
    }
    
} catch (PDOException $e) {
    $error = 'Error fetching payment details: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="content-header">
            <h1>Payment Details</h1>
            <p>Complete transaction information</p>
        </div>
        
        <!-- Payment Status Banner -->
        <div class="status-banner status-<?php echo $payment['status']; ?>">
            <h2>
                <?php if ($payment['status'] === 'success'): ?>
                    ✅ Payment Successful
                <?php elseif ($payment['status'] === 'failed'): ?>
                    ❌ Payment Failed
                <?php else: ?>
                    ⏳ Payment <?php echo ucfirst($payment['status']); ?>
                <?php endif; ?>
            </h2>
            <p>Transaction ID: <strong><?php echo htmlspecialchars($payment['razorpay_payment_id'] ?? 'N/A'); ?></strong></p>
        </div>
        
        <!-- Payment Details -->
        <div class="details-grid">
            <div class="detail-card">
                <h3>Transaction Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Payment ID:</span>
                    <span class="detail-value"><code><?php echo htmlspecialchars($payment['razorpay_payment_id'] ?? 'N/A'); ?></code></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span>
                    <span class="detail-value"><code><?php echo htmlspecialchars($payment['razorpay_order_id']); ?></code></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span class="detail-value"><?php echo format_currency($payment['amount']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Currency:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($payment['currency']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <?php if ($payment['status'] === 'success'): ?>
                            <span class="badge badge-success">Success</span>
                        <?php elseif ($payment['status'] === 'failed'): ?>
                            <span class="badge badge-danger">Failed</span>
                        <?php else: ?>
                            <span class="badge badge-warning"><?php echo ucfirst($payment['status']); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Created:</span>
                    <span class="detail-value"><?php echo format_date($payment['created_at']); ?></span>
                </div>
                <?php if ($payment['status'] === 'failed' && $payment['error_description']): ?>
                <div class="detail-row">
                    <span class="detail-label">Error:</span>
                    <span class="detail-value" style="color: #ff6b6b;"><?php echo htmlspecialchars($payment['error_description']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="detail-card">
                <h3>User Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($payment['name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($payment['email']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mobile:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($payment['mobile']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Legacy ID:</span>
                    <span class="detail-value"><strong><?php echo htmlspecialchars($payment['legacy_id']); ?></strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($payment['address']); ?></span>
                </div>
            </div>
        </div>
        
        <?php if ($razorpay_details): ?>
        <!-- Razorpay Details -->
        <div class="table-card">
            <h3>Razorpay Response Data</h3>
            <div class="code-block">
                <pre><?php echo json_encode($razorpay_details, JSON_PRETTY_PRINT); ?></pre>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Actions -->
        <div class="actions-bar">
            <a href="user-details.php?id=<?php echo $payment['user_id']; ?>" class="btn-primary">View User Details</a>
            <?php if ($payment['status'] === 'success'): ?>
                <a href="../invoice.html?id=<?php echo urlencode($payment['legacy_id']); ?>" target="_blank" class="btn-secondary">View Invoice</a>
            <?php endif; ?>
            <a href="payments.php" class="btn-secondary">Back to Payments</a>
        </div>
    </div>
</div>

<style>
.status-banner {
    padding: 30px;
    border-radius: 15px;
    margin-bottom: 40px;
    text-align: center;
    border: 2px solid;
}

.status-banner h2 {
    font-size: 28px;
    margin-bottom: 10px;
}

.status-banner p {
    font-size: 16px;
    opacity: 0.8;
}

.status-success {
    background: rgba(76, 175, 80, 0.1);
    border-color: #4CAF50;
    color: #81c784;
}

.status-failed {
    background: rgba(244, 67, 54, 0.1);
    border-color: #f44336;
    color: #ff6b6b;
}

.status-pending, .status-created {
    background: rgba(255, 152, 0, 0.1);
    border-color: #ff9800;
    color: #ffb74d;
}

.code-block {
    background: #0a0a0a;
    padding: 20px;
    border-radius: 10px;
    overflow-x: auto;
}

.code-block pre {
    color: #d4af37;
    font-size: 13px;
    line-height: 1.6;
    margin: 0;
}
</style>

<?php include 'includes/footer.php'; ?>