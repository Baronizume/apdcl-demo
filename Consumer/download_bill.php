<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

require_once("../fpdf/fpdf.php");
include("../db.php");

$consumer_no = $_SESSION['consumer'];

if (!isset($_GET['id'])) {
    die("Invalid Bill.");
}

$billId = (int)$_GET['id'];

/* ===============================
   FETCH BILL
================================ */

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='$billId'
AND consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($billQuery)==0){
    die("Bill not found.");
}

$bill = mysqli_fetch_assoc($billQuery);

/* ===============================
   FETCH USER
================================ */

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$user = mysqli_fetch_assoc($userQuery);

/* ===============================
   CALCULATIONS
================================ */

$units = $bill['units'];

$totalBill = $bill['total_bill'];

/* ===============================
   AMOUNT IN WORDS
================================ */

function numberToWords($number)
{
    $ones = array(
        0 => '',
        1 => 'One',
        2 => 'Two',
        3 => 'Three',
        4 => 'Four',
        5 => 'Five',
        6 => 'Six',
        7 => 'Seven',
        8 => 'Eight',
        9 => 'Nine',
        10 => 'Ten',
        11 => 'Eleven',
        12 => 'Twelve',
        13 => 'Thirteen',
        14 => 'Fourteen',
        15 => 'Fifteen',
        16 => 'Sixteen',
        17 => 'Seventeen',
        18 => 'Eighteen',
        19 => 'Nineteen'
    );

    $tens = array(
        2 => 'Twenty',
        3 => 'Thirty',
        4 => 'Forty',
        5 => 'Fifty',
        6 => 'Sixty',
        7 => 'Seventy',
        8 => 'Eighty',
        9 => 'Ninety'
    );

    $number = round($number);

    if($number == 0){
        return "Zero";
    }

    $words = "";

    if($number >= 10000000){

        $words .= numberToWords(floor($number/10000000)) . " Crore ";
        $number %= 10000000;

    }

    if($number >= 100000){

        $words .= numberToWords(floor($number/100000)) . " Lakh ";
        $number %= 100000;

    }

    if($number >= 1000){

        $words .= numberToWords(floor($number/1000)) . " Thousand ";
        $number %= 1000;

    }

    if($number >= 100){

        $words .= numberToWords(floor($number/100)) . " Hundred ";
        $number %= 100;

    }

    if($number > 0){

        if($number < 20){

            $words .= $ones[$number];

        }else{

            $words .= $tens[floor($number/10)];

            if(($number % 10) > 0){

                $words .= " ".$ones[$number % 10];

            }

        }

    }

    return trim($words);
}


$amountWords = numberToWords($totalBill);

/* ===============================
   CREATE PDF
================================ */

class PDF extends FPDF
{
    function Header()
    {
        if(file_exists("../assets/images/logo-circle.png")){
            $this->Image("../assets/images/logo-circle.png",10,8,22);
        }

        $this->SetFont('Arial','B',16);
        $this->Cell(0,8,'ASSAM POWER DISTRIBUTION COMPANY LIMITED',0,1,'C');

        $this->SetFont('Arial','',11);
        $this->Cell(0,6,'Official Electricity Bill',0,1,'C');

        $this->Ln(4);

        $this->SetDrawColor(0,102,204);
        $this->Line(10,32,200,32);

        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-18);

        $this->SetFont('Arial','I',9);

        $this->Cell(
            0,
            5,
            'Generated from APDCL Consumer Portal',
            0,
            1,
            'C'
        );

        $this->Cell(
            0,
            5,
            'Page '.$this->PageNo(),
            0,
            0,
            'C'
        );
    }

    function TitleRow($title)
    {
        $this->SetFillColor(0,102,204);
        $this->SetTextColor(255);

        $this->SetFont('Arial','B',11);

        $this->Cell(190,8,$title,1,1,'L',true);

        $this->SetTextColor(0);
    }
}

$pdf = new PDF();

$pdf->SetAutoPageBreak(true,20);

$pdf->AddPage();

$pdf->SetFont('Arial','',10);
/* ==========================================
   CONSUMER DETAILS
========================================== */

$pdf->TitleRow("CONSUMER DETAILS");

$pdf->SetFont('Arial','',10);

$pdf->Cell(40,8,'Consumer No',1);
$pdf->Cell(55,8,$user['consumer_no'],1);

$pdf->Cell(35,8,'Meter No',1);
$pdf->Cell(60,8,$user['meter_no'],1);
$pdf->Ln();

$pdf->Cell(40,8,'Name',1);
$pdf->Cell(55,8,$user['name'],1);

$pdf->Cell(35,8,'Category',1);
$pdf->Cell(60,8,$user['category'],1);
$pdf->Ln();

$pdf->Cell(40,8,'Mobile',1);
$pdf->Cell(55,8,$user['mobile'],1);

$pdf->Cell(35,8,'Supply Type',1);
$pdf->Cell(60,8,$bill['supply_voltage'],1);
$pdf->Ln();

$pdf->Cell(40,8,'Email',1);
$pdf->Cell(55,8,$user['email'],1);

$pdf->Cell(35,8,'Status',1);
$pdf->Cell(60,8,$bill['status'],1);
$pdf->Ln();

$pdf->Cell(40,8,'Zone',1);
$pdf->Cell(55,8,$user['zone'],1);

$pdf->Cell(35,8,'Circle',1);
$pdf->Cell(60,8,$user['circle'],1);
$pdf->Ln();

$pdf->Cell(40,8,'Sub Division',1);
$pdf->Cell(55,8,$user['sub_division'],1);

$pdf->Cell(35,8,'DTR No',1);
$pdf->Cell(60,8,$bill['dtr_no'],1);
$pdf->Ln();

$pdf->Cell(40,20,'Address',1);
$pdf->MultiCell(150,20,$user['address'],1);

$pdf->Ln(6);


/* ==========================================
   BILL INFORMATION
========================================== */

$pdf->TitleRow("BILL INFORMATION");

$pdf->Cell(45,8,'Bill Number',1);
$pdf->Cell(50,8,$bill['bill_no'],1);

$pdf->Cell(40,8,'Bill Date',1);
$pdf->Cell(55,8,date("d-m-Y",strtotime($bill['bill_date'])),1);
$pdf->Ln();

$pdf->Cell(45,8,'Billing Month',1);
$pdf->Cell(50,8,$bill['bill_month'],1);

$pdf->Cell(40,8,'Due Date',1);
$pdf->Cell(55,8,date("d-m-Y",strtotime($bill['due_date'])),1);
$pdf->Ln();

$pdf->Cell(45,8,'Bill Period',1);
$pdf->Cell(
145,
8,
$bill['bill_period_from']." (".$bill['billing_days']." Days)",
1
);

$pdf->Ln(14);


/* ==========================================
   METER READING
========================================== */

$pdf->TitleRow("METER READING");

$pdf->SetFillColor(220,230,242);
$pdf->SetFont('Arial','B',10);

$pdf->Cell(47,8,'Previous',1,0,'C',true);
$pdf->Cell(47,8,'Current',1,0,'C',true);
$pdf->Cell(47,8,'Units',1,0,'C',true);
$pdf->Cell(49,8,'MF',1,1,'C',true);

$pdf->SetFont('Arial','',10);

$pdf->Cell(47,8,$bill['previous_reading'],1,0,'C');
$pdf->Cell(47,8,$bill['current_reading'],1,0,'C');
$pdf->Cell(47,8,$units,1,0,'C');
$pdf->Cell(49,8,$bill['mf'],1,1,'C');

$pdf->Ln(8);
/* ==========================================
   BILL CHARGES
========================================== */

$pdf->TitleRow("BILL CHARGES");

$pdf->SetFillColor(220,230,242);
$pdf->SetFont('Arial','B',10);

$pdf->Cell(130,8,'Description',1,0,'C',true);
$pdf->Cell(60,8,'Amount (Rs.)',1,1,'C',true);

$pdf->SetFont('Arial','',10);

$pdf->Cell(130,8,'Energy Charge',1);
$pdf->Cell(60,8,number_format($bill['energy_charge'],2),1,1,'R');

$pdf->Cell(130,8,'Fixed Charge',1);
$pdf->Cell(60,8,number_format($bill['fixed_charge'],2),1,1,'R');

$pdf->Cell(130,8,'Electricity Duty',1);
$pdf->Cell(60,8,number_format($bill['electricity_duty'],2),1,1,'R');

if(isset($bill['fpppa_charge']))
{
    $pdf->Cell(130,8,'FPPPA Charge',1);
    $pdf->Cell(60,8,number_format($bill['fpppa_charge'],2),1,1,'R');
}

if(isset($bill['government_subsidy']))
{
    $pdf->Cell(130,8,'Government Subsidy',1);
    $pdf->Cell(60,8,'- '.number_format($bill['government_subsidy'],2),1,1,'R');
}

$pdf->SetFont('Arial','B',11);

$pdf->Cell(130,10,'TOTAL BILL',1);
$pdf->Cell(60,10,'Rs. '.number_format($bill['total_bill'],2),1,1,'R');

$pdf->Ln(8);

/* ==========================================
   AMOUNT IN WORDS
========================================== */

$pdf->SetFont('Arial','B',10);
$pdf->Cell(45,8,'Amount in Words :');

$pdf->SetFont('Arial','',10);
$pdf->MultiCell(
145,
8,
$amountWords.' Rupees Only'
);

$pdf->Ln(5);

/* ==========================================
   PAYMENT DETAILS
========================================== */

$pdf->TitleRow("PAYMENT DETAILS");

$pdf->Cell(50,8,'Bill Status',1);
$pdf->Cell(45,8,$bill['status'],1);

$pdf->Cell(45,8,'Due Date',1);
$pdf->Cell(50,8,date("d-m-Y",strtotime($bill['due_date'])),1);

$pdf->Ln();

$pdf->Cell(50,8,'Payable Amount',1);
$pdf->Cell(
140,
8,
'Rs. '.number_format($bill['total_bill'],2),
1
);

$pdf->Ln(15);

/* ==========================================
   FOOTER NOTE
========================================== */

$pdf->SetFont('Arial','I',9);

$pdf->MultiCell(
190,
6,
"This is a computer generated electricity bill and does not require a signature."
);

$pdf->Ln(10);

$pdf->SetFont('Arial','B',10);

$pdf->Cell(120);

$pdf->Cell(
70,
6,
'Authorized Signatory',
0,
1,
'C'
);

$pdf->Ln(5);

/* ==========================================
   DOWNLOAD PDF
========================================== */

$fileName = "APDCL_Bill_".$bill['consumer_no']."_".$bill['month'].".pdf";

$pdf->Output("D",$fileName);

exit;