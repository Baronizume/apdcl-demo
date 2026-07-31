<?php
include("../db.php");

header("Content-Type: application/json");

if (!isset($_GET['consumer_no']) || empty(trim($_GET['consumer_no']))) {
    echo json_encode([
        "error" => "Consumer Number is required."
    ]);
    exit();
}

$consumer_no = trim($_GET['consumer_no']);

/*=========================================
    FETCH CONSUMER DETAILS
=========================================*/

$stmt = mysqli_prepare($conn,"
SELECT
    consumer_no,
    name,
    father_name,
    mobile,
    address,
    meter_no,
    category,
    dtr_no,
    pole_no,
    zone,
    circle,
    sub_division
FROM users
WHERE consumer_no=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){

    echo json_encode([
        "error"=>"Consumer not found."
    ]);

    exit();

}

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*=========================================
    FETCH LAST READING
=========================================*/

$previous_reading = 0;

$stmt = mysqli_prepare($conn,"
SELECT current_reading
FROM bills
WHERE consumer_no=?
ORDER BY id DESC
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if($row=mysqli_fetch_assoc($result)){
    $previous_reading = $row['current_reading'];
}

mysqli_stmt_close($stmt);

/*=========================================
    RETURN JSON
=========================================*/

echo json_encode([

    "consumer_no"=>$user['consumer_no'],
    "name"=>$user['name'],
    "father_name"=>$user['father_name'],
    "mobile"=>$user['mobile'],
    "address"=>$user['address'],
    "meter_no"=>$user['meter_no'],
    "category"=>$user['category'],
    "dtr_no"=>$user['dtr_no'],
    "pole_no"=>$user['pole_no'],
    "zone"=>$user['zone'],
    "circle"=>$user['circle'],
    "sub_division"=>$user['sub_division'],
    "previous_reading"=>$previous_reading

]);

mysqli_close($conn);
?>