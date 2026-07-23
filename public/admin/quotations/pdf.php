<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| KVN CONSTRUCTION PLATFORM
|--------------------------------------------------------------------------
| QUOTATION PDF GENERATOR
|--------------------------------------------------------------------------
| File: /public/admin/quotations/pdf.php
|--------------------------------------------------------------------------
| Generates a PDF contract/quotation for a project estimate.
| Uses a lightweight HTML-to-PDF approach without external libraries.
|--------------------------------------------------------------------------
*/

require_once '../../../config/app.php';
require_once '../../../middleware/admin.php';
require_once '../../../helpers/security.php';
require_once '../../../helpers/formatter.php';

/*
|--------------------------------------------------------------------------
| QUOTATION ID
|--------------------------------------------------------------------------
*/

$quotationId = (int) ($_GET['id'] ?? 0);

if ($quotationId <= 0) {
    die('Invalid quotation ID.');
}

/*
|--------------------------------------------------------------------------
| FETCH QUOTATION
|--------------------------------------------------------------------------
*/

try {
    $query = "
        SELECT q.*, c.name AS client_name, c.email AS client_email, 
               c.phone AS client_phone, c.address AS client_address,
               u.full_name AS created_by_name
        FROM quotations q
        LEFT JOIN clients c ON q.client_id = c.id
        LEFT JOIN users u ON q.created_by = u.id
        WHERE q.id = :id
        LIMIT 1
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([':id' => $quotationId]);
    $quotation = $stmt->fetch();

    if (!$quotation) {
        die('Quotation not found.');
    }
} catch (Exception $e) {
    die('Failed to load quotation: ' . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| FETCH QUOTATION ITEMS
|--------------------------------------------------------------------------
*/

$items = [];
try {
    $itemQuery = "
        SELECT * FROM quotation_items
        WHERE quotation_id = :quotation_id
        ORDER BY id ASC
    ";
    $itemStmt = $conn->prepare($itemQuery);
    $itemStmt->execute([':quotation_id' => $quotationId]);
    $items = $itemStmt->fetchAll();
} catch (Exception $e) {
    error_log('Quotation items fetch error: ' . $e->getMessage());
}

/*
|--------------------------------------------------------------------------
| CALCULATE TOTALS
|--------------------------------------------------------------------------
*/

$subtotal = 0;
foreach ($items as $item) {
    $subtotal += (float) ($item['total'] ?? ($item['quantity'] * $item['unit_price']));
}

$gstRate = 5; // 5% GST for construction
$gstAmount = $subtotal * ($gstRate / 100);
$grandTotal = $subtotal + $gstAmount;

// If quotation has saved totals, use those instead
if (!empty($quotation['subtotal'])) $subtotal = (float) $quotation['subtotal'];
if (!empty($quotation['tax_amount'])) $gstAmount = (float) $quotation['tax_amount'];
if (!empty($quotation['total_amount'])) $grandTotal = (float) $quotation['total_amount'];

/*
|--------------------------------------------------------------------------
| GENERATE QUOTATION NUMBER
|--------------------------------------------------------------------------
*/

$quotationNumber = 'KVN/QT/' . str_pad((string)$quotationId, 5, '0', STR_PAD_LEFT) . '/' . date('Y');

/*
|--------------------------------------------------------------------------
| SET HEADERS FOR PDF DOWNLOAD
|--------------------------------------------------------------------------
*/

$fileName = 'Quotation_' . $quotationNumber . '.pdf';

// Use Content-Type application/pdf and Content-Disposition for download
// This works with the browser's built-in PDF viewer
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $fileName . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Check if TCPDF or DomPDF is available
$tcpdfPath = ROOT_PATH . '/vendor/tecnickcom/tcpdf/tcpdf.php';
$dompdfPath = ROOT_PATH . '/vendor/dompdf/dompdf/autoload.inc.php';
$mpdfPath = ROOT_PATH . '/vendor/mpdf/mpdf/mpdf.php';

$pdfLibrary = null;
if (file_exists($tcpdfPath)) {
    $pdfLibrary = 'tcpdf';
} elseif (file_exists($dompdfPath)) {
    $pdfLibrary = 'dompdf';
} elseif (file_exists($mpdfPath)) {
    $pdfLibrary = 'mpdf';
}

if ($pdfLibrary === 'tcpdf') {
    // Use TCPDF
    require_once $tcpdfPath;
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator(APP_NAME);
    $pdf->SetAuthor(APP_NAME);
    $pdf->SetTitle('Quotation ' . $quotationNumber);
    $pdf->SetSubject('Construction Quotation');
    
    $pdf->setHeaderFont(['helvetica', '', 10]);
    $pdf->setFooterFont(['helvetica', '', 8]);
    
    $pdf->SetDefaultMonospacedFont('courier');
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    
    $pdf->setImageScale(1.25);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    
    $pdf->AddPage();
    
    // Generate HTML content
    $html = generateQuotationHTML($quotation, $quotationNumber, $items, $subtotal, $gstAmount, $grandTotal, $gstRate);
    
    $pdf->writeHTML($html, true, false, true, false, '');
    
    $pdf->Output($fileName, 'I');
    
} elseif ($pdfLibrary === 'dompdf') {
    // DomPDF
    require_once $dompdfPath;
    
    $dompdf = new Dompdf\Dompdf();
    $html = generateQuotationHTML($quotation, $quotationNumber, $items, $subtotal, $gstAmount, $grandTotal, $gstRate);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($fileName, ['Attachment' => false]);
    
} elseif ($pdfLibrary === 'mpdf') {
    // mPDF
    require_once $mpdfPath;
    
    $mpdf = new \Mpdf\Mpdf();
    $html = generateQuotationHTML($quotation, $quotationNumber, $items, $subtotal, $gstAmount, $grandTotal, $gstRate);
    $mpdf->WriteHTML($html);
    $mpdf->Output($fileName, 'I');
    
} else {
    // No PDF library found - render HTML as a printable page instead
    renderPrintableQuotation($quotation, $quotationNumber, $items, $subtotal, $gstAmount, $grandTotal, $gstRate);
}

// ============================================================
// HELPER: Generate Quotation HTML
// ============================================================

function generateQuotationHTML($q, $num, $items, $subtotal, $gst, $total, $gstRate): string 
{
    $companyName = escape(APP_NAME);
    $companyAddress = '123, Construction Avenue,<br>Industrial Area, Bangalore - 560001';
    $companyPhone = '+91 98765 43210';
    $companyEmail = 'info@kvnconstruction.com';
    $companyGst = '29ABCDE1234F1Z5';
    
    $date = date('d M Y');
    $validUntil = date('d M Y', strtotime('+30 days'));
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Quotation</title>
        <style>
            body { font-family: "Helvetica", "Arial", sans-serif; font-size: 12pt; line-height: 1.6; color: #333; }
            .header { text-align: center; border-bottom: 3px solid #111827; padding-bottom: 20px; margin-bottom: 30px; }
            .header h1 { font-size: 28px; color: #111827; margin: 0; }
            .header h2 { font-size: 16px; color: #f5b400; margin: 5px 0; }
            .header p { font-size: 10px; color: #666; margin: 3px 0; }
            .company-info { margin-bottom: 30px; }
            .company-info table { width: 100%; }
            .company-info td { vertical-align: top; }
            .company-info .left { width: 50%; }
            .company-info .right { width: 50%; text-align: right; }
            .info-box { border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .info-box h4 { margin: 0 0 10px 0; color: #111827; }
            .info-box p { margin: 3px 0; font-size: 10pt; }
            table.items { width: 100%; border-collapse: collapse; margin: 20px 0; }
            table.items th { background: #111827; color: #fff; padding: 10px; text-align: left; font-size: 10pt; }
            table.items td { padding: 10px; border-bottom: 1px solid #eee; font-size: 10pt; }
            table.items tr:nth-child(even) { background: #f9fafb; }
            table.items .text-right { text-align: right; }
            table.items .text-center { text-align: center; }
            .totals { width: 300px; margin-left: auto; }
            .totals table { width: 100%; }
            .totals td { padding: 8px 10px; font-size: 10pt; }
            .totals .grand-total { background: #111827; color: #fff; font-size: 14pt; font-weight: bold; }
            .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 9pt; color: #666; }
            .footer table { width: 100%; }
            .footer td { vertical-align: top; width: 50%; }
            .terms { margin-top: 30px; }
            .terms h4 { color: #111827; margin-bottom: 10px; }
            .terms ol { font-size: 10pt; padding-left: 20px; }
            .stamp { text-align: right; margin-top: 40px; }
            .stamp p { margin: 3px 0; font-size: 10pt; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>' . $companyName . '</h1>
            <h2>CONSTRUCTION QUOTATION</h2>
            <p>Quotation #: ' . $num . ' | Date: ' . $date . '</p>
        </div>
        
        <div class="company-info">
            <table>
                <tr>
                    <td class="left">
                        <div class="info-box">
                            <h4>Our Details</h4>
                            <p><strong>' . $companyName . '</strong></p>
                            <p>' . $companyAddress . '</p>
                            <p>Phone: ' . $companyPhone . '</p>
                            <p>Email: ' . $companyEmail . '</p>
                            <p>GST: ' . $companyGst . '</p>
                        </div>
                    </td>
                    <td class="right">
                        <div class="info-box">
                            <h4>Client Details</h4>
                            <p><strong>' . escape($q['client_name'] ?? 'N/A') . '</strong></p>
                            <p>Email: ' . escape($q['client_email'] ?? 'N/A') . '</p>
                            <p>Phone: ' . escape($q['client_phone'] ?? 'N/A') . '</p>
                            <p>Address: ' . nl2br(escape($q['client_address'] ?? 'N/A')) . '</p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <div style="margin-bottom: 20px;">
            <table style="width: 100%;">
                <tr>
                    <td><strong>Project:</strong> ' . escape($q['project_name'] ?? 'N/A') . '</td>
                    <td style="text-align: right;"><strong>Valid Until:</strong> ' . $validUntil . '</td>
                </tr>';

    if (!empty($q['payment_terms'])) {
        $html .= '<tr><td colspan="2"><strong>Payment Terms:</strong> ' . escape($q['payment_terms']) . '</td></tr>';
    }

    $html .= '
            </table>
        </div>
        
        <h4 style="margin-bottom: 10px;">Quotation Details</h4>
        
        <table class="items">
            <tr>
                <th style="width: 60px;">#</th>
                <th>Description</th>
                <th style="width: 80px;" class="text-center">Qty</th>
                <th style="width: 120px;" class="text-right">Unit Price</th>
                <th style="width: 120px;" class="text-right">Total</th>
            </tr>';

    if (!empty($items)) {
        $count = 1;
        foreach ($items as $item) {
            $itemTotal = (float) ($item['total'] ?? ($item['quantity'] * $item['unit_price']));
            $html .= '
            <tr>
                <td>' . $count . '</td>
                <td>' . escape($item['description'] ?? '') . '</td>
                <td class="text-center">' . number_format((float)$item['quantity']) . '</td>
                <td class="text-right">₹' . number_format((float)$item['unit_price'], 2) . '</td>
                <td class="text-right">₹' . number_format($itemTotal, 2) . '</td>
            </tr>';
            $count++;
        }
    } else {
        // If no items, show the total from quotation
        $html .= '
            <tr>
                <td>1</td>
                <td>' . escape($q['description'] ?? 'Construction Services') . '</td>
                <td class="text-center">1</td>
                <td class="text-right">₹' . number_format($total, 2) . '</td>
                <td class="text-right">₹' . number_format($total, 2) . '</td>
            </tr>';
    }

    $html .= '
        </table>
        
        <div class="totals">
            <table>
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="text-right">₹' . number_format($subtotal, 2) . '</td>
                </tr>
                <tr>
                    <td><strong>GST (' . $gstRate . '%)</strong></td>
                    <td class="text-right">₹' . number_format($gst, 2) . '</td>
                </tr>
                <tr class="grand-total">
                    <td><strong>Grand Total</strong></td>
                    <td class="text-right">₹' . number_format($total, 2) . '</td>
                </tr>
            </table>
        </div>
        
        <div class="terms">
            <h4>Terms & Conditions</h4>
            <ol>
                <li>This quotation is valid for 30 days from the date of issue.</li>
                <li>Payment terms: 50% advance, 40% on completion, 10% on handover.</li>
                <li>Any changes to scope will be billed separately.</li>
                <li>Material prices are subject to market fluctuations.</li>
                <li>Site measurements are indicative; final billing based on actual measurements.</li>
                <li>Taxes as applicable will be charged extra.</li>
                <li>Completion timeline will be confirmed upon PO receipt.</li>
                <li>Warranty: 1 year for workmanship, manufacturer warranty for materials.</li>
            </ol>
        </div>
        
        <div class="stamp">
            <p>For <strong>' . $companyName . '</strong></p>
            <br><br>
            <p>____________________________</p>
            <p>Authorized Signatory</p>
        </div>
        
        <div class="footer">
            <table>
                <tr>
                    <td>
                        <p><strong>Office:</strong> ' . $companyAddress . '</p>
                        <p><strong>Phone:</strong> ' . $companyPhone . ' | <strong>Email:</strong> ' . $companyEmail . '</p>
                    </td>
                    <td style="text-align: right;">
                        <p>GST: ' . $companyGst . '</p>
                        <p>' . $companyName . '</p>
                    </td>
                </tr>
            </table>
        </div>
    </body>
    </html>';
    
    return $html;
}

// ============================================================
// HELPER: Render printable HTML when no PDF library
// ============================================================

function renderPrintableQuotation($q, $num, $items, $subtotal, $gst, $total, $gstRate): void 
{
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Quotation <?php echo escape($num); ?></title>
        <style>
            @media print {
                .no-print { display: none !important; }
                body { margin: 0; padding: 20px; }
                @page { margin: 20mm; }
            }
            body { font-family: Arial, sans-serif; font-size: 12pt; color: #333; margin: 40px; }
            .no-print { margin-bottom: 20px; }
            .no-print button { padding: 12px 30px; background: #f5b400; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; }
            .no-print button:hover { background: #e0a300; }
            .header { text-align: center; border-bottom: 3px solid #111827; padding-bottom: 20px; margin-bottom: 30px; }
            .header h1 { font-size: 28px; color: #111827; margin: 0; }
            .header h2 { font-size: 16px; color: #f5b400; margin: 5px 0; }
            .info-grid { display: flex; gap: 20px; margin-bottom: 20px; }
            .info-box { flex: 1; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
            .info-box h4 { margin: 0 0 10px 0; color: #111827; }
            .info-box p { margin: 3px 0; font-size: 10pt; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #111827; color: #fff; padding: 10px; text-align: left; font-size: 10pt; }
            td { padding: 10px; border-bottom: 1px solid #eee; font-size: 10pt; }
            tr:nth-child(even) { background: #f9fafb; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .totals { width: 300px; margin-left: auto; }
            .totals td { padding: 8px 10px; }
            .grand-total td { background: #111827; color: #fff; font-weight: bold; }
            .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 9pt; color: #666; }
            .terms { margin-top: 30px; }
            .terms h4 { color: #111827; }
            .terms ol { font-size: 10pt; }
            .stamp { text-align: right; margin-top: 40px; }
        </style>
    </head>
    <body>
        <div class="no-print">
            <button onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
            <button onclick="window.close()" style="background: #6c757d; color: #fff;">Close</button>
        </div>
        <?php echo generateQuotationHTML($q, $num, $items, $subtotal, $gst, $total, $gstRate); ?>
    </body>
    </html>
    <?php
}

// Log the PDF generation
if (function_exists('logSecurityEvent')) {
    logSecurityEvent('QUOTATION_PDF_GENERATED', [
        'quotation_id' => $quotationId,
        'number' => $quotationNumber,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
}