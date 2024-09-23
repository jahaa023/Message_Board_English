<?php
//Displays how many new messages were sent after you logged in.
session_start();
require "conn.php";
$conn->select_db("board");

if(!isset($_SESSION['notifications'])){
    $_SESSION['notifications'] = time();
}

$i = 0;
$sql = "SELECT * FROM " . $_SESSION['current_table'] . " ORDER BY " . $_SESSION['current_table'] . ".`message_id`";
$result = $conn->query($sql);
while($row = mysqli_fetch_array($result)){
    if ($row['notif_time'] > $_SESSION['notifications'] and $row['user_id'] != $_SESSION['user_id']) {
        $i++;
    };
};
if ($i == 0){
    echo "Message Board";
} else {
    echo "Message Board (" . $i . ")";
}
?>