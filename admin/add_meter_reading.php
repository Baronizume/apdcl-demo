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
    SUCCESS / ERROR MESSAGE
====================================================*/

$success = "";
$error = "";

/*====================================================
    SAVE METER READING
====================================================*/

if(isset($_POST['save_reading'])){

    $consumer_no = trim($_POST['consumer_no']);
    $meter_no = trim($_POST['meter_no']);

    $previous_reading = (float)$_POST['previous_reading'];
    $current_reading  = (float)$_POST['current_reading'];

    $reading_date = $_POST['reading_date'];

    $meter_status = trim($_POST['meter_status']);
    $reader_name  = trim($_POST['reader_name']);
    $remarks      = trim($_POST['remarks']);

    /* Validation */

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

        $units = $current_reading - $previous_reading;

        $stmt = mysqli_prepare($conn,"
        INSERT INTO meter_reading
        (
            consumer_no,
            meter_no,
            previous_reading,
            current_reading,
            units,
            reading_date,
            meter_status,
            reader_name,
            remarks,
            created_at
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?,?,NOW()
        )
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ssdddssss",
            $consumer_no,
            $meter_no,
            $previous_reading,
            $current_reading,
            $units,
            $reading_date,
            $meter_status,
            $reader_name,
            $remarks
        );

        if(mysqli_stmt_execute($stmt)){

            $_SESSION['success']="Meter reading added successfully.";

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

<title>Add Meter Reading | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f4f7fb;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:1100px;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.card-header{
    background:#0d6efd;
    color:#fff;
    font-size:22px;
    font-weight:600;
    border-radius:18px 18px 0 0!important;
}

.form-label{
    font-weight:600;
}

.form-control,
.form-select{
    min-height:48px;
    border-radius:10px;
}

.btn{
    border-radius:10px;
    min-width:160px;
}

</style>

</head>

<body>

<div class="container mt-5">

<div class="card">

<div class="card-header">

<i class="fa-solid fa-gauge-high"></i>

Add Meter Reading

</div>

<div class="card-body">

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<?= $error; ?>

</div>

<?php } ?>

<form method="POST">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Consumer Number

</label>

<input
type="text"
name="consumer_no"
id="consumer_no"
class="form-control"
placeholder="Enter Consumer Number"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Consumer Name

</label>

<input
type="text"
name="consumer_name"
id="consumer_name"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Meter Number

</label>

<input
type="text"
name="meter_no"
id="meter_no"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Reading Date

</label>

<input
type="date"
name="reading_date"
class="form-control"
value="<?= date('Y-m-d'); ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Previous Reading
</label>

<input
type="number"
step="0.01"
name="previous_reading"
id="previous_reading"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Current Reading

</label>

<input
type="number"
step="0.01"
name="current_reading"
id="current_reading"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Units Consumed

</label>

<input
type="number"
step="0.01"
name="units"
id="units"
class="form-control">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Meter Status

</label>

<select
name="meter_status"
class="form-select">

<option value="Normal">Normal</option>

<option value="Door Locked">Door Locked</option>

<option value="Meter Faulty">Meter Faulty</option>

<option value="Meter Changed">Meter Changed</option>

<option value="Power Off">Power Off</option>

<option value="Disconnected">Disconnected</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Reader Name

</label>

<input
type="text"
name="reader_name"
class="form-control"
value="<?= htmlspecialchars($_SESSION['name']); ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Remarks

</label>

<input
type="text"
name="remarks"
class="form-control"
placeholder="Optional">

</div>

</div>

<hr>

<div class="text-center">

<button
type="submit"
name="save_reading"
class="btn btn-success">

<i class="fa fa-save"></i>

Save Reading

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

const consumerInput=document.getElementById("consumer_no");

consumerInput.addEventListener("change",function(){

    let consumer=this.value.trim();

    if(consumer=="") return;

    fetch("fetch_meter_details.php?consumer_no="+consumer)

    .then(response=>response.json())

    .then(data=>{

        if(data.status=="success"){

            document.getElementById("consumer_name").value=data.name;

            document.getElementById("meter_no").value=data.meter_no;

            document.getElementById("previous_reading").value=data.previous_reading;

            calculateUnits();

        }else{

            alert("Consumer not found.");

            document.getElementById("consumer_name").value="";

            document.getElementById("meter_no").value="";

            document.getElementById("previous_reading").value="";

            document.getElementById("current_reading").value="";

            document.getElementById("units").value="";

        }

    });

});

function calculateUnits(){

    let previous=parseFloat(document.getElementById("previous_reading").value)||0;

    let current=parseFloat(document.getElementById("current_reading").value)||0;

    let units=current-previous;

    if(units<0){

        units=0;

    }

    document.getElementById("units").value=units;

}

document.getElementById("current_reading").addEventListener("keyup",calculateUnits);

document.getElementById("current_reading").addEventListener("change",calculateUnits);

document.querySelector("form").addEventListener("submit",function(e){

    let previous=parseFloat(document.getElementById("previous_reading").value)||0;

    let current=parseFloat(document.getElementById("current_reading").value)||0;

    if(current<previous){

        alert("Current reading cannot be less than previous reading.");

        e.preventDefault();

    }

});

</script>

</body>