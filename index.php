<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */
    $install_file = "install.php";
    
    if(file_exists($install_file))
    {
        echo 'Found fine : installing !';

        //header("Location: /pro/" . $install_file);
        header( "Location:".$install_file );

    }

    $install_file = null; // Free the memeory 

    echo 'EVERY THING US OK';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INDEX</title>
</head>
<body>
    
</body>
</html>