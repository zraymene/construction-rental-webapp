<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */

require_once("admin/database/db_connect.php");

if( $_SERVER['REQUEST_METHOD'] == 'POST' )
{

    require_once("admin/systems.php");

    session_start();

    $_SESSION['ADMINS_MANGER'] = new AdminsManger($db_connection);

    $admin = new Admin();
    $admin->username = $_POST['username'];
    $admin->password = $_POST['password'];
    $admin->is_ceo   = TRUE;

    if($_SESSION['ADMINS_MANGER']->add($admin) == NULL)
    {
        echo 'Error while adding admin !';
        header("Location:install.php");
    }

  //  header("Location:/pro/");
  //  unlink(__FILE__);
}

$queries = array(
    "CREATE DATABASE ". DATABASE_NAME ,

    "CREATE TABLE ". DATABASE_NAME .".`admins` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `username` varchar(255) NOT NULL,
        `password` varchar(255) NOT NULL,
        `is_ceo` tinyint(1) NOT NULL,
        PRIMARY KEY (`id`)
       ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8",

    "CREATE TABLE ". DATABASE_NAME .".`clients` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `first_name` varchar(255) NOT NULL,
        `last_name` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
        `phone` varchar(20) NOT NULL,
        `list_rents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
        PRIMARY KEY (`id`)
       ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8   ",

       "CREATE TABLE ". DATABASE_NAME .".`materials` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `default_price` double unsigned DEFAULT 0,
        `is_free` tinyint(1) NOT NULL DEFAULT 0,
        `list_clients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`list_clients`)),
        `image_path` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
       ) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8",

       "CREATE TABLE ". DATABASE_NAME .".`rents` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `client_id` int(11) NOT NULL,
        `material_id` int(11) NOT NULL,
        `price` double NOT NULL DEFAULT 0,
        `creation_date` datetime NOT NULL,
        `deadline_date` date NOT NULL,
        PRIMARY KEY (`id`)
       ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8"
);

$start = microtime(true);

foreach ($queries as $query) {

    if (!$db_connection->query($query))
    {
        echo $db_connection->error . "<br>";
        continue;
    }

}
$elapsed = microtime(true) - $start;

echo 'Elapsed time : '.$elapsed;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web application installation precess</title>
</head>
<body>

    <form method="POST" action="install.php" onsubmit="verify_data">
        <label for="username_id">Username:</label><br>
        <input name="username" type="text" id="username_id"> <br>
        <label for="password_id">Password:</label><br>
        <input name="password" type="password" id="password_id"><br>
        <input type="submit" value="Register" id="submit_btn">
    </form>

</body>
</html>