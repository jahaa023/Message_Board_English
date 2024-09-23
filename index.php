<?php
session_start();
// Connects to mysql server and specifies which database to use
require 'conn.php';
$conn->select_db("board");

$loginwarning = "";
$showwarning = 0;

// If the login form has been posted
if(!empty($_POST['username'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // SQL code to retreive username form database that matches with input
    $sql = "SELECT username FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    // If the username doesnt exist
    if ( !$row ) {
        $loginwarning = "Username does not exist!";
        $showwarning = 1;
    } else { // If the user exists, compare password hash
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);
        $row = mysqli_fetch_array($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $row['user_id'];
            header("Location: board.php");
        } else {
            $loginwarning = "Password is wrong!";
            $showwarning = 1;
        }
    };
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Board - Login</title>
    <link rel="stylesheet" href="https://use.typekit.net/wte3ssy.css">
    <link rel="icon" type="image/x-icon" href="img/Message_Board_Logo.svg">
    <style><?php include "style.css" ?></style>
</head>
<body>
    <a href="myadmin/index.php" class="admin_link">Admin</a>
    <div class="login_container">
        <div class="login_logo_container">
            <img src="img/Message_Board_Logo.svg" alt="Message Board Logo">
        </div>
        <h1>Log in</h1>
        <form action="index.php" method="POST">
            <div class="login_warning">
                <?php echo $loginwarning ?>
            </div>
            <input type="text" class="login_cred" placeholder="Username" required name="username">
            <br>
            <input type="password" class="login_cred" placeholder="Password" required name="password" id="password">
            <br>
            <input type="checkbox" onclick="showPassword()" id="showpassword"><label for=showpassword class="showpassword_label"> Show password </label>
            <br>
            <br>
            <a href="register.php" class="register_link">Dont have a user? Register.</a>
            <br>
            <input type="submit" class="login_button" value="Log in">
        </form>
    </div>
    <?php
        if($showwarning == 1){
            echo "<style>.login_warning{display: block;} .login_container{height: 550px;}</style>";
        };
    ?>
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
</body>
</html>