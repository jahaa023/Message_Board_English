<!--A div that pushes the first message down-->
<div class="message_top"></div>
<?php
session_start();
require "conn.php";
require 'validate.php';
$conn->select_db("board");

//Gets specified amount of messages from database
$sql = "SELECT * FROM `messages` ORDER BY `messages`.`message_id` DESC LIMIT " . $_SESSION['message_amount'];
$result = $conn->query($sql);

//Gets the current time in Oslo
$datetime = new DateTime( "now", new DateTimeZone( "Europe/Oslo" ) );
$date = $datetime->format( 'Y-m-d' );
$replyButton = "";

while ($row = mysqli_fetch_array($result)) {
    //Retrieves the profile image for each message
    $sql2 = "SELECT profile_image FROM users WHERE username='" . $row['username'] . "'";
    $result2 = $conn->query($sql2);
    $row2 = mysqli_fetch_array($result2);

    //If the message is edited, display that
    if($row['edited'] == 1){
        $edited = "  (edited)";
    } else {
        $edited = "";
    };

    //If the message was sent today, the date says today instead of the full date
    if ($row['date'] == $date) {
        $datemessage = "Today" . $edited;
    } else {
        $datemessage = $row['date'] . $edited;
    };

    //Adds delete and edit buttons to messages sent by logged in user
    if ($row['username'] == $_SESSION['username']) {
        $editButton = "<button form='actionForm' id='editMessageButton' name='edit_message' value='" . $row['message_id'] . "'></button>";
        $deleteButton = "<button form='actionForm' id='deleteMessageButton' name='delete_message' value='" . $row['message_id'] . "'></button>";
    } else {
        $deleteButton = "";
        $editButton = "";
    };

    $replyLink = "";
    if($row['reply'] != 0) {
        //If the message is a reply to a diffrent message, add a link to that message
        $sql3 = "SELECT * FROM messages WHERE message_id='" . $row['reply'] . "'";
        $result3 = $conn->query($sql3);
        if($result3->num_rows == 1) {
            $row3 = mysqli_fetch_array($result3);

            $sql4 = "SELECT * FROM users WHERE username='" . $row3['username'] . "'";
            $result4 = $conn->query($sql4);
            $row4 = mysqli_fetch_array($result4);

            $replyLink = "<button id='messageReplyLink' onclick='messageScroll(" . $row['reply'] . ")'>
                <div class='reply_link_half_container'>
                    <img src='img/reply.svg' class='reply_link_username_arrow'>
                    <div class='reply_link_username_profilepic' style='background-image: url(profile_images/" . $row4['profile_image'] . ");'></div>
                    <p class='reply_link_username' style='color:" . $row3['username_color'] . ";'>" . $row4['username'] . "</p>
                </div>
                <div class='reply_link_half_container'>
                    <p class='reply_link_message'>" . $row3['message'] . "</p>
                </div>
            </button>";
        } else {
            //If the replied to message is deleted, show a message sent by "Deleted message"
            $replyLink = "<button id='messageReplyLink' style='cursor: not-allowed;'>
                <div class='reply_link_half_container'>
                    <img src='img/reply.svg' class='reply_link_username_arrow'>
                    <div class='reply_link_username_profilepic' style='background-image: url(profile_images/defaultprofile.svg);'></div>
                    <p class='reply_link_username'>Deleted message</p>
                </div>
                <div class='reply_link_half_container'>
                    <p class='reply_link_message'>Deleted message</p>
                </div>
            </button>";
        };
    };

    //Echoes the message into document. If the message has a picture, display the picture
    if ($row['file'] != NULL) {
        $messagefile = "<a target='_blank' href='user_images/" . $row['file'] . "'><img id='message_image' src='user_images/" . $row['file'] . "'></a><br>";
    } else {
        $messagefile = "";
    }

    //If there is a link in the message, wrap it in <a> tag;
    $text = strip_tags($row['message']);
    $textWithLinks = preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank" rel="nofollow">$1</a>', $text);
    $message = $textWithLinks;
    
    echo "<div class='message' id='" . $row['message_id'] . "' style='border: 1px solid " . $row['username_color'] . "'>
        <button form='actionForm' id='replyMessage' name='reply_message' value='" . $row['message_id'] . "'></button>
        <div id='message_username_container'>
            <div id='message_profile_image' style='background-image: url(profile_images/" . $row2['profile_image'] . ");'></div>
            <p id='message_username' style='color:" . $row['username_color'] . "'>" . validate($row['username']) . "</p>
            <p id='message_timestamp'>" . $row['time'] . " - " . $datemessage . "</p>
        </div>
        $replyLink
        <p id='message_content'>" . $message . "</p>" . $messagefile . $deleteButton . $editButton . "</div>";
};
//Checks if there are more messages in the database than the amount that is loaded in. If there is, add a button to load more messages in.
$sql = "SELECT COUNT(*) c FROM messages";
$result = $conn->query($sql);
$row = mysqli_fetch_array($result);
if($row['c'] > $_SESSION['message_amount']){
    echo "<button id='loadInMoreButton' onclick='loadMessagesPHP()'>Load inn more messages</button>";
}
?>
<!--A div that pushes the last message up above the message bar-->
<div class="message_bottom"></div>