<?php

include("../db.php");


/*==============================
    TEST ADMIN NOTIFICATION
==============================*/


$title = "New Complaint Received";


$message = "Consumer 890000100000 has registered a new complaint CMP000003.";



$status = "Unread";



$query = mysqli_prepare($conn,"
INSERT INTO admin_notifications
(
title,
message,
status
)
VALUES
(?,?,?)
");



mysqli_stmt_bind_param(
    $query,
    "sss",
    $title,
    $message,
    $status
);



if(mysqli_stmt_execute($query)){


    echo "

    <div style='
    font-family:Segoe UI;
    padding:20px;
    background:#d4edda;
    color:#155724;
    border-radius:10px;
    width:400px;
    margin:50px auto;
    text-align:center;
    '>

    <h3>✅ Notification Added</h3>

    <p>
    $title
    </p>

    <p>
    $message
    </p>

    </div>

    ";


}else{


    echo "

    <div style='
    font-family:Segoe UI;
    padding:20px;
    background:#f8d7da;
    color:#721c24;
    width:400px;
    margin:50px auto;
    text-align:center;
    '>

    <h3>❌ Error</h3>

    ".mysqli_error($conn)."

    </div>

    ";


}


?>