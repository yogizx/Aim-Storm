<?php
require_once 'includes/auth.php';

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $action = $_POST['action'] ?? '';
        
        try {
            switch ($action) {
                case 'update_profile':
                    $name = sanitize_input($_POST['name']);
                    $email = sanitize_input($_POST['email']);
                    
                    $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $admin_id]);
                    
                    $_SESSION['admin_name'] = $name;
                    $success = 'Profile updated successfully';
                    break;
                    
                case 'change_password':
                    $current_password = $_POST['current_password'];
                    $new_password = $_POST['new_password'];
                    $confirm_password = $_POST['confirm_password'];
                    
                    // Verify current password
                    $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = ?");
                    $stmt->execute([$admin_id]);
                    $admin = $stmt->fetch();
                    
                    if (!password_verify($current_password, $admin['password_hash'])) {
                        $error = 'Current password is incorrect';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'New passwords do not match';
                    } elseif (strlen($new_password) < 8) {
                        $error = 'Password must be at least 8 characters';
                    } else {
                        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$new_hash, $admin_id]);
                        
                        $success = 'Password changed successfully';
                        
                        // Log password change
                        $logStmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
                        $logStmt->execute([$admin_id, 'password_change', $_SERVER['REMOTE_ADDR']]);
                    }
                    break;
                    
                case 'update_site_settings':
                    $site_name = sanitize_input($_POST['site_name']);
                    $site_email = sanitize_input($_POST['site_email']);
                    $razorpay_key = sanitize_input($_POST['razorpay_key']);
                    $razorpay_secret = sanitize_input($_POST['razorpay_secret']);
                    
                    // Update or insert settings
                    $settings = [
                        'site_name' => $site_name,
                        'site_email' => $site_email,
                        'razorpay_key' => $razorpay_key,
                        'razorpay_secret' => $razorpay_secret
                    ];
                    
                    foreach ($settings as $key => $value) {
                        $stmt = $pdo->prepare("
                            INSERT INTO settings (setting_key, setting_value) 
                            VALUES (?, ?) 
                            ON DUPLICATE KEY UPDATE setting_value = ?
                        ");
                        $stmt->execute([$key, $value, $value]);
                    }
                    
                    $success = 'Site settings updated successfully';
                    break;
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch current settings
try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_data = $stmt->fetch();
    
    // Fetch site settings
    $stmt = $pdo->query("SELECT * FROM settings");
    $site_settings = [];
    while ($row = $stmt->fetch()) {
        $site_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $error = 'Error fetching settings: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="content-header">
            <h1>Settings</h1>
            <p>Manage admin and system settings</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <!-- Settings Tabs -->
        <div class="settings-tabs">
            <button class="tab-btn active" data-tab="profile">Profile</button>
            <button class="tab-btn" data-tab="password">Password</button>
            <button class="tab-btn" data-tab="site">Site Settings</button>
            <button class="tab-btn" data-tab="logs">Activity Logs</button>
        </div>
        
        <!-- Profile Settings -->
        <div class="tab-content active" id="profile">
            <div class="settings-card">
                <h3>Admin Profile</h3>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($admin_data['username']); ?>" disabled>
                        <small>Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($admin_data['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($admin_data['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Login</label>
                        <input type="text" value="<?php echo format_date($admin_data['last_login']); ?>" disabled>
                    </div>
                    
                    <button type="submit" class="btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
        
        <!-- Password Settings -->
        <div class="tab-content" id="password">
            <div class="settings-card">
                <h3>Change Password</h3>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required minlength="8">
                        <small>Minimum 8 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="8">
                    </div>
                    
                    <button type="submit" class="btn-primary">Change Password</button>
                </form>
            </div>
        </div>
        
        <!-- Site Settings -->
        <div class="tab-content" id="site">
            <div class="settings-card">
                <h3>Site Configuration</h3>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="update_site_settings">
                    
                    <div class="form-group">
                        <label>Site Name</label>
                        <input type="text" name="site_name" value="<?php echo htmlspecialchars($site_settings['site_name'] ?? 'MAATKA'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Site Email</label>
                        <input type="email" name="site_email" value="<?php echo htmlspecialchars($site_settings['site_email'] ?? 'support@maatka.com'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Razorpay Key ID</label>
                        <input type="text" name="razorpay_key" value="<?php echo htmlspecialchars($site_settings['razorpay_key'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Razorpay Secret Key</label>
                        <input type="password" name="razorpay_secret" value="<?php echo htmlspecialchars($site_settings['razorpay_secret'] ?? ''); ?>" required>
                    </div>
                    
                    <button type="submit" class="btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
        
        <!-- Activity Logs -->
        <div class="tab-content" id="logs">
            <div class="settings-card">
                <h3>Recent Activity</h3>
                <?php
                $stmt = $pdo->prepare("
                    SELECT al.*, a.username 
                    FROM admin_logs al 
                    LEFT JOIN admins a ON al.admin_id = a.id 
                    ORDER BY al.created_at DESC 
                    LIMIT 50
                ");
                $stmt->execute();
                $logs = $stmt->fetchAll();
                ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo format_date($log['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                <td><span class="badge badge-info"><?php echo htmlspecialchars($log['action']); ?></span></td>
                                <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                <td><?php echo htmlspecialchars($log['details'] ?? '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Remove active class from all
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Add active to clicked
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});
</script>

<?php include 'includes/footer.php'; ?>