<?php
//Updates list for which users are online
session_start();
require 'conn.php';
$conn->select_db("board");
$username = $_SESSION['username'];

$onlinelistTime = time() + 10;
$result_update = mysqli_query($conn, "UPDATE users SET last_login=$onlinelistTime WHERE username='$username'");

$sql_online = "SELECT * FROM users";
$result_online = $conn->query($sql_online);
while($row_online = mysqli_fetch_array($result_online)){
    if($row_online['last_login']>time()){
        echo "<p>" . $row_online['username'] . "</p>";
    };
};
?>