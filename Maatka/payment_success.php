<?php
require_once 'db_config.php';

$oid = $_GET['oid'] ?? '';
$pid = $_GET['pid'] ?? '';
$sig = $_GET['sig'] ?? '';

$success = false;
$message = "Verifying your payment...";

if (!empty($oid) && !empty($pid)) {
    // In a production environment, you should verify the signature here using Razorpay SDK
    // For this implementation, we will update the payment status based on the received IDs
    
    // Update payment record
    $stmt = $conn->prepare("UPDATE payments SET razorpay_payment_id = ?, razorpay_signature = ?, status = 'success' WHERE razorpay_order_id = ?");
    $stmt->bind_param("sss", $pid, $sig, $oid);
    
    if ($stmt->execute()) {
        // Get user details associated with this order
        $stmt_user = $conn->prepare("SELECT id, name, email, legacy_id FROM users WHERE id = (SELECT user_id FROM payments WHERE razorpay_order_id = ?)");
        $stmt_user->bind_param("s", $oid);
        $stmt_user->execute();
        $res = $stmt_user->get_result();
        
        if ($row = $res->fetch_assoc()) {
            $user_id = $row['id'];
            $user_name = $row['name'];
            $user_email = $row['email'];
            $legacy_id = $row['legacy_id'];
            
            // 1. Update user status
            $stmt_update_user = $conn->prepare("UPDATE users SET payment_status = 'completed' WHERE id = ?");
            $stmt_update_user->bind_param("i", $user_id);
            $stmt_update_user->execute();

            // 2. Generate Invoice
            $invoice_num = 'INV-' . strtoupper(substr(md5(time() . $user_id), 0, 8));
            $invoice_date = date('Y-m-d');
            $amount = 900.00;
            
            $stmt_inv = $conn->prepare("INSERT INTO invoices (user_id, invoice_number, invoice_date, amount) VALUES (?, ?, ?, ?)");
            $stmt_inv->bind_param("issd", $user_id, $invoice_num, $invoice_date, $amount);
            $stmt_inv->execute();

            // 3. Send Confirmation Email
            $subject = "Welcome to MAATKA - Your Membership is Confirmed!";
            $to = $user_email;
            
            $headers = "From: MAATKA Support <support@maatka.com>\r\n";
            $headers .= "Reply-To: support@maatka.com\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            
            $email_body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #FFD700;'>
                <h2 style='color: #F4B400;'>Welcome, $user_name!</h2>
                <p>Your payment of <strong>₹900</strong> has been successfully received.</p>
                <div style='background: #fbfbfb; padding: 15px; border-radius: 8px;'>
                    <p><strong>Your MAATKA Legacy ID:</strong> <span style='color: #F4B400; font-weight: bold;'>$legacy_id</span></p>
                    <p><strong>Invoice Number:</strong> $invoice_num</p>
                    <p><strong>Status:</strong> Premium Member</p>
                </div>
                <p>Your 100 KRITIKA Credits and 1 Sudarshana Coin have been added to your profile.</p>
                <p>You can now log in to the members portal using your email and Legacy ID.</p>
                <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
                <p style='font-size: 12px; color: #888;'>This is an automated message from MAATKA Operations.</p>
            </div>
            ";
            
            $mail_status = 'failed';
            if (mail($to, $subject, $email_body, $headers)) {
                $mail_status = 'sent';
            }
            
            // 4. Log Email in DB
            $err_msg = ($mail_status == 'failed') ? 'Mail server rejected.' : NULL;
            $stmt_email_log = $conn->prepare("INSERT INTO email_logs (user_id, email_to, subject, status, error_message) VALUES (?, ?, ?, ?, ?)");
            $stmt_email_log->bind_param("issss", $user_id, $user_email, $subject, $mail_status, $err_msg);
            $stmt_email_log->execute();
            
            $success = true;
            $message = "Payment Successful! Welcome to the MAATKA lifestyle. Your Legacy ID is $legacy_id. Check your email for the invoice.";
        }
    } else {
        $message = "There was an error updating your records. Please contact support.";
    }
} else {
    $message = "Invalid request or payment failed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status | MAATKA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --black-primary: #0a0a0a;
            --gold-primary: #FFD700;
            --gold-gradient: linear-gradient(135deg, #FFD700 0%, #F4B400 50%, #FFC107 100%);
        }
        body {
            background-color: var(--black-primary);
            color: white;
            font-family: 'Inter', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .success-card {
            background: rgba(20, 20, 20, 0.8);
            border: 1px solid rgba(255, 215, 0, 0.2);
            padding: 60px;
            border-radius: 24px;
            max-width: 500px;
            backdrop-filter: blur(20px);
            box-shadow: 0 0 50px rgba(0,0,0,0.5);
        }
        .success-icon {
            font-size: 80px;
            color: var(--gold-primary);
            margin-bottom: 30px;
        }
        h1 { font-family: 'Clash Display', sans-serif; font-size: 2.5rem; margin-bottom: 20px; }
        p { color: #b0b0b0; line-height: 1.6; margin-bottom: 40px; }
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: var(--gold-gradient);
            color: black;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 700;
            transition: 0.3s;
        }
        .btn:hover { transform: scale(1.05); }
    </style>
</head>
<body>
    <div class="success-card">
        <?php if ($success): ?>
            <div class="success-icon"><i class="fas fa-check-circle"></i></div>
            <h1>Application Success</h1>
            <p><?php echo $message; ?></p>
            <a href="index.html" class="btn">Go to Dashboard</a>
        <?php else: ?>
            <div class="success-icon" style="color: #ff5050;"><i class="fas fa-times-circle"></i></div>
            <h1>Payment Error</h1>
            <p><?php echo $message; ?></p>
            <a href="join.html" class="btn">Retry Application</a>
        <?php endif; ?>
    </div>
</body>
</html>
