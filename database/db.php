<?php
$localhost = "localhost";
$uname = "root";
$pw = "";
$dbname = "brewpos";

$conn = new mysqli($localhost, $uname, $pw, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>