<?php
// Builds a PDF bill from the session cart using the FPDF library already
// bundled with the project. No new dependency, and nothing is written to the
// database -- this is the visitor's own order summary.
session_start();
require('../../adminSide/posBackend/fpdf186/fpdf.php');
require_once '../config.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: menu.php#bill');
    exit;
}

// Look the current prices up rather than trusting anything held client side.
$lines = [];
$stmt = mysqli_prepare($link, "SELECT item_name, item_price FROM Menu WHERE item_id = ?");
foreach ($cart as $item_id => $quantity) {
    mysqli_stmt_bind_param($stmt, "s", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $lines[] = [
            'name'     => $row['item_name'],
            'price'    => (float) $row['item_price'],
            'quantity' => (int) $quantity,
            'total'    => (float) $row['item_price'] * (int) $quantity,
        ];
    }
}
mysqli_stmt_close($stmt);
mysqli_close($link);

$before_tax = array_sum(array_column($lines, 'total'));
$tax_rate   = 0.1;                      // same 10% the staff POS applies
$tax_amount = $before_tax * $tax_rate;
$after_tax  = $before_tax + $tax_amount;

class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->Cell(0, 10, 'EMBER DINING & BAR', 0, 1, 'C');
        $this->Ln(4);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Order Summary', 1, 1, 'C');
        $this->Ln(4);
    }

    function Footer() {
        $this->SetY(-20);
        $this->SetFont('Arial', 'I', 9);
        $this->Cell(0, 10, 'This is an order summary, not a paid receipt. Please pay at the counter.', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Date: ' . date('Y-m-d H:i'), 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(80, 10, 'Item', 1);
$pdf->Cell(35, 10, 'Price', 1);
$pdf->Cell(30, 10, 'Quantity', 1);
$pdf->Cell(45, 10, 'Total', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 11);
foreach ($lines as $line) {
    $pdf->Cell(80, 10, $line['name'], 1);
    $pdf->Cell(35, 10, 'Rs. ' . number_format($line['price'], 2), 1);
    $pdf->Cell(30, 10, $line['quantity'], 1);
    $pdf->Cell(45, 10, 'Rs. ' . number_format($line['total'], 2), 1);
    $pdf->Ln();
}

$pdf->Ln(6);
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(145, 10, 'Total', 1);
$pdf->Cell(45, 10, 'Rs. ' . number_format($before_tax, 2), 1);
$pdf->Ln();
$pdf->Cell(145, 10, 'Tax (' . ($tax_rate * 100) . '%)', 1);
$pdf->Cell(45, 10, 'Rs. ' . number_format($tax_amount, 2), 1);
$pdf->Ln();
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(145, 10, 'Grand Total', 1);
$pdf->Cell(45, 10, 'Rs. ' . number_format($after_tax, 2), 1);
$pdf->Ln();

$pdf->Output('D', 'ember-order-summary.pdf');
