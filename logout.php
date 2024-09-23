<?php
//Script to logout user
session_start();
unset($_SESSION['username']);
unset($_SESSION['current_table']);  
session_destroy();
header("Location: index.php");
exit;
?>;