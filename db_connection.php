<?php

$connection = mysqli_connect("localhost",'root','','hirely');
if(!$connection){
    die("Connection Failed: ". mysqli_connect_error());
}
else{
    // Connection successful
}

?>