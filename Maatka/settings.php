<?php
require_once 'db_config.php';

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_finance':
                setSetting('razorpay_key', $_POST['razorpay_key'], $conn);
                setSetting('razorpay_secret', $_POST['razorpay_secret'], $conn);
                setSetting('gst_percentage', $_POST['gst_percentage'], $conn);
                $message = "Finance settings updated successfully.";
                $messageType = "success";
                break;
            case 'update_company':
                setSetting('company_address', $_POST['company_address'], $conn);
                setSetting('company_gst_no', $_POST['company_gst_no'], $conn);
                $message = "Company information updated successfully.";
                $messageType = "success";
                break;
        }
    }
}

// Fetch current settings
$razorpay_key = getSetting('razorpay_key', $conn) ?: '';
$razorpay_secret = getSetting('razorpay_secret', $conn) ?: '';
$gst_percentage = getSetting('gst_percentage', $conn) ?: '18';
$company_address = getSetting('company_address', $conn) ?: '';
$company_gst_no = getSetting('company_gst_no', $conn) ?: '';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAATKA | Settings</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --black-primary: #0a0a0a;
            --black-secondary: #0f0f0f;
            --black-card: rgba(18, 18, 18, 0.92);
            --gold-primary: #FFD700;
            --gold-secondary: #F4B400;
            --gold-accent: #FFC107;
            --text-light: #ffffff;
            --text-gray: #a0a0a0;
            --success: #00e676;
            --danger: #ff5252;
            --transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--black-primary);
            color: var(--text-light);
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
        }

        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: radial-gradient(circle at 50% 50%, #1a1a1a 0%, #0a0a0a 100%);
        }

        .gold-grid {
            position: absolute;
            width: 200%;
            height: 200%;
            background-image:
                linear-gradient(rgba(255, 215, 0, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 215, 0, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            transform: rotate(-15deg);
            top: -50%;
            left: -50%;
            opacity: 0.5;
        }

        .sidebar {
            width: 280px;
            background: var(--black-card);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 215, 0, 0.1);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        .sidebar-logo {
            font-family: 'Clash Display', sans-serif;
            font-size: 1.8rem;
            color: var(--gold-primary);
            font-weight: 700;
            margin-bottom: 50px;
            padding-left: 10px;
        }

        .sidebar-menu {
            list-style: none;
            flex-grow: 1;
        }

        .menu-item {
            margin-bottom: 15px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            color: var(--text-gray);
            text-decoration: none;
            border-radius: 12px;
            transition: var(--transition);
            font-weight: 500;
        }

        .menu-link:hover,
        .menu-link.active {
            background: rgba(255, 215, 0, 0.1);
            color: var(--gold-primary);
        }

        .menu-link i {
            font-size: 1.1rem;
            width: 20px;
        }

        .logout-btn {
            margin-top: auto;
            color: var(--danger);
            border: 1px solid rgba(255, 82, 82, 0.2);
            background: rgba(255, 82, 82, 0.05);
            padding: 12px;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            font-weight: 600;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: var(--danger);
            color: white;
        }

        .main-content {
            flex-grow: 1;
            margin-left: 280px;
            padding: 40px;
            width: calc(100% - 280px);
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .greeting-box h2 {
            font-family: 'Clash Display', sans-serif;
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .greeting-box p {
            color: var(--text-gray);
        }

        .current-time {
            text-align: right;
            background: rgba(255, 215, 0, 0.05);
            padding: 10px 20px;
            border-radius: 12px;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        .current-time span {
            display: block;
            color: var(--gold-primary);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(0, 230, 118, 0.1);
            color: var(--success);
            border: 1px solid rgba(0, 230, 118, 0.2);
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }

        .settings-card {
            background: var(--black-card);
            border: 1px solid rgba(255, 215, 0, 0.1);
            border-radius: 20px;
            padding: 30px;
        }

        .card-header {
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 15px;
        }

        .card-header h3 {
            font-family: 'Clash Display', sans-serif;
            font-size: 1.2rem;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .card-header p {
            font-size: 0.85rem;
            color: var(--text-gray);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-gray);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px 15px;
            border-radius: 10px;
            color: white;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--gold-primary);
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-save {
            background: var(--gold-primary);
            color: var(--black-primary);
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            width: 100%;
            margin-top: 10px;
        }

        .btn-save:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .gst-split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .split-item span {
            display: block;
            font-size: 0.8rem;
            color: var(--text-gray);
        }

        .split-item strong {
            color: var(--gold-primary);
            font-size: 1rem;
        }

        @media (max-width: 900px) {
            .sidebar { width: 80px; padding: 30px 10px; }
            .sidebar-logo, .menu-link span, .logout-btn span { display: none; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); }
            .settings-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>

<body>
    <div class="bg-animation">
        <div class="gold-grid"></div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">MAATKA</div>
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="admin_panel.php" class="menu-link">
                    <i class="fas fa-th-large"></i>
                    <span>Member Panel</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="settings.php" class="menu-link active">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
        <div class="logout-btn" onclick="window.location.href='index.html'">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout Session</span>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="dashboard-header">
            <div class="greeting-box">
                <h2>Settings</h2>
                <p>Configure ecosystem gateway and company details.</p>
            </div>
            <div class="current-time">
                <span id="timeDisplay">10:00:00 AM</span>
                <p id="dateDisplay">--</p>
            </div>
        </header>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-check-circle"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <div class="settings-grid">
            <!-- Razorpay & Finance Settings -->
            <div class="settings-card">
                <div class="card-header">
                    <h3>Finance & Gateway</h3>
                    <p>Manage Razorpay API keys and GST parameters</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_finance">
                    <div class="form-group">
                        <label>Razorpay Key ID</label>
                        <input type="text" name="razorpay_key" class="form-control" value="<?php echo htmlspecialchars($razorpay_key); ?>" placeholder="rzp_test_...">
                    </div>
                    <div class="form-group">
                        <label>Razorpay Secret Key</label>
                        <input type="password" name="razorpay_secret" class="form-control" value="<?php echo htmlspecialchars($razorpay_secret); ?>" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label>Total GST Percentage (%)</label>
                        <input type="number" step="0.01" name="gst_percentage" id="gstInput" class="form-control" value="<?php echo htmlspecialchars($gst_percentage); ?>" oninput="updateGSTSplit()">
                        <div class="gst-split">
                            <div class="split-item">
                                <span>CGST</span>
                                <strong id="cgstVal">--</strong>
                            </div>
                            <div class="split-item">
                                <span>SGST</span>
                                <strong id="sgstVal">--</strong>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-save">Save Finance Settings</button>
                </form>
            </div>

            <!-- Company Settings -->
            <div class="settings-card">
                <div class="card-header">
                    <h3>Company Information</h3>
                    <p>Details reflected on member invoices</p>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="update_company">
                    <div class="form-group">
                        <label>Company GST Number</label>
                        <input type="text" name="company_gst_no" class="form-control" value="<?php echo htmlspecialchars($company_gst_no); ?>" placeholder="27XXXXX0000X1Z5">
                    </div>
                    <div class="form-group">
                        <label>Company Business Address</label>
                        <textarea name="company_address" class="form-control" rows="4" style="height: auto;"><?php echo htmlspecialchars($company_address); ?></textarea>
                    </div>
                    <button type="submit" class="btn-save">Update Company Info</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        function updateTime() {
            const now = new Date();
            document.getElementById('timeDisplay').textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            document.getElementById('dateDisplay').textContent = now.toLocaleDateString([], { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }

        function updateGSTSplit() {
            const total = parseFloat(document.getElementById('gstInput').value) || 0;
            const split = (total / 2).toFixed(2);
            document.getElementById('cgstVal').textContent = split + '%';
            document.getElementById('sgstVal').textContent = split + '%';
        }

        updateTime();
        setInterval(updateTime, 1000);
        updateGSTSplit();
    </script>
</body>

</html>
