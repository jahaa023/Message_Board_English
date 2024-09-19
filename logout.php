<?php
//Script to logout user
session_start();
unset($_SESSION['username']);   
session_destroy();
header("Location: index.php");
exit;
?>;