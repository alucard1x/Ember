<?php
// bill.php - makes a PDF bill of "Your Order" with the FPDF library and downloads it
// nothing is saved in the database
session_start();
require('../../adminSide/posBackend/fpdf186/fpdf.php');
require_once '../config.php';

$cart = [];
if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}

// empty cart -> nothing to print, go back to the menu
if (empty($cart)) {
    header('Location: menu.php#bill');
    exit;
}

// get the name and price of every dish from the database
$lines = [];
$before_tax = 0;
$stmt = mysqli_prepare($link, "SELECT item_name, item_price FROM Menu WHERE item_id = ?");
foreach ($cart as $item_id => $quantity) {
    mysqli_stmt_bind_param($stmt, "s", $item_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $row = null;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
    }
    if ($row) {
        $price = (float) $row['item_price'];
        $total = $price * (int) $quantity;
        $lines[] = [
            'name'     => $row['item_name'],
            'price'    => $price,
            'quantity' => (int) $quantity,
            'total'    => $total,
        ];
        $before_tax = $before_tax + $total;
    }
}
mysqli_stmt_close($stmt);
mysqli_close($link);

$tax_rate   = 0.1;                      // 10% tax, same as the staff POS
$tax_amount = $before_tax * $tax_rate;
$after_tax  = $before_tax + $tax_amount;

// the top and bottom of every PDF page
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

// date
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, 'Date: ' . date('Y-m-d H:i'), 0, 1, 'L');
$pdf->Ln(2);

// table heading
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(80, 10, 'Item', 1);
$pdf->Cell(35, 10, 'Price', 1);
$pdf->Cell(30, 10, 'Quantity', 1);
$pdf->Cell(45, 10, 'Total', 1);
$pdf->Ln();

// one row for each dish
$pdf->SetFont('Arial', '', 11);
foreach ($lines as $line) {
    $pdf->Cell(80, 10, $line['name'], 1);
    $pdf->Cell(35, 10, 'Rs. ' . number_format($line['price'], 2), 1);
    $pdf->Cell(30, 10, $line['quantity'], 1);
    $pdf->Cell(45, 10, 'Rs. ' . number_format($line['total'], 2), 1);
    $pdf->Ln();
}

// totals
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

// 'D' = download the file
$pdf->Output('D', 'ember-order-summary.pdf');
