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
    $where_clause = "WHERE (i.invoice_number LIKE ? OR u.name LIKE ? OR u.legacy_id LIKE ?)";
    $search_term = "%$search%";
    $params = [$search_term, $search_term, $search_term];
}

try {
    // Get total count
    $count_query = "SELECT COUNT(*) as total FROM invoices i JOIN users u ON i.user_id = u.id $where_clause";
    $stmt = $pdo->prepare($count_query);
    $stmt->execute($params);
    $total_invoices = $stmt->fetch()['total'];
    $total_pages = ceil($total_invoices / $per_page);
    
    // Get invoices
    $query = "
        SELECT i.*, u.name, u.email, u.legacy_id 
        FROM invoices i 
        JOIN users u ON i.user_id = u.id 
        $where_clause 
        ORDER BY i.created_at DESC 
        LIMIT $per_page OFFSET $offset
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $invoices = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error fetching invoices: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="content-header">
            <h1>Invoices Management</h1>
            <p>Manage all member invoices</p>
        </div>
        
        <!-- Search -->
        <div class="toolbar">
            <form method="GET" class="search-form">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search by invoice number, name, or Legacy ID" 
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button type="submit" class="btn-search">Search</button>
                <?php if ($search): ?>
                    <a href="invoices.php" class="btn-clear">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Invoices Table -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Invoice Number</th>
                            <th>Legacy ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($invoices)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No invoices found</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($invoice['legacy_id']); ?></code></td>
                                <td><?php echo htmlspecialchars($invoice['name']); ?></td>
                                <td><?php echo htmlspecialchars($invoice['email']); ?></td>
                                <td><?php echo format_currency($invoice['amount']); ?></td>
                                <td><?php echo date('d M Y', strtotime($invoice['invoice_date'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="../invoice.html?id=<?php echo urlencode($invoice['legacy_id']); ?>" target="_blank" class="btn-icon" title="View Invoice">👁️</a>
                                        <?php if ($invoice['pdf_path']): ?>
                                            <a href="../invoices/<?php echo htmlspecialchars($invoice['pdf_path']); ?>" target="_blank" class="btn-icon" title="Download PDF">📄</a>
                                        <?php endif; ?>
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
<!-- After the search form -->
<div class="toolbar-actions">
    <a href="export-csv.php?type=invoices" class="btn-export">📥 Export CSV</a>
</div>

<?php include 'includes/footer.php'; ?>