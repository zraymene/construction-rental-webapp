<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */

session_start();

if(!isset($_SESSION['admin']))   // Check if admin is already loged in 
{
    header("Location:auth/login.php");
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INDEX</title>
</head>
<body>
    ADMIN  INDEX
    <form method="POST" action="auth/logout.php">
        <input type="submit" value="Logout" id="submit_btn">
    </form>
</body>
</html>