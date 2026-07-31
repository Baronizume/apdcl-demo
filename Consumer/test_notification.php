<?php

include("../db.php");


$consumer_no = "890000100000";


while(true){


$title = "System Update";


$message = "APDCL notification updated at ".date("d M Y h:i:s A");



$query=mysqli_prepare($conn,"
INSERT INTO notifications
(
consumer_no,
title,
message,
status
)
VALUES
(?,?,?,'Unread')
");



mysqli_stmt_bind_param(
$query,
"sss",
$consumer_no,
$title,
$message
);



mysqli_stmt_execute($query);



echo "Notification Added: ".$message."<br>";



sleep(1);


}

?>