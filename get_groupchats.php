<?php
//Gets list of groupchats that logged in user is in and applies the specified groupchat image to them
session_start();
require "conn.php";
$conn->select_db("board");
$user_id = $_SESSION['user_id'];

if ($_SESSION['current_table'] == "messages"){
    $stylebutton = "style='border: 3px solid #7ED0FF'";
} else {
    $stylebutton = "";
};

echo "<button $stylebutton type='submit' value='messages' form='actionForm' name='select_groupchat' class='pm_list_button'>Public chat</button>";
$sql = "SELECT * FROM groupchats WHERE user_id=$user_id";
$result = $conn->query($sql);
if(mysqli_num_rows($result) > 0){
    while ($row = mysqli_fetch_array($result)) {
        if ($row['tablename'] == $_SESSION['current_table']){
            $stylebutton = "border: 3px solid #7ED0FF";
        } else {
            $stylebutton = "";
        }

        $sql2 = "SELECT * FROM groupchat_settings WHERE tablename='" . $row['tablename'] . "'";
        $result2 = $conn->query($sql2);
        $row2 = mysqli_fetch_array($result2);

        echo "<button style='background-image: url(groupchat_images/" . $row2['groupchat_image'] . "); $stylebutton' type='submit' value='" . $row['tablename'] . "' form='actionForm' name='select_groupchat' class='pm_list_button'></button>";
    };
}
?>