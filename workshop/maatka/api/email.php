<?php
// Email System using PHPMailer
// Install: composer require phpmailer/phpmailer

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_confirmation_email($user) {
    global $pdo;
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($user['email'], $user['name']);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to MAATKA - Your Membership is Confirmed';
        $mail->Body = get_email_template($user);
        $mail->AltBody = get_email_text_template($user);
        
        // Send
        $mail->send();
        
        // Log success
        $stmt = $pdo->prepare("
            INSERT INTO email_logs (user_id, email_to, subject, status) 
            VALUES (?, ?, ?, 'sent')
        ");
        $stmt->execute([$user['id'], $user['email'], $mail->Subject]);
        
        return true;
        
    } catch (Exception $e) {
        // Log failure
        $stmt = $pdo->prepare("
            INSERT INTO email_logs (user_id, email_to, subject, status, error_message) 
            VALUES (?, ?, ?, 'failed', ?)
        ");
        $stmt->execute([$user['id'], $user['email'], 'Welcome Email', $mail->ErrorInfo]);
        
        log_error('Email sending failed: ' . $mail->ErrorInfo);
        return false;
    }
}

function get_email_template($user) {
    $legacy_id = htmlspecialchars($user['legacy_id']);
    $name = htmlspecialchars($user['name']);
    $invoice_number = htmlspecialchars($user['invoice_number']);
    $invoice_link = SITE_URL . '/invoice.html?id=' . urlencode($legacy_id);
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #1a1a1a;
            padding: 0;
        }
        .header {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            padding: 40px 30px;
            text-align: center;
            border-bottom: 3px solid #d4af37;
        }
        .header h1 {
            color: #d4af37;
            font-size: 48px;
            margin: 0 0 10px 0;
        }
        .header p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
        }
        .welcome {
            font-size: 24px;
            color: #d4af37;
            margin-bottom: 20px;
        }
        .message {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.8;
            margin-bottom: 30px;
        }
        .benefits-box {
            background: #2a2a2a;
            padding: 30px;
            border-radius: 10px;
            border: 2px solid #d4af37;
            margin: 30px 0;
        }
        .benefits-title {
            color: #d4af37;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .benefit-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }
        .benefit-item:last-child {
            border-bottom: none;
        }
        .benefit-label {
            color: rgba(255, 255, 255, 0.7);
        }
        .benefit-value {
            color: #d4af37;
            font-weight: bold;
        }
        .legacy-box {
            background: #0a0a0a;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
        }
        .legacy-label {
            color: rgba(255, 255, 255, 0.6);
            font-size: 12px;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }
        .legacy-id {
            color: #d4af37;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            background: #d4af37;
            color: #0a0a0a;
            text-decoration: none;
            border-radius: 30px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            background: #0a0a0a;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #2a2a2a;
        }
        .footer p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>MAATKA</h1>
            <p>Small Contribution. Long-Term Vision.</p>
        </div>
        
        <div class="content">
            <h2 class="welcome">Welcome to MAATKA, {$name}!</h2>
            
            <p class="message">
                Your membership is confirmed. You've taken a meaningful step toward the future. 
                Thank you for believing in our vision.
            </p>
            
            <div class="benefits-box">
                <h3 class="benefits-title">Your Membership Benefits</h3>
                <div class="benefit-item">
                    <span class="benefit-label">💎 KRYTA Credits</span>
                    <span class="benefit-value">100 Credits</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-label">🪙 Sudarshana Coin</span>
                    <span class="benefit-value">1 Coin</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-label">🎫 Legacy ID</span>
                    <span class="benefit-value">{$legacy_id}</span>
                </div>
                <div class="benefit-item">
                    <span class="benefit-label">📄 Invoice Number</span>
                    <span class="benefit-value">{$invoice_number}</span>
                </div>
            </div>
            
            <div class="legacy-box">
                <p class="legacy-label">YOUR MAATKA LEGACY ID</p>
                <p class="legacy-id">{$legacy_id}</p>
            </div>
            
            <p class="message">
                Keep your Legacy ID safe. It's your permanent MAATKA identifier and may be used 
                for future platform features.
            </p>
            
            <center>
                <a href="{$invoice_link}" class="button">Download Invoice</a>
            </center>
            
            <p class="message" style="margin-top: 30px; font-size: 14px;">
                <strong>Important:</strong> All membership benefits have been credited to your account. 
                KRYTA Credits are internal digital credits with no current monetary value. 
                The Sudarshana Coin is a symbolic token representing goodwill.
            </p>
        </div>
        
        <div class="footer">
            <p><strong>MAATKA</strong></p>
            <p>Small Contribution. Long-Term Vision.</p>
            <p>www.maatka.com | support@maatka.com</p>
            <p style="margin-top: 20px; font-size: 11px;">
                This is an automated email. Please do not reply directly to this message.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}

function get_email_text_template($user) {
    $legacy_id = $user['legacy_id'];
    $name = $user['name'];
    $invoice_number = $user['invoice_number'];
    $invoice_link = SITE_URL . '/invoice.html?id=' . urlencode($legacy_id);
    
    return <<<TEXT
MAATKA - Welcome to Your Journey

Dear {$name},

Your membership is confirmed! You've taken a meaningful step toward the future.

YOUR MEMBERSHIP BENEFITS:
- 100 KRYTA Credits
- 1 Sudarshana Coin (Symbolic)
- 1 Unique Legacy ID: {$legacy_id}
- Invoice Number: {$invoice_number}

YOUR LEGACY ID: {$legacy_id}

Keep this ID safe. It's your permanent MAATKA identifier.

Download your invoice: {$invoice_link}

Thank you for believing in our vision.

Best regards,
MAATKA Team

www.maatka.com
support@maatka.com
TEXT;
}

function resend_confirmation_email($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return false;
    }
    
    return send_confirmation_email($user);
}

?>