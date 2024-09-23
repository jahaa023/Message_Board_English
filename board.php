<?php
// Connects to mysqli server og specifises what database to use and declares some variables
$username = "";
session_start();
require 'conn.php';
require 'validate.php';
$conn->select_db("board");
$warning = "";
$showwarning = 0;
$sidebarProfileImage = "";
$replyingto_message = "";
$replyingto_username = "";
$replyingto = 0;

//If you're not logged in, you get redirected to login page
if(!empty($_SESSION['username'])){
    $username = $_SESSION['username'];
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    $_SESSION['user_id'] = $row['user_id'];
} else {
    header("Location: index.php");
};

//Specifies how many messages are going to be loaded in on startup
if(!isset($_SESSION['message_amount'])){
    $_SESSION['message_amount'] = 50;
};

//Sets default chat to public chat
if(!isset($_SESSION['current_table'])){
    $_SESSION['current_table'] = "messages";
};

//If you reply to a message, it shows what message you are replying to
if(!empty($_POST['reply_message'])){
    $reply_message_id = $_POST['reply_message'];
    $sql = "SELECT * FROM " . $_SESSION['current_table'] . " WHERE message_id = '$reply_message_id'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);

    $sql2 = "SELECT * FROM users WHERE user_id=" . $row['user_id'];
    $result2 = $conn->query($sql2);
    $row2 = mysqli_fetch_array($result2);

    $replyingto_message = $row['message'];
    $replyingto_username = "Replying to: " . $row2['username'];
    echo "<style>.replyingto_container{display: inline;}</style>";
    $_SESSION['replyingto'] = $reply_message_id;
}

//Cancels the reply if you click the x button
if(!empty($_POST['cancelReply'])){
    $_SESSION['replyingto'] = 0;
};

//Sets current groupchat to selected groupchat
if(!empty($_POST['select_groupchat'])){
    $_SESSION['current_table'] = $_POST['select_groupchat'];
    $_SESSION['message_amount'] = 50;
};

//Creates a private messaging board (groupchat) with the specified users
if(!empty($_POST['create_pm_checkbox'])){
    $pm_create_checkbox = $_POST['create_pm_checkbox'];
    $tablename = bin2hex(random_bytes(10));
    $sql = "CREATE TABLE " . $tablename . " (message_id int NOT NULL AUTO_INCREMENT, user_id int,message varchar(500) DEFAULT (NULL),file varchar(50) DEFAULT (NULL),date varchar(64),time varchar(64),edited int NULL DEFAULT (NULL),notif_time BIGINT DEFAULT 0,reply int DEFAULT 0,PRIMARY KEY (message_id))";
    $conn->query($sql);
    $length = count($pm_create_checkbox);

    $sql = "INSERT INTO groupchats (user_id, tablename) VALUES (" . $_SESSION['user_id'] . ", '$tablename')";
    $conn->query($sql);

    $groupchat_name = "";
    for ($i = 0; $i < $length; $i++) {
        $sql = "INSERT INTO groupchats (user_id, tablename) VALUES ($pm_create_checkbox[$i], '$tablename')";
        $conn->query($sql);

        $sql = "SELECT username FROM users WHERE user_id=$pm_create_checkbox[$i]";
        $result = $conn->query($sql);
        $row = mysqli_fetch_array($result);
        $groupchat_name = $groupchat_name . $row['username'] . ", ";
    }

    $groupchat_name = $groupchat_name . $_SESSION['username'];
    $groupchat_name = substr($groupchat_name, 0, 128) . '...';
    $sql = "INSERT INTO groupchat_settings (tablename, groupchat_name) VALUES ('$tablename', '$groupchat_name')";
    $conn->query($sql);
    $_SESSION['current_table'] = $tablename;
};

//Gets profile picture path to user who is logged inn
$sql = "SELECT profile_image FROM users WHERE username = '$username'";
$result = $conn->query($sql);
$profilepicrow = mysqli_fetch_array($result);

//Function to delete messages you have sent
if(!empty($_POST['delete_message'])){
    $delete = $_POST['delete_message'];
    $sql = "DELETE FROM " . $_SESSION['current_table'] . " WHERE message_id=$delete";
    $conn->query($sql);
}

//Function to edit message
if(!empty($_POST['edit_message_content'])){
    $newcontent = $_POST['edit_message_content'];
    $finaleditmessage_id = $_POST['edit_message_id'];
    $sql = "UPDATE " . $_SESSION['current_table'] . " SET message='$newcontent' WHERE message_id='$finaleditmessage_id'";
    $conn->query($sql);
    $sql ="UPDATE " . $_SESSION['current_table'] . " SET edited=1 WHERE message_id='$finaleditmessage_id'";
    $conn->query($sql);
};

//Inserts message into database if message is posted
if(!empty($_POST['message_content'])){
    $message_content = $_POST['message_content'];
    //Get 24-hour time format in Oslo
    $datetime = new DateTime( "now", new DateTimeZone( "Europe/Oslo" ) );
    $date = $datetime->format( 'Y-m-d' );
    $time = $datetime->format( 'H:i' );
    //Gets file if file is posted
    $file_name = $_FILES['image']['name'];
    $tempname = $_FILES['image']['tmp_name'];
    $folder = 'user_images/'.$file_name;
    $file_type = $_FILES['image']['type'];
    //If filetype is not supported it shows a warning
    $allowed = array("image/jpeg", "image/png", "image/webp", "image/gif");
    if (!in_array($file_type, $allowed) and !empty($file_type)) {
        $warning = "File type not supported. Only JPG, PNG, GIF and WebP allowed.";
        $showwarning = 1;
    } else {
        //Changes name of file if it already exists
        if (file_exists($folder)){
            $temp = explode(".", $file_name);
            $newfilename = round(microtime(true)) . '.' . end($temp);
            $folder = 'user_images/'.$newfilename;
            $file_name = $newfilename;
        };
        //Inserts message into database
        $notif_time = time();
        // If the message is a reply to another message, insert the id of the other message
        if(isset($_SESSION['replyingto']) and $_SESSION['replyingto'] != 0) {
            $replyingto = $_SESSION['replyingto'];
            $_SESSION['replyingto'] = 0;
        }
        if(move_uploaded_file($tempname, $folder)){
            $sql = "INSERT INTO " . $_SESSION['current_table'] . " (user_id, message, file, date, time, notif_time, reply) VALUES ('" . $_SESSION['user_id'] . "', '$message_content', '$file_name', '$date', '$time', $notif_time, $replyingto)";
        } else {
            $sql = "INSERT INTO " . $_SESSION['current_table'] . " (user_id, message, date, time, notif_time, reply) VALUES ('" . $_SESSION['user_id'] . "', '$message_content', '$date', '$time', $notif_time, $replyingto)";
        };
        $result = $conn->query($sql);
    };
}

// Sets username color to user that is logged in to their username color
$sql = "SELECT username_color FROM users WHERE user_id=" . $_SESSION['user_id'];
$result = $conn->query($sql);
$row = mysqli_fetch_array($result);
echo "<style>#sidebarUsername{color:" . $row['username_color'] . "}</style>"

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title id="board_title">Message Board</title>
    <link rel="stylesheet" href="https://use.typekit.net/wte3ssy.css">
    <link rel="icon" type="image/x-icon" href="img/Message_Board_Logo.svg">
    <style><?php include "style.css" ?></style>
    <link class="jsbin" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js"></script>
</head>
<body>
    <!--Div that appers when editing message-->
    <div class="blurry_container">
        <div class="edit_message_container">
            <h1 class="edit_message_h1">Edit message</h1>
                <?php
                if(!empty($_POST['edit_message'])){
                    $editmessage_id = $_POST['edit_message'];
                    echo "<style>.blurry_container{display: block;} .edit_message_container{display: block;}</style>";
                    //Gets current time in Oslo
                    $datetime = new DateTime( "now", new DateTimeZone( "Europe/Oslo" ) );
                    $date = $datetime->format( 'Y-m-d' );
                    $sql = "SELECT * FROM " . $_SESSION['current_table'] . " WHERE message_id='$editmessage_id'";
                    $result = $conn->query($sql);
                    $row = mysqli_fetch_array($result);
                        //Gets profile picture and username from user that sent message
                        $sql2 = "SELECT * FROM users WHERE user_id='" . $row['user_id'] . "'";
                        $result2 = $conn->query($sql2);
                        $row2 = mysqli_fetch_array($result2);
                    
                        //If the date the message was sendt was today, it says today instead of the date the message was sendt.
                        if ($row['date'] == $date) {
                            $datemessage = "Today (edited)";
                        } else {
                            $datemessage = $row['date'] . " (edited)";
                        };

                        //Echoes the message. If the message has an image, display the image. Changes the message text with an input
                        if ($row['file'] != NULL) {
                            $messagefile = "<a target='_blank' href='user_images/" . $row['file'] . "'><img id='message_image' src='user_images/" . $row['file'] . "'></a><br>";
                        } else {
                            $messagefile = "";
                        }
                        echo "<input type='hidden' value='" . $row['message_id'] . "' form='actionForm' name='edit_message_id'></input>";
                        echo "<div class='message' id='editMessage'><div id='message_username_container'><div id='message_profile_image' style='background-image: url(profile_images/" . $row2['profile_image'] . ");'></div><p id='message_username'>" . validate($row2['username']) . "</p><p id='message_timestamp'>" . $row['time'] . " - " . $datemessage . "</p></div><input type='text' maxlength='450' name='edit_message_content' id='edit_message_input' form='actionForm' value='" . validate($row['message']) . "'></input><br>" . $messagefile . "</div>";
                        echo "<input type='submit' id='submitMessageEdit' form='actionForm' value=''></input>";
                        echo "<input type='button' id='cancelSubmitMessageEdit' onClick='window.location.reload()'>";
                    };
                ?>
        </div>
        <!--Container that pops up when making private messages and group chats-->
        <div class="create_pm_container">
            <button class="create_pm_cancel" onClick="window.location.reload()"></button>
            <h1 class="create_pm_headline">Private message</h1>
            <div class="create_pm_list_container">
                <?php
                //Provides a list of all users except the user logged in
                    $sql = "SELECT * FROM users WHERE NOT username='$username'";
                    $result = $conn->query($sql);
                    while ($row = mysqli_fetch_array($result)) {
                        echo "
                        <div class='create_pm_row'>
                            <div class='create_pm_row_inner'>
                                <input type='checkbox' form='actionForm' name='create_pm_checkbox[]' class='create_pm_checkbox' value='" . $row['user_id'] . "'>
                                <div class='create_pm_profilepicture' style='background-image: url(profile_images/" . $row['profile_image'] . ")'></div>
                                <p class='create_pm_username' style='color: " . $row['username_color'] . "'>" . $row['username'] . "</p>
                            </div>
                        </div><br>";
                    };
                    if(!empty($_POST['create_pm'])){
                        echo "<style>.blurry_container{display: block;} .create_pm_container{display: block;}</style>";
                    };
                ?>
                <button type="submit" class="create_pm_done" form="actionForm">Done</button>
            </div>
        </div>
        <div class="groupchat_settings_container">
            <div class="groupchat_settings_inner">
                <h1 class="groupchat_settings_headline">Groupchat settings</h1>
                <button class="create_pm_cancel" onClick="window.location.reload()"></button>
                <?php
                //Echoes the groupchat image and groupchat name in groupchat settings
                if(!empty($_POST['groupchat_settings'])){
                    $tablename = $_POST['groupchat_settings'];
                    $sql = "SELECT * FROM groupchat_settings WHERE tablename='$tablename'";
                    $result = $conn->query($sql);
                    $row = mysqli_fetch_array($result);
                    echo "<div class='groupchat_settings_profile_container'>
                    <div class='groupchat_settings_image' style='background-image: url(groupchat_images/" . $row['groupchat_image'] . ")'></div>
                    <p class='groupchat_settings_name'>" . $row['groupchat_name'] . "</p>
                    </div>";
                    echo "<style>.blurry_container{display: block;} .groupchat_settings_container{display: block;}</style>";
                }

                //Function to change groupchat name
                if(!empty($_POST['submitgroupchatname'])){
                    $newgroupchatname = $_POST['newgroupchatname'];
                    $sql = "UPDATE groupchat_settings SET groupchat_name='$newgroupchatname' WHERE tablename='" . $_SESSION['current_table'] . "'";
                    $conn->query($sql);
                };

                //Function to leave groupchat
                if(!empty($_POST['leavegroupchat'])){
                    $sql = "DELETE FROM groupchats WHERE user_id='" . $_SESSION['user_id'] . "' AND tablename='" . $_SESSION['current_table'] . "'";
                    $conn->query($sql);
                    $_SESSION['current_table'] = "messages";
                };

                //Takes user submitted image and changes either background image or profile image of groupchat
                if(!empty($_POST['submitimage'])){
                    $file_name = $_FILES['image']['name'];
                        $tempname = $_FILES['image']['tmp_name'];
                        $file_type = $_FILES['image']['type'];
                        $submitType = $_POST['submittype'];
                        if ($submitType == "image") {
                            $folder = 'groupchat_images/'.$file_name;
                        } else {
                            $folder = 'groupchat_background_images/'.$file_name;
                        }
                        $allowed = array("image/jpeg", "image/png", "image/webp");
                        if (!in_array($file_type, $allowed)) {
                            $warning = "File type not supported. Only JPEG, PNG and WebP supported!";
                            $showwarning = 1;
                        } else {
                            //Changes name of file if file already exists
                            if (file_exists($folder)){
                                $temp = explode(".", $file_name);
                                $newfilename = round(microtime(true)) . '.' . end($temp);
                                if ($submitType == "image") {
                                    $folder = 'groupchat_images/'.$newfilename;
                                } else {
                                    $folder = 'groupchat_background_images/'.$newfilename;
                                }
                                $file_name = $newfilename;
                            };
                            if(move_uploaded_file($tempname, $folder)){
                                if ($submitType == "image") {
                                    $sql = "UPDATE groupchat_settings SET groupchat_image='$file_name' WHERE tablename='" . $_SESSION['current_table'] . "'";
                                } else {
                                    $sql = "UPDATE groupchat_settings SET groupchat_background_image='$file_name' WHERE tablename='" . $_SESSION['current_table'] . "'";
                                }
                                $result = $conn->query($sql);
                            } else {
                                $warning = "Something went wrong.";
                                $showwarning = 1;
                            };
                        }
                };

                ?>
                <form action="board.php" method="POST" enctype="multipart/form-data">
                    <div class="imageMenu" id="backgroundimageMenuGroupchatSettings">
                        <p>Change groupchat background</p>
                        <input type="file" id="imageInput" accept="image/jpeg, image/png, image/webp" onchange="readURLBackground(this);" name="image">
                        <div class="preview_img_container">
                            <img id="preview_img_background" src="#"/>
                        </div>
                        <input type="hidden" name="submittype" value="background">
                        <input type="submit" id="profileImageSettingsSubmit" value="Save" name="submitimage">
                    </div>
                </form>
                <form action="board.php" method="POST" enctype="multipart/form-data">
                    <div class="imageMenu" id="imageMenuGroupchatSettings">
                        <p>Change groupchat image</p>
                        <input type="file" id="imageInput" accept="image/jpeg, image/png, image/webp" onchange="readURL(this);" name="image">
                        <div class="preview_img_container">
                            <img id="preview_img" src="#"/>
                        </div>
                        <input type="hidden" name="submittype" value="image">
                        <input type="submit" id="profileImageSettingsSubmit" value="Save" name="submitimage">
                    </div>
                    <div class="groupchat_settings_change_name">
                        <p>Change groupchat name</p>
                        <input type="text" placeholder="Type in groupchat name" maxlength="50" name="newgroupchatname">
                        <button type="submit" name="submitgroupchatname" value="Save">Save</button>
                    </div>
                    <div class="current_table_users_display">
                        <p>Users in groupchat:</p>
                        <div class="current_table_users_display_inner">
                        <?php
                        //Echoes all users in groupchat
                        if ($_SESSION['current_table'] != "messages"){
                            $sql = "SELECT * FROM groupchats WHERE tablename='" . $_SESSION['current_table'] . "'";
                            $result = $conn->query($sql);
                            if(mysqli_num_rows($result) != 0){
                                while ($row = mysqli_fetch_array($result)){
                                    $sql2 = "SELECT username FROM users WHERE user_id='" . $row['user_id'] . "'";
                                    $result2 = $conn->query($sql2);
                                    $row2 = mysqli_fetch_array($result2);
                                    echo $row2['username'] . "<br>";
                                }
                            }
                        }
                        ?>
                        </div>
                    </div>
                    <button class="leave_groupchat_button" type="submit" name="leavegroupchat" value="Leave">Leave groupchat</button>
                    <button class="groupchat_settings_done_button" onClick="window.location.reload()">Done</button>
                </form>
            </div>
        </div>
    </div>
    <div class="messages_warning_container">
        <div class="messages_warning" id="messages_warning">
            <p><?php echo $warning;?></p>
        </div>
    </div>
    <div class="corner_logo">
        <img src="img/Message_Board_Logo.svg" alt="Message Board Logo">
    </div>
    <div class="users_online_container">
        <div class="users_online">
            <img src="img/users_online.svg" alt="Users online">
            <p id="onlineAmount"></p>
        </div>
        <div class="users_online_list">
            <div class="users_online_text_container" id="usersOnline">
            </div>
        </div>
    </div>
    <div class="sidebar">
        <div class="dropdown_container">
            <div class="dropdown">
                <button class="dropbtn"></button>
                <div class="dropdown-content">
                    <a href="user_settings.php">Settings</a>
                    <a href="logout.php">Log out.</a>
                </div>
            </div>
        </div>
        <div class="pm_list_container">
            <button class="pm_list_button_add" form="actionForm" name="create_pm" value="create_pm"></button>
            <div id="pm_list">
                
            </div>
        </div>
        <div class="profile">
            <div class="sidebar_profile_image_container">
                <div class="profile_image_sidebar" style="background-image: url(<?php echo "profile_images/" . $profilepicrow['profile_image']; ?>);"></div>
            </div>
            <p id="sidebarUsername"><?php echo $username ?></p>
        </div>
    </div>
    <div class="replyingto_container">
        <input type="submit" id="cancel_reply" name="cancelReply" form="actionForm" value=" "></input>
        <p class="replyingto_username"><?php echo $replyingto_username; ?></p>
        <p class="replyingto_message"><?php echo $replyingto_message; ?></p>
    </div>
    <div class="message_bar">
        <form action="board.php" method="POST" class="message_form" enctype="multipart/form-data" autocomplete="off">
            <input type="text" id="writeArea" name="message_content" placeholder="Type your message here." maxlength="450" required>
            <div class="imageMenu" id="imageMenu">
                <p>Add photo.</p>
                <input type="file" id="imageInput" accept="image/jpeg, image/png, image/webp, image/gif" onchange="readURL(this);" name="image" value="image_input">
                <div class="preview_img_container">
                    <img id="preview_img" src="#"/>
                </div>
            </div>
            <button id="addImageButton" type="button"></button>
            <input type="submit" id="sendButton" value="" name="submit">
        </form>
        <div class="current_table_display_container">
            <?php
                //Displays name, image and settings of current groupchat youre in
                if ($_SESSION['current_table'] == "messages"){
                    echo "<p class='current_table_display_name'>Public Chat</p>";
                    echo "<style>.current_table_display_settings{display: none;} .current_table_display_image{display:none;}</style>";
                } else {
                    $sql = "SELECT * FROM groupchat_settings WHERE tablename='" . $_SESSION['current_table'] . "'";
                    $result = $conn->query($sql);
                    if(mysqli_num_rows($result) != 0){
                        $row = mysqli_fetch_array($result);
                        echo "<div class='current_table_display_inner'><button type='submit' class='current_table_display_settings' form='actionForm' name='groupchat_settings' value='" . $row['tablename'] . "'></button>";
                        echo "<div class='current_table_display_image' id='current_table_display_image' style='background-image: url(groupchat_images/" . $row['groupchat_image'] . ")'></div></div>";
                        echo "<p class='current_table_display_name'>" . $row['groupchat_name'] . "</p>";
                    }
                }
            ?>
        </div>
    </div>
    <div class="message_area_container">
        <div class="message_area" id="message_area">
        </div>
    </div>
    <form action="board.php" method="POST" id="actionForm"></form>
    <script type="text/javascript">
        //AJAX function that updates messages in real time
        function table(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("message_area").innerHTML = this.responseText
            }
            xhttp.open("GET", "system.php");
            xhttp.send();
        }

        //Function that updates amount of notifications
        function updatenotif(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("board_title").innerHTML = this.responseText
            }
            xhttp.open("GET", "updatenotif.php");
            xhttp.send();
        }

        //Updates messages and notifications every 2 seconds
        setInterval(function(){
            table();
            updatenotif();
        }, 2000);

        //Function that gets all the groupchat that logged in user is in
        function get_groupchats(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("pm_list").innerHTML = this.responseText
            }
            xhttp.open("GET", "get_groupchats.php");
            xhttp.send();
        }

        //Updates list of users online
        function onlinelist(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("usersOnline").innerHTML = this.responseText
            }
            xhttp.open("GET", "update_onlinelist.php");
            xhttp.send();
        }

        //Updates amount of users online
        function onlineAmount(){
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function(){
                document.getElementById("onlineAmount").innerHTML = this.responseText
            }
            xhttp.open("GET", "update_onlineamount.php");
            xhttp.send();
        }

        //Updates online list, online amount and groupchats every 5 seconds
        setInterval(function(){
            onlinelist();
            onlineAmount();
            get_groupchats();
        }, 5000);

        window.onload=function(){
        onlinelist();
        onlineAmount();
        table();
        updatenotif();
        get_groupchats();
        }
        //Script for showing preview of image youre attaching to message
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#preview_img')
                        .attr('src', e.target.result)
                        .width(150)
                        .height(200);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
        //Script for showing preview of image youre changing background to
        function readURLBackground(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#preview_img_background')
                        .attr('src', e.target.result)
                        .width(150)
                        .height(200);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
        // Function to hide or show image input menu
        var x = document.getElementById("imageMenu");
        var y = document.getElementById("imageInput");
        var z = document.getElementById("preview_img");
        x.style.display = 'none';
        document.getElementById('addImageButton').onclick = function() {
            if (x.style.display == 'none') {
                x.style.display = 'inline';
            } else {
                x.style.display = 'none';
                y.value = "";
                z.src = "";
            };
        };
        // Script to prevent forms being resubmitted on page refresh
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        };
        //Script that hides warning after 3 seconds
        $("#messages_warning").delay(3000).hide(1);

        //Function to call php script to load in more messages async
        function loadMessagesPHP(){
        $.get('/load_messages.php?callfunc=1'); return false;
        table();
        }

        //Scrolls to and highlights message
        function jumpToMessage(messageID){
            var i=0;
            document.getElementById(messageID).scrollIntoView({behavior: "auto", block: "center", inline: "center"});
            var highlighInterval = setInterval(function(){
                i++;
                if (i<45){
                    document.getElementById(messageID).style.backgroundColor = "#7ED0FF";
                } else {
                    clearInterval(highlighInterval);
                }
            }, 25);
        }

        //Loads in message and scrolls to it
        function messageScroll(messageID) {
            if (document.getElementById(messageID) == null) {
                var isMessageLoaded = setInterval(function(){
                    if(document.getElementById(messageID) == null){
                        loadMessagesPHP();
                        document.body.style.cursor = "wait";
                    } else {
                        document.body.style.cursor = "default";
                        clearInterval(isMessageLoaded);
                        document.getElementById(messageID).scrollIntoView({behavior: "auto", block: "center", inline: "center"});
                        jumpToMessage(messageID);
                    }
                }, 200);
            } else {
                jumpToMessage(messageID);
            }
        };
    </script>
</body>
<?php
    //Shows a warning if a warning is to be showed
    if($showwarning == 1){
        echo "<style>.messages_warning{display: block;}</style>";
    };
    //Shows who youre replying to
    if(!empty($_POST['reply_message'])){
        echo "<style>.replyingto_container{display: inline;}</style>";
    }
    //Sets background image for message area in group chats
    if($_SESSION['current_table'] != "messages"){
        $sql = "SELECT * FROM groupchat_settings WHERE tablename='" . $_SESSION['current_table'] . "'";
        $result = $conn->query($sql);
        $row = mysqli_fetch_array($result);
    
        echo "<style>.message_area_container{background-image: url('groupchat_background_images/" . $row['groupchat_background_image'] . "');
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-size: cover;
        background-position: center;}</style>";
    }
?>
</html>