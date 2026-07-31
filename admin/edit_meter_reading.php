<?php
session_start();

/*====================================================
    ADMIN LOGIN CHECK
====================================================*/

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*====================================================
    CHECK ID
====================================================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Meter Reading ID.");
}

$id = (int)$_GET['id'];

/*====================================================
    FETCH METER READING
====================================================*/

$query = mysqli_query($conn,"
SELECT *
FROM meter_reading
WHERE id='$id'
LIMIT 1
");

if(mysqli_num_rows($query)==0){
    die("Meter Reading not found.");
}

$row = mysqli_fetch_assoc($query);

/*====================================================
    MESSAGE
====================================================*/

$success = "";
$error   = "";

/*====================================================
    UPDATE METER READING
====================================================*/

if(isset($_POST['update_reading'])){

    $consumer_no = trim($_POST['consumer_no']);
    $meter_no    = trim($_POST['meter_no']);

    $previous_reading = (int)$_POST['previous_reading'];
    $current_reading  = (int)$_POST['current_reading'];

    $reading_date = $_POST['reading_date'];

    if(
        empty($consumer_no) ||
        empty($meter_no) ||
        empty($reading_date)
    ){

        $error = "Please fill all required fields.";

    }
    elseif($current_reading < $previous_reading){

        $error = "Current reading cannot be less than previous reading.";

    }
    else{

        $stmt = mysqli_prepare($conn,"
        UPDATE meter_reading
        SET
            consumer_no=?,
            meter_no=?,
            previous_reading=?,
            current_reading=?,
            reading_date=?
        WHERE id=?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssiisi",
            $consumer_no,
            $meter_no,
            $previous_reading,
            $current_reading,
            $reading_date,
            $id
        );

        if(mysqli_stmt_execute($stmt)){

            $_SESSION['success']="Meter Reading updated successfully.";

            header("Location: meter_reading.php");

            exit();

        }else{

            $error = mysqli_error($conn);

        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Edit Meter Reading | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:1100px;
    margin-top:40px;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.card-header{
    background:#1565c0;
    color:#fff;
    font-size:22px;
    font-weight:600;
}

.form-control{
    min-height:48px;
    border-radius:10px;
}

.btn{
    border-radius:10px;
    min-width:160px;
}

.form-label{
    font-weight:600;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header">

<i class="fa fa-edit"></i>

Edit Meter Reading

</div>

<div class="card-body">

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<?= $error; ?>

</div>

<?php } ?>

<form method="POST">

<div class="row">

    <!-- Consumer Number -->
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Consumer Number
        </label>

        <input
            type="text"
            name="consumer_no"
            id="consumer_no"
            class="form-control"
            value="<?= htmlspecialchars($row['consumer_no']); ?>"
            required>

    </div>

    <!-- Meter Number -->
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Meter Number
        </label>

        <input
            type="text"
            name="meter_no"
            id="meter_no"
            class="form-control"
            value="<?= htmlspecialchars($row['meter_no']); ?>"
            required>

    </div>

    <!-- Previous Reading -->
    <div class="col-md-4 mb-3">

        <label class="form-label">
            Previous Reading
        </label>

        <input
            type="number"
            name="previous_reading"
            id="previous_reading"
            class="form-control"
            value="<?= $row['previous_reading']; ?>"
            required>

    </div>

    <!-- Current Reading -->
    <div class="col-md-4 mb-3">

        <label class="form-label">
            Current Reading
        </label>

        <input
            type="number"
            name="current_reading"
            id="current_reading"
            class="form-control"
            value="<?= $row['current_reading']; ?>"
            required>

    </div>

    <!-- Units -->
    <div class="col-md-4 mb-3">

        <label class="form-label">
            Units Consumed
        </label>

        <input
            type="number"
            id="units"
            class="form-control"
            value="<?= $row['current_reading'] - $row['previous_reading']; ?>">

    </div>

    <!-- Reading Date -->
    <div class="col-md-6 mb-3">

        <label class="form-label">
            Reading Date
        </label>

        <input
            type="date"
            name="reading_date"
            class="form-control"
            value="<?= $row['reading_date']; ?>"
            required>

    </div>

</div>

<hr>

<div class="text-center">

    <button
        type="submit"
        name="update_reading"
        class="btn btn-success">

        <i class="fa fa-save"></i>

        Update Reading

    </button>

    <a
        href="meter_reading.php"
        class="btn btn-secondary">

        <i class="fa fa-arrow-left"></i>

        Back

    </a>

</div>

</form>

</div>

</div>

</div>

<script>

/*=========================================
    AUTO CALCULATE UNITS
=========================================*/

function calculateUnits(){

    let previous = parseInt(document.getElementById("previous_reading").value) || 0;

    let current = parseInt(document.getElementById("current_reading").value) || 0;

    let units = current - previous;

    if(units < 0){
        units = 0;
    }

    document.getElementById("units").value = units;

}

document.getElementById("previous_reading").addEventListener("keyup", calculateUnits);
document.getElementById("previous_reading").addEventListener("change", calculateUnits);

document.getElementById("current_reading").addEventListener("keyup", calculateUnits);
document.getElementById("current_reading").addEventListener("change", calculateUnits);

/*=========================================
    VALIDATION
=========================================*/

document.querySelector("form").addEventListener("submit", function(e){

    let previous = parseInt(document.getElementById("previous_reading").value) || 0;

    let current = parseInt(document.getElementById("current_reading").value) || 0;

    if(current < previous){

        alert("Current Reading cannot be less than Previous Reading.");

        e.preventDefault();

        return false;
    }

});

/* Initial Calculation */

calculateUnits();

</script>

</body>

</html>