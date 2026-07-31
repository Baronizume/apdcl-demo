<?php
session_start();
include("../db.php");
require("../fpdf/fpdf.php");

if (!isset($_SESSION['consumer'])) {
    die("Please login first.");
}

if (!isset($_GET['id'])) {
    die("Invalid Payment ID.");
}

$consumer_no = $_SESSION['consumer'];
$payment_id = intval($_GET['id']);

// -------------------- PAYMENT DETAILS --------------------
$sql = "SELECT * FROM payments
        WHERE id='$payment_id'
        AND consumer_no='$consumer_no'";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die(mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    die("Payment not found.");
}

$payment = mysqli_fetch_assoc($result);

// -------------------- BILL DETAILS --------------------
$bill_no = $payment['bill_no'];

$bill_sql = "SELECT * FROM bills
             WHERE bill_no='$bill_no'";

$bill_result = mysqli_query($conn, $bill_sql);

$bill = mysqli_fetch_assoc($bill_result);

// -------------------- PDF --------------------

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',18);
$pdf->Cell(190,10,'ASSAM POWER DISTRIBUTION COMPANY LIMITED',0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Cell(190,8,'PAYMENT RECEIPT',0,1,'C');

$pdf->Ln(5);

$pdf->SetFillColor(230,240,255);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Receipt No',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,$payment['receipt_no'],1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Payment ID',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,$payment['payment_id'],1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Consumer No',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,$payment['consumer_no'],1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Consumer Name',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,$payment['consumer_name'],1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Bill Number',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,$payment['bill_no'],1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Bill Month',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,
    isset($bill['bill_month']) ? $bill['bill_month']." ".$bill['bill_year'] : "-",
1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Amount Paid',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,"Rs. ".number_format($payment['amount'],2),1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Payment Method',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,$payment['payment_method'],1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Transaction ID',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,$payment['transaction_id'],1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Payment Date',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,date("d-m-Y H:i",strtotime($payment['payment_date'])),1,1);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(60,8,'Status',1,0,'L',true);
$pdf->SetFont('Arial','',11);
$pdf->Cell(130,8,$payment['status'],1,1);

$pdf->Ln(15);

$pdf->SetFont('Arial','I',10);
$pdf->MultiCell(
190,
6,
"This is a computer generated payment receipt.\nNo signature is required."
);

$pdf->Ln(10);

$pdf->SetFont('Arial','B',10);
$pdf->Cell(190,8,'Thank you for paying your electricity bill.',0,1,'C');

$pdf->Output(
'D',
'APDCL_Receipt_'.$payment['receipt_no'].'.pdf'
);
exit;
?>