<?php
//Updates counter for how many users are online
require 'conn.php';
$conn->select_db("board");

$sql_online = "SELECT COUNT(*) AS total FROM users WHERE last_login>" . time();
$result_online = $conn->query($sql_online);
$data=mysqli_fetch_assoc($result_online);
echo "<h1>" . $data['total'] . "</h1>";
?>