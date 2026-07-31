<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

include("../../db.php");

$type = isset($_GET['type']) ? $_GET['type'] : "";

switch($type){

    // =========================
    // CONSUMER REPORT
    // =========================
    case "consumer":

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Consumer_Report.xls");

        echo "ID\tConsumer No\tName\tEmail\tPhone\n";

        $result = mysqli_query($conn,"SELECT * FROM users ORDER BY id DESC");

        while($row=mysqli_fetch_assoc($result)){

            echo $row['id']."\t";
            echo $row['consumer_no']."\t";
            echo $row['name']."\t";
            echo $row['email']."\t";
            echo $row['phone']."\n";

        }

    break;


    // =========================
    // PAYMENT REPORT
    // =========================
    case "payment":

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Payment_Report.xls");

        echo "ID\tConsumer No\tAmount\tPayment Date\n";

        $result = mysqli_query($conn,"SELECT * FROM payments ORDER BY id DESC");

        while($row=mysqli_fetch_assoc($result)){

            echo $row['id']."\t";
            echo $row['consumer_no']."\t";
            echo $row['amount']."\t";
            echo $row['payment_date']."\n";

        }

    break;


    // =========================
    // BILL REPORT
    // =========================
    case "bill":

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Bill_Report.xls");

        echo "ID\tConsumer No\tUnits\tTotal Bill\tStatus\tMonth\n";

        $result = mysqli_query($conn,"SELECT * FROM bills ORDER BY id DESC");

        while($row=mysqli_fetch_assoc($result)){

            echo $row['id']."\t";
            echo $row['consumer_no']."\t";
            echo $row['units']."\t";
            echo $row['total_bill']."\t";
            echo $row['status']."\t";
            echo $row['month']."\n";

        }

    break;


    // =========================
    // COMPLAINT REPORT
    // =========================
    case "complaint":

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Complaint_Report.xls");

        echo "ID\tConsumer No\tSubject\tStatus\tDate\n";

        $result = mysqli_query($conn,"SELECT * FROM complaints ORDER BY id DESC");

        while($row=mysqli_fetch_assoc($result)){

            echo $row['id']."\t";
            echo $row['consumer_no']."\t";
            echo $row['subject']."\t";
            echo $row['status']."\t";
            echo $row['date']."\n";

        }

    break;


    // =========================
    // REVENUE REPORT
    // =========================
    case "revenue":

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=Revenue_Report.xls");

        echo "Payment ID\tConsumer No\tAmount\tPayment Date\n";

        $result = mysqli_query($conn,"SELECT * FROM payments ORDER BY payment_date DESC");

        while($row=mysqli_fetch_assoc($result)){

            echo $row['id']."\t";
            echo $row['consumer_no']."\t";
            echo $row['amount']."\t";
            echo $row['payment_date']."\n";

        }

    break;


    default:

        echo "Invalid Report Type.";

    break;
}
?>