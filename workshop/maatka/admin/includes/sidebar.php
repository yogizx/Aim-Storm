<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2>MAATKA</h2>
        <p>Admin Panel</p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <a href="users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
            <span class="nav-icon">👥</span>
            <span class="nav-text">Users</span>
        </a>
        
        <a href="payments.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'payments.php' ? 'active' : ''; ?>">
            <span class="nav-icon">💳</span>
            <span class="nav-text">Payments</span>
        </a>
        
        <a href="invoices.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'invoices.php' ? 'active' : ''; ?>">
            <span class="nav-icon">📄</span>
            <span class="nav-text">Invoices</span>
        </a>
        
        <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
            <span class="nav-icon">⚙️</span>
            <span class="nav-text">Settings</span>
        </a>
        
        <a href="logout.php" class="nav-item nav-logout">
            <span class="nav-icon">🚪</span>
            <span class="nav-text">Logout</span>
        </a>
    </nav>
    
    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar">
                <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
            </div>
            <div class="admin-details">
                <p class="admin-name"><?php echo htmlspecialchars($admin_name); ?></p>
                <p class="admin-role">Administrator</p>
            </div>
        </div>
    </div>
</aside>