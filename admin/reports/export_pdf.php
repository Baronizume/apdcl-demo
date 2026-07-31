<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}

include("../../db.php");
require("../../fpdf/fpdf.php");

$pdf = new FPDF();

$pdf->AddPage();

$pdf->SetFont('Arial','B',16);

$pdf->Cell(190,10,'APDCL Consumer Report',0,1,'C');

$pdf->SetFont('Arial','',11);

$pdf->Cell(190,8,'Generated On : '.date("d-m-Y H:i:s"),0,1,'C');

$pdf->Ln(5);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(15,10,'ID',1);
$pdf->Cell(35,10,'Consumer No',1);
$pdf->Cell(50,10,'Name',1);
$pdf->Cell(55,10,'Email',1);
$pdf->Cell(35,10,'Phone',1);

$pdf->Ln();

$pdf->SetFont('Arial','',9);

$result = mysqli_query($conn,"SELECT * FROM users ORDER BY id DESC");

while($row=mysqli_fetch_assoc($result))
{

    $pdf->Cell(15,8,$row['id'],1);

    $pdf->Cell(35,8,$row['consumer_no'],1);

    $pdf->Cell(50,8,$row['name'],1);

    $pdf->Cell(55,8,$row['email'],1);

    $phone = isset($row['phone']) ? $row['phone'] : "-";

    $pdf->Cell(35,8,$phone,1);

    $pdf->Ln();

}

$pdf->Output("I","Consumer_Report.pdf");
exit();
?>