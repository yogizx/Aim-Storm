<?php
require_once 'includes/auth.php';
require_once __DIR__ . '/../api/email.php';

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
        $_SESSION['error'] = 'User not found';
        header('Location: users.php');
        exit;
    }
    
    // Check if payment is completed
    if ($user['payment_status'] !== 'completed') {
        $_SESSION['error'] = 'Cannot send email - payment not completed';
        header('Location: user-details.php?id=' . $user_id);
        exit;
    }
    
    // Resend email
    $result = send_confirmation_email($user);
    
    if ($result) {
        $_SESSION['success'] = 'Confirmation email resent successfully to ' . $user['email'];
    } else {
        $_SESSION['error'] = 'Failed to send email. Please check email logs.';
    }
    
    header('Location: user-details.php?id=' . $user_id);
    exit;
    
} catch (Exception $e) {
    log_error('Resend email error: ' . $e->getMessage());
    $_SESSION['error'] = 'An error occurred while sending email';
    header('Location: user-details.php?id=' . $user_id);
    exit;
}
?>