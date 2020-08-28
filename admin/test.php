<?php

require_once("database/db_connect.php");
require_once("core/systems.php");
/*
$_SESSION['CLIENTS_MANGER'] = new ClientsManger($db_connection);

// WRITING TEST :
$client = new Client();

$client->first_name = "ZEROUAL";
$client->last_name  = "AYMENE";
$client->email      = "aymenezeroual@gmail.com";
$client->phone      = "0699893372";
$client->list_rents = array(50 , 10 , 56, 65);

//echo $_SESSION['CLIENTS_MANGER']->add($client);


//READING TEST
//print_r($_SESSION['CLIENTS_MANGER']->select_id(4));

// DELETE
echo $_SESSION['CLIENTS_MANGER']->delete(4);
*/

$_SESSION['MATERIALS_MANGER'] = new MaterialsManger($db_connection);

$mat = new Material();

$mat->name          = "TRACKROT";
$mat->default_price = 500;
$mat->is_free       = TRUE;
$mat->list_clients  = array(50,51,51,35);
$mat->image_path    = "PDF.png";

//vvar_dump($mat);

//echo $_SESSION['MATERIALS_MANGER']->add($mat);

//$_SESSION['MATERIALS_MANGER']->delete(6);

/*$_SESSION['RENTS_MANGER'] = new RentsManger($db_connection);

$rent = new Rent();

$rent->client_id      = 5;
$rent->material_id    = 10;
$rent->price          = 500.65;
$rent->creation_date  = date("Y-m-d H:i:s");
$rent->deadline       = date("Y-m-d");

//$_SESSION['RENTS_MANGER']->add($rent);

//print_r($_SESSION['RENTS_MANGER']->select_id(1));

//$_SESSION['RENTS_MANGER']->delete(3);

*/

if(!isset($_SESSION['ADMINS_MANGER']))
        $_SESSION['ADMINS_MANGER'] = new AdminsManger($db_connection);
    else 
        $_SESSION['ADMINS_MANGER']->refresh_db_connection($db_connection);

$admin = new Admin();

$admin->username = "bogaag";
$admin->password = "fodadag";
$admin->is_ceo   = TRUE;

$_SESSION['ADMINS_MANGER']->add($admin);

/*if($_SESSION['ADMINS_MANGER']->auth("bogaag" , "fodadag") != NULL)
    echo 'FOUND';
else
    echo 'NOT FOUND';
*/

//$_SESSION['ADMINS_MANGER']->delete(2);

