<?php
// PHP script to connect to database
$servername = "localhost";
$serverusername = "root";
$serverpassword = "";

$conn = new mysqli($servername, $serverusername, $serverpassword);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>