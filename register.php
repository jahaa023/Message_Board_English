<?php
session_start();
// Connects to mysql server and specifies which database to use
require 'conn.php';
require 'validate.php';
$conn->select_db("board");

$loginwarning = "";
$showwarning = 0;

// If the form has been posted
if(!empty($_POST['username'])) {
    $username = validate($_POST['username']);
    $password = $_POST['password'];
    // SQL code to retreive username form database that matches with input
    $sql = "SELECT username FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    // If the username is not taken, the password is hashed and inserted into database, else show a warning that the username is taken
    if ( !$row ) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password_hash')";
        $conn->query($sql);
        $_SESSION['username'] = $username;
        header("Location: board.php");
    }
    else {
        $loginwarning = "Username taken.";
        $showwarning = 1;
    };
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Board - Create user</title>
    <link rel="stylesheet" href="https://use.typekit.net/wte3ssy.css">
    <link rel="icon" type="image/x-icon" href="img/Message_Board_Logo.svg">
    <style><?php include "style.css" ?></style>
</head>
<body>
    <div class="login_container">
        <div class="login_logo_container">
            <img src="img/Message_Board_Logo.svg" alt="Message Board Logo">
        </div>
        <h1>Create user</h1>
        <form action="register.php" method="POST">
            <div class="login_warning">
                <?php echo $loginwarning ?>
            </div>
            <input type="text" class="login_cred" placeholder="Create username" name="username" required maxlength="30">
            <br>
            <input type="password" class="login_cred" placeholder="Create password" name="password" required id="password">
            <br>
            <input type="checkbox" onclick="showPassword()" id="showpassword"><label for=showpassword class="showpassword_label"> Show password </label>
            <br>
            <input type="submit" class="login_button" value="Create user">
        </form>
    </div>
    <?php
        if($showwarning == 1){
            echo "<style>.login_warning{display: block;} .login_container{height: 500px;}</style>";
        };
    ?>
</body>
<script>
    // Function to show or hide password
    function showPassword() {
        var x = document.getElementById("password");
        if (x.type === "password") {
            x.type = "text";
        } else {
            x.type = "password";
        }
        } 
</script>
</html>