<?php
require_once 'db_config.php';

// Simple session-based auth (for demonstration, replace with a real login system)
session_start();
// In a real app, you'd check $_SESSION['admin_logged_in']

// Fetch all users Joined
$query = "SELECT u.*, i.invoice_number, i.amount as paid_amount 
          FROM users u 
          LEFT JOIN invoices i ON u.id = i.user_id 
          ORDER BY u.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAATKA | Admin Dashboard</title>
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
            padding-bottom: 20px;
        }

        h1 {
            font-family: 'Clash Display', sans-serif;
            font-size: 24px;
            color: var(--gold-primary);
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--black-card);
            border: 1px solid rgba(255, 215, 0, 0.1);
            padding: 24px;
            border-radius: 16px;
            text-align: center;
        }

        .stat-val { font-size: 32px; font-weight: bold; color: var(--gold-primary); }
        .stat-label { font-size: 12px; color: var(--text-gray); text-transform: uppercase; }

        .table-container {
            background: var(--black-card);
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(255, 215, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: rgba(255, 215, 0, 0.05);
            padding: 16px 20px;
            font-size: 13px;
            text-transform: uppercase;
            color: var(--gold-primary);
        }

        td {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 14px;
        }

        tr:hover { background: rgba(255, 255, 255, 0.02); }

        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success { background: rgba(0, 230, 118, 0.1); color: var(--success); }
        .badge-pending { background: rgba(255, 215, 0, 0.1); color: var(--gold-primary); }
    </style>
</head>

<body>
    <div class="bg-animation"></div>

    <aside class="sidebar">
        <div class="sidebar-logo">MAATKA</div>
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="admin_panel.php" class="menu-link active">
                    <i class="fas fa-th-large"></i>
                    <span>Member Panel</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="settings.html" class="menu-link">
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

    <main class="main-content">
        <div class="header">
            <h1><i class="fas fa-shield-halved"></i> MAATKA Intelligence</h1>
            <div>
                <a href="index.html" style="color: var(--gold-primary); text-decoration: none; font-size: 14px;">View Public Site</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <?php 
                    $res = $conn->query("SELECT COUNT(*) as total FROM users");
                    $total = $res->fetch_assoc()['total'];
                ?>
                <div class="stat-val"><?php echo $total; ?></div>
                <div class="stat-label">Total Applications</div>
            </div>
            <div class="stat-card">
                <?php 
                    $res = $conn->query("SELECT COUNT(*) as total FROM users WHERE payment_status = 'completed'");
                    $completed = $res->fetch_assoc()['total'];
                ?>
                <div class="stat-val"><?php echo $completed; ?></div>
                <div class="stat-label">Paid Members</div>
            </div>
            <div class="stat-card">
                <?php 
                    $res = $conn->query("SELECT SUM(amount) as total FROM invoices");
                    $revenue = $res->fetch_assoc()['total'] ?: 0;
                ?>
                <div class="stat-val">₹<?php echo number_format($revenue); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <div class="table-container">
            <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Legacy ID</th>
                    <th>Member Name</th>
                    <th>Email / Phone</th>
                    <th>Status</th>
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td><code><?php echo $row['legacy_id']; ?></code></td>
                    <td><strong><?php echo $row['name']; ?></strong></td>
                    <td style="color: var(--text-muted); font-size: 12px;">
                        <?php echo $row['email']; ?><br>
                        <?php echo $row['mobile']; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo $row['payment_status'] == 'completed' ? 'badge-success' : 'badge-pending'; ?>">
                            <?php echo $row['payment_status']; ?>
                        </span>
                    </td>
                    <td>
                        <?php if($row['invoice_number']): ?>
                            <span style="font-size: 12px;"><?php echo $row['invoice_number']; ?></span>
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
