<?php
session_start();
require_once 'includes/config.php';

// Log the logout
if (isset($_SESSION['admin_id'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], 'logout', $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        // Silently fail
    }
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login
header('Location: index.php?logout=1');
exit;
?>