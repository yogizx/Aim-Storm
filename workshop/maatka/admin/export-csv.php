<?php
require_once 'includes/auth.php';

$type = $_GET['type'] ?? 'users';

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=maatka_' . $type . '_' . date('Y-m-d') . '.csv');

// Create output stream
$output = fopen('php://output', 'w');

try {
    switch ($type) {
        case 'users':
            exportUsers($output);
            break;
            
        case 'payments':
            exportPayments($output);
            break;
            
        case 'invoices':
            exportInvoices($output);
            break;
            
        default:
            fputcsv($output, ['Error: Invalid export type']);
    }
} catch (Exception $e) {
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}

fclose($output);
exit;

function exportUsers($output) {
    global $pdo;
    
    // CSV Headers
    fputcsv($output, [
        'Legacy ID',
        'Name',
        'Email',
        'Mobile',
        'Address',
        'KRYTA Credits',
        'Sudarshana Coin',
        'Payment Status',
        'Invoice Number',
        'Joined Date'
    ]);
    
    // Get all users
    $stmt = $pdo->query("
        SELECT 
            legacy_id, name, email, mobile, address,
            kryta_credits, sudarshana_coin, payment_status,
            invoice_number, created_at
        FROM users
        ORDER BY created_at DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['legacy_id'],
            $row['name'],
            $row['email'],
            $row['mobile'],
            $row['address'],
            $row['kryta_credits'],
            $row['sudarshana_coin'],
            $row['payment_status'],
            $row['invoice_number'],
            date('Y-m-d H:i:s', strtotime($row['created_at']))
        ]);
    }
}

function exportPayments($output) {
    global $pdo;
    
    // CSV Headers
    fputcsv($output, [
        'Payment ID',
        'Order ID',
        'Legacy ID',
        'Name',
        'Email',
        'Amount',
        'Status',
        'Payment Method',
        'Date'
    ]);
    
    // Get all payments
    $stmt = $pdo->query("
        SELECT 
            p.razorpay_payment_id, p.razorpay_order_id,
            u.legacy_id, u.name, u.email,
            p.amount, p.status, p.payment_method, p.created_at
        FROM payments p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['razorpay_payment_id'] ?? 'N/A',
            $row['razorpay_order_id'],
            $row['legacy_id'],
            $row['name'],
            $row['email'],
            $row['amount'],
            $row['status'],
            $row['payment_method'] ?? 'N/A',
            date('Y-m-d H:i:s', strtotime($row['created_at']))
        ]);
    }
}

function exportInvoices($output) {
    global $pdo;
    
    // CSV Headers
    fputcsv($output, [
        'Invoice Number',
        'Legacy ID',
        'Name',
        'Email',
        'Amount',
        'Invoice Date',
        'PDF Available'
    ]);
    
    // Get all invoices
    $stmt = $pdo->query("
        SELECT 
            i.invoice_number, u.legacy_id, u.name, u.email,
            i.amount, i.invoice_date, i.pdf_path
        FROM invoices i
        JOIN users u ON i.user_id = u.id
        ORDER BY i.created_at DESC
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['invoice_number'],
            $row['legacy_id'],
            $row['name'],
            $row['email'],
            $row['amount'],
            $row['invoice_date'],
            $row['pdf_path'] ? 'Yes' : 'No'
        ]);
    }
}
?>