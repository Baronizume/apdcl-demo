<?php

include("../db.php");

if(!isset($_GET['consumer_no'])){
    exit;
}

$consumer_no = mysqli_real_escape_string($conn,$_GET['consumer_no']);

$sql = mysqli_query($conn,"
SELECT
    u.consumer_no,
    u.name,
    u.meter_no,
    (
        SELECT current_reading
        FROM meter_reading
        WHERE consumer_no=u.consumer_no
        ORDER BY id DESC
        LIMIT 1
    ) AS last_reading
FROM users u
WHERE u.consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($sql)==0){

    echo json_encode([
        "status"=>"not_found"
    ]);

    exit;

}

$row = mysqli_fetch_assoc($sql);

echo json_encode([

    "status"=>"success",

    "name"=>$row['name'],

    "meter_no"=>$row['meter_no'],

    "previous_reading"=>$row['last_reading'] ?? 0

]);

?>