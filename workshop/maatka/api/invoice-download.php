<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die('Method not allowed');
}

try {
    $legacy_id = $_GET['id'] ?? '';
    
    if (empty($legacy_id)) {
        http_response_code(400);
        die('Legacy ID is required');
    }
    
    // Get user details
    $stmt = $pdo->prepare("
        SELECT u.*, i.pdf_path 
        FROM users u 
        LEFT JOIN invoices i ON u.id = i.user_id
        WHERE u.legacy_id = ? AND u.payment_status = 'completed'
        LIMIT 1
    ");
    $stmt->execute([$legacy_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        die('Invoice not found');
    }
    
    // Check if PDF already exists
    if ($user['pdf_path'] && file_exists(__DIR__ . '/../invoices/' . $user['pdf_path'])) {
        $pdf_path = __DIR__ . '/../invoices/' . $user['pdf_path'];
    } else {
        // Generate PDF on the fly
        require_once 'invoice-generator.php';
        $pdf_path = generate_invoice_pdf($user);
    }
    
    // Serve PDF file
    if (file_exists($pdf_path)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="MAATKA_Invoice_' . $user['legacy_id'] . '.pdf"');
        header('Content-Length: ' . filesize($pdf_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        readfile($pdf_path);
        exit;
    } else {
        http_response_code(500);
        die('Invoice file not found');
    }
    
} catch (Exception $e) {
    log_error('Invoice download error: ' . $e->getMessage());
    http_response_code(500);
    die('Error downloading invoice');
}
?>