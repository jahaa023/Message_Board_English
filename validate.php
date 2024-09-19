<?php
//Script to validate user input to make sure HTML and JavaScript code cannot be parsed
function validate($data){ 
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
};
?>