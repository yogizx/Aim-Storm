<?php
require_once 'includes/auth.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header('Location: users.php');
    exit;
}

try {
    // Get user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: users.php');
        exit;
    }
    
    // Get payment details
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $payments = $stmt->fetchAll();
    
    // Get email logs
    $stmt = $pdo->prepare("SELECT * FROM email_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$user_id]);
    $email_logs = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error fetching user details: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="content-header">
            <h1>User Details</h1>
            <p>Complete information for <?php echo htmlspecialchars($user['name']); ?></p>
        </div>
        
        <!-- User Info Card -->
        <div class="details-grid">
            <div class="detail-card">
                <h3>Personal Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Legacy ID:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['legacy_id']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Full Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mobile:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['mobile']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['address']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Joined:</span>
                    <span class="detail-value"><?php echo format_date($user['created_at']); ?></span>
                </div>
            </div>
            
            <div class="detail-card">
                <h3>Membership Benefits</h3>
                <div class="detail-row">
                    <span class="detail-label">KRYTA Credits:</span>
                    <span class="detail-value"><?php echo $user['kryta_credits']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Sudarshana Coin:</span>
                    <span class="detail-value"><?php echo $user['sudarshana_coin']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Invoice Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($user['invoice_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Status:</span>
                    <span class="detail-value">
                        <?php if ($user['payment_status'] === 'completed'): ?>
                            <span class="badge badge-success">Completed</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Payment History -->
        <div class="table-card">
            <h3>Payment History</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No payment records</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($payment['razorpay_payment_id'] ?? 'N/A'); ?></code></td>
                                <td><code><?php echo htmlspecialchars($payment['razorpay_order_id']); ?></code></td>
                                <td><?php echo format_currency($payment['amount']); ?></td>
                                <td>
                                    <?php if ($payment['status'] === 'success'): ?>
                                        <span class="badge badge-success">Success</span>
                                    <?php elseif ($payment['status'] === 'failed'): ?>
                                        <span class="badge badge-danger">Failed</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><?php echo ucfirst($payment['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></td>
                                <td><?php echo format_date($payment['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Email Logs -->
        <div class="table-card">
            <h3>Email History</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>To</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($email_logs)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No email records</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($email_logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['subject']); ?></td>
                                <td><?php echo htmlspecialchars($log['email_to']); ?></td>
                                <td>
                                    <?php if ($log['status'] === 'sent'): ?>
                                        <span class="badge badge-success">Sent</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Failed</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($log['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="actions-bar">
            <a href="resend-email.php?id=<?php echo $user['id']; ?>" class="btn-primary">Resend Confirmation Email</a>
            <a href="../invoice.html?id=<?php echo urlencode($user['legacy_id']); ?>" target="_blank" class="btn-secondary">View Invoice</a>
            <a href="users.php" class="btn-secondary">Back to Users</a>
        </div>
    </div>
</div>

<style>
.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.detail-card {
    background: var(--color-gray);
    padding: 30px;
    border-radius: 15px;
    border: 1px solid var(--color-gray-light);
}

.detail-card h3 {
    color: var(--color-primary);
    font-size: 20px;
    margin-bottom: 25px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--color-gray-light);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: rgba(255, 255, 255, 0.6);
    font-size: 14px;
}

.detail-value {
    color: rgba(255, 255, 255, 0.9);
    font-weight: 600;
}

.actions-bar {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    flex-wrap: wrap;
}
</style>

<?php include 'includes/footer.php'; ?>