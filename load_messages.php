<?php
//Loads in more messages
if ($_GET['callfunc'] == 1) {
    session_start();
    $_SESSION['message_amount'] = $_SESSION['message_amount'] + 50;
};
?>