<?php
require_once 'includes/auth.php';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = ITEMS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Search
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$params = [];

if (!empty($search)) {
    $where_clause = "WHERE (name LIKE ? OR email LIKE ? OR mobile LIKE ? OR legacy_id LIKE ?)";
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term, $search_term];
}

try {
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM users $where_clause";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_users = $stmt->fetch()['total'];
    $total_pages = ceil($total_users / $per_page);
    
    // Get users
    $query = "SELECT * FROM users $where_clause ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error fetching users: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="content-header">
            <h1>Users Management</h1>
            <p>Manage all MAATKA members</p>
        </div>
        
        <!-- Search & Filter -->
        <div class="toolbar">
            <form method="GET" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by name, email, mobile, or Legacy ID" 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button type="submit" class="btn-search">Search</button>
                <?php if ($search): ?>
                    <a href="users.php" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
            <div class="toolbar-actions">
                <a href="users.php?export=csv" class="btn-export">Export CSV</a>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Legacy ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
                            <th>KRYTA Credits</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No users found</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($user['legacy_id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['mobile']); ?></td>
                                <td><span class="badge badge-gold"><?php echo $user['kryta_credits']; ?></span></td>
                                <td>
                                    <?php if ($user['payment_status'] === 'completed'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo format_date($user['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="user-details.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="View Details">👁️</a>
                                        <a href="resend-email.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="Resend Email">📧</a>
                                    </div>
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
                    <a href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn-page">Previous</a>
                <?php endif; ?>
                
                <span class="page-info">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" class="btn-page">Next</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- After the search form, update toolbar-actions -->
<div class="toolbar-actions">
    <a href="export-csv.php?type=users" class="btn-export">📥 Export CSV</a>
</div>

<?php include 'includes/footer.php'; ?>