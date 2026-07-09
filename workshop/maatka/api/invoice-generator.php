<?php
require_once 'config.php';

// Install: composer require tecnickcom/tcpdf

require_once __DIR__ . '/../vendor/autoload.php';

use TCPDF;

function generate_invoice_pdf($user) {
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator('MAATKA');
    $pdf->SetAuthor('MAATKA');
    $pdf->SetTitle('Invoice - ' . $user['invoice_number']);
    $pdf->SetSubject('MAATKA Membership Invoice');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins
    $pdf->SetMargins(20, 20, 20);
    $pdf->SetAutoPageBreak(TRUE, 20);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 10);
    
    // Invoice HTML
    $html = get_invoice_html($user);
    
    // Print text using writeHTMLCell()
    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Save PDF
    $filename = 'invoice_' . $user['legacy_id'] . '.pdf';
    $filepath = __DIR__ . '/../invoices/' . $filename;
    
    // Create invoices directory if it doesn't exist
    if (!file_exists(__DIR__ . '/../invoices/')) {
        mkdir(__DIR__ . '/../invoices/', 0755, true);
    }
    
    $pdf->Output($filepath, 'F');
    
    // Update database
    global $pdo;
    $stmt = $pdo->prepare("UPDATE invoices SET pdf_path = ? WHERE user_id = ?");
    $stmt->execute([$filename, $user['id']]);
    
    return $filepath;
}

function get_invoice_html($user) {
    $invoice_number = htmlspecialchars($user['invoice_number']);
    $legacy_id = htmlspecialchars($user['legacy_id']);
    $name = htmlspecialchars($user['name']);
    $email = htmlspecialchars($user['email']);
    $mobile = htmlspecialchars($user['mobile']);
    $address = htmlspecialchars($user['address']);
    $date = date('d M Y');
    
    return <<<HTML
<style>
    .header { text-align: center; margin-bottom: 30px; }
    .brand { font-size: 36px; color: #d4af37; font-weight: bold; }
    .tagline { font-size: 12px; color: #666; }
    .invoice-title { font-size: 20px; margin: 20px 0; }
    .details { margin: 20px 0; }
    .detail-row { margin: 5px 0; }
    .label { font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th { background: #d4af37; color: #000; padding: 10px; text-align: left; }
    td { padding: 10px; border-bottom: 1px solid #ddd; }
    .total { font-size: 18px; font-weight: bold; text-align: right; margin: 20px 0; }
    .footer { text-align: center; margin-top: 40px; font-size: 11px; color: #666; }
</style>

<div class="header">
    <div class="brand">MAATKA</div>
    <div class="tagline">Small Contribution. Long-Term Vision.</div>
    <div class="tagline">www.maatka.com</div>
</div>

<div class="invoice-title">
    <strong>INVOICE: {$invoice_number}</strong><br>
    Date: {$date}
</div>

<div class="details">
    <strong>Member Details:</strong><br>
    <div class="detail-row"><span class="label">Name:</span> {$name}</div>
    <div class="detail-row"><span class="label">Email:</span> {$email}</div>
    <div class="detail-row"><span class="label">Mobile:</span> {$mobile}</div>
    <div class="detail-row"><span class="label">Address:</span> {$address}</div>
    <div class="detail-row"><span class="label">Legacy ID:</span> <strong>{$legacy_id}</strong></div>
</div>

<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Quantity</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>MAATKA Digital Membership</td>
            <td>1</td>
            <td>₹900.00</td>
        </tr>
    </tbody>
</table>

<div class="total">
    Total Paid: ₹900.00
</div>

<div class="details">
    <strong>Membership Benefits Credited:</strong><br>
    <div class="detail-row">💎 KRYTA Credits: 100</div>
    <div class="detail-row">🪙 Sudarshana Coin: 1</div>
    <div class="detail-row">🎫 Legacy ID: {$legacy_id}</div>
</div>

<div class="footer">
    <p><strong>Thank you for joining MAATKA</strong></p>
    <p>This is a computer-generated invoice and does not require a signature.</p>
    <p>For support, contact us at support@maatka.com</p>
    <p>MAATKA | www.maatka.com | All contributions are final and non-refundable.</p>
</div>
HTML;
}

?>