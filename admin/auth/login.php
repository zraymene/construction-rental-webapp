<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */
require_once("../core/db_connect.php");
require_once("../core/systems.php");

session_start();

$error = "";

if(isset($_SESSION['admin']))   // Check if admin is already loged in 
{
    header("Location:../");
}
if($_SERVER["REQUEST_METHOD"] == "POST")
{
    refresh_mangers(ADMINS_MANGER_FLAG,$db_connection);

    $admin = $_SESSION['ADMINS_MANGER']->auth($_POST['username'], $_POST['password']);

    if( $admin != NULL)
    {
        $_SESSION['admin'] = $admin;
        header("Location:../");
    }else
    {
        LOG_ERROR('Admin login failure from IP :' . $_SERVER['REMOTE_ADDR'],__LINE__ , __FILE__);
        $error = "Wrong Username/Password!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin login</title>
</head>
<body>

    <form name="auth_form" method="POST" action="login.php" onsubmit="verify_data(this);">
        <label for="username_field">Username:</label><br>
        <input name="username" type="text" id="username_field"> <br>
        <label for="password_field">Password:</label><br>
        <input name="password" type="password" id="password_field"><br>
        <input type="submit" value="Login" id="submit_btn">
    </form>

    <p><?php echo $error?></p>
    <script src="../../js/scripts.js"></script>
</body>
</html>