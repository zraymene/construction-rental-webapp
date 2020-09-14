<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */
require_once("../core/db_connect.php");
require_once("../core/systems.php");
require("../core/lang.php");

session_start();

if(!isset($_SESSION["LANG_DATA"]))
    lang_init();

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
        $error = LANG_R("LOGIN_ERROR");
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php LANG("LOGIN_TITLE"); ?></title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="icon" href="../../css/dashboard.png">
</head>
<body>

    <div id="edit_wraper" class="popup-container">
        <?php 
            if(!empty($error))
            {
                echo "<div class=\"notfication-container notif-red\">
                        <div class=\"notif-icon\">
                            <img />
                        </div>
                        <div class=\"notif-msg\">
                            <p>{$error}</p>
                        </div>
                    </div>";
            }
        ?>
        <div class="container center">
            <h1><?php LANG("LOGIN"); ?></h1>
            <form name="auth_form" method="POST" action="login.php" onsubmit="verify_data(this);">
                <label for="username_field"><?php LANG("ADMIN_PAGE_TABLE_USERNAME"); ?>:</label><br>
                <input name="username" type="text" class="input-field"> <br>
                <label for="password_field"><?php LANG("ADMIN_PAGE_PASSWORD"); ?>:</label><br>
                <input name="password" type="password" class="input-field"><br>
                <input type="submit" value="Login" class="btn">
            </form>
        </div>
    </div>


    <script src="../../js/scripts.js"></script>
</body>
</html>