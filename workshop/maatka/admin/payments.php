<?php
require_once 'includes/auth.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Filters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$where_conditions = ['1=1'];
$params = [];

if ($status) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status;
}

if ($date_from) {
    $where_conditions[] = "DATE(p.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $where_conditions[] = "DATE(p.created_at) <= ?";
    $params[] = $date_to;
}

$where_clause = implode(' AND ', $where_conditions);

try {
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM payments p WHERE $where_clause";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_payments = $stmt->fetch()['total'];
    $total_pages = ceil($total_payments / $per_page);
    
    // Get payments
    $query = "
        SELECT p.*, u.name, u.email, u.legacy_id 
        FROM payments p 
        JOIN users u ON p.user_id = u.id 
        WHERE $where_clause 
        ORDER BY p.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
    
    // Get summary stats
    $summary_query = "
        SELECT 
            COUNT(*) as total_count,
            SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as total_revenue,
            SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count
        FROM payments p 
        WHERE $where_clause
    ";
    $stmt = $pdo->prepare($summary_query);
    $stmt->execute($params);
    $summary = $stmt->fetch();
    
} catch (PDOException $e) {
    $error = 'Error fetching payments: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="content-header">
            <h1>Payments Management</h1>
            <p>Track all payment transactions</p>
        </div>
        
        <!-- Summary Cards -->
        <div class="summary-row">
            <div class="summary-card">
                <h4>Total Revenue</h4>
                <p class="summary-value"><?php echo format_currency($summary['total_revenue']); ?></p>
            </div>
            <div class="summary-card">
                <h4>Successful</h4>
                <p class="summary-value"><?php echo number_format($summary['success_count']); ?></p>
            </div>
            <div class="summary-card">
                <h4>Failed</h4>
                <p class="summary-value"><?php echo number_format($summary['failed_count']); ?></p>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="toolbar">
            <form method="GET" class="filter-form">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="success" <?php echo $status === 'success' ? 'selected' : ''; ?>>Success</option>
                    <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                </select>
                
                <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>" placeholder="From Date">
                <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>" placeholder="To Date">
                
                <button type="submit" class="btn-filter">Filter</button>
                <a href="payments.php" class="btn-clear">Clear</a>
            </form>
            
            <div class="toolbar-actions">
                <a href="payments.php?export=csv" class="btn-export">Export CSV</a>
            </div>
        </div>
        
        <!-- Payments Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Legacy ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No payments found</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($payment['razorpay_payment_id']); ?></code></td>
                                <td><strong><?php echo htmlspecialchars($payment['legacy_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($payment['name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['email']); ?></td>
                                <td><?php echo format_currency($payment['amount']); ?></td>
                                <td>
                                    <?php if ($payment['status'] === 'success'): ?>
                                        <span class="badge badge-success">Success</span>
                                    <?php elseif ($payment['status'] === 'failed'): ?>
                                        <span class="badge badge-danger">Failed</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($payment['created_at']); ?></td>
                                <td>
                                    <a href="payment-details.php?id=<?php echo $payment['id']; ?>" class="btn-icon" title="View Details">👁️</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" class="btn-page">Previous</a>
                <?php endif; ?>
                
                <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo http_build_query(array_diff_key($_GET, ['page' => ''])); ?>" class="btn-page">Next</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- After the filter form, update toolbar-actions -->
<div class="toolbar-actions">
    <a href="export-csv.php?type=payments" class="btn-export">📥 Export CSV</a>
</div>

<?php include 'includes/footer.php'; ?>