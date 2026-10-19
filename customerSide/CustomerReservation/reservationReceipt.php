<?php
// reservationReceipt.php - downloads the PDF receipt of one reservation (FPDF). DB: Reservations (R)
require('../../adminSide/posBackend/fpdf186/fpdf.php');
require_once '../config.php';

// the reservation id from the address, e.g. ?reservation_id=1320263
$reservation_id = 1;
if (isset($_GET['reservation_id'])) {
    $reservation_id = (int) $_GET['reservation_id'];
}

// returns the reservation row, or null
function getReservationInfoById($link, $reservation_id) {
    $query = "SELECT * FROM Reservations WHERE reservation_id='$reservation_id'";
    $result = mysqli_query($link, $query);

    if ($result) {
        $reservationInfo = mysqli_fetch_assoc($result);
        return $reservationInfo;
    } else {
        return null;
    }
}

$reservationInfo = getReservationInfoById($link, $reservation_id);

if ($reservationInfo) {
    // the top of the PDF page
    class PDF extends FPDF {
        function Header() {
            $this->SetFont('Arial', 'B', 20);

            $logoText = "EMBER DINING & BAR";
            
            // the restaurant name (clicking it in the PDF opens the website)
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 10, $logoText, 0, 1, 'C', false, (getenv('APP_URL') ?: 'http://localhost/RestaurantProject') . '/customerSide/home/home.php#hero');
            $this->SetTextColor(0);

            $this->Ln(10);

            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 10, 'Reservation Information', 1, 1, 'C');
        }
    }


    $pdf = new PDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    // one row for each detail
    $pdf->Cell(40, 10, 'Reservation ID:', 1);
    $pdf->Cell(150, 10, $reservationInfo['reservation_id'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Customer Name:', 1);
    $pdf->Cell(150, 10, $reservationInfo['customer_name'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Table ID:', 1);
    $pdf->Cell(150, 10, $reservationInfo['table_id'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Reservation Time:', 1);
    $pdf->Cell(150, 10, $reservationInfo['reservation_time'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Reservation Date:', 1);
    $pdf->Cell(150, 10, $reservationInfo['reservation_date'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Head Count:', 1);
    $pdf->Cell(150, 10, $reservationInfo['head_count'], 1);
    $pdf->Ln();

    $pdf->Cell(40, 10, 'Special Request:', 1);
    $pdf->Cell(150, 10, $reservationInfo['special_request'], 1);
    $pdf->Ln();

    // 'D' = download the file
    $pdf->Output('Reservation-Copy-ID' . $reservationInfo['reservation_id'] . '.pdf', 'D');
} else {
    echo 'Invalid reservation ID or reservation not found.';
}
?>
