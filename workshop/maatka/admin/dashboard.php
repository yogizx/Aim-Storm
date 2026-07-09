<?php
require_once 'includes/auth.php';

// Get statistics
try {
    // Total Members
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE payment_status = 'completed'");
    $total_members = $stmt->fetch()['total'];
    
    // Total Revenue
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'success'");
    $total_revenue = $stmt->fetch()['total'] ?? 0;
    
    // Today's Members
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE() AND payment_status = 'completed'");
    $today_members = $stmt->fetch()['total'];
    
    // This Month's Revenue
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'success'");
    $month_revenue = $stmt->fetch()['total'] ?? 0;
    
    // Recent Members
    $stmt = $pdo->query("SELECT * FROM users WHERE payment_status = 'completed' ORDER BY created_at DESC LIMIT 10");
    $recent_members = $stmt->fetchAll();
    
    // Recent Payments
    $stmt = $pdo->query("SELECT p.*, u.name, u.email FROM payments p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 10");
    $recent_payments = $stmt->fetchAll();
    
    // Monthly Stats (last 6 months)
    $stmt = $pdo->query("
        SELECT 
            DATE_FORMAT(created_at, '%b %Y') as month,
            COUNT(*) as members,
            SUM(amount) as revenue
        FROM payments 
        WHERE status = 'success' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY YEAR(created_at), MONTH(created_at)
        ORDER BY created_at ASC
    ");
    $monthly_stats = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error fetching statistics: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="content-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($admin_name); ?>!</p>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card gold">
                <div class="stat-icon">👥</div>
                <div class="stat-details">
                    <h3><?php echo number_format($total_members); ?></h3>
                    <p>Total Members</p>
                </div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon">💰</div>
                <div class="stat-details">
                    <h3><?php echo format_currency($total_revenue); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            
            <div class="stat-card blue">
                <div class="stat-icon">📈</div>
                <div class="stat-details">
                    <h3><?php echo number_format($today_members); ?></h3>
                    <p>Today's Members</p>
                </div>
            </div>
            
            <div class="stat-card purple">
                <div class="stat-icon">📊</div>
                <div class="stat-details">
                    <h3><?php echo format_currency($month_revenue); ?></h3>
                    <p>This Month</p>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="charts-row">
            <div class="chart-card">
                <h3>Monthly Growth</h3>
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="activity-row">
            <!-- Recent Members -->
            <div class="activity-card">
                <h3>Recent Members</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Legacy ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_members as $member): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($member['legacy_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo format_date($member['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="users.php" class="view-all">View All Members →</a>
            </div>
            
            <!-- Recent Payments -->
            <div class="activity-card">
                <h3>Recent Payments</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['name']); ?></td>
                                <td><?php echo format_currency($payment['amount']); ?></td>
                                <td><span class="badge badge-success">Success</span></td>
                                <td><?php echo format_date($payment['created_at']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <a href="payments.php" class="view-all">View All Payments →</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly Growth Chart
const ctx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($monthly_stats, 'month')); ?>,
        datasets: [{
            label: 'Members',
            data: <?php echo json_encode(array_column($monthly_stats, 'members')); ?>,
            borderColor: '#d4af37',
            backgroundColor: 'rgba(212, 175, 55, 0.1)',
            tension: 0.4
        }, {
            label: 'Revenue (₹)',
            data: <?php echo json_encode(array_column($monthly_stats, 'revenue')); ?>,
            borderColor: '#4CAF50',
            backgroundColor: 'rgba(76, 175, 80, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>