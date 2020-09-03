<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */

 // Default site configurations 
 // Don't touch if you don't know what are you doing !!!

define("DATABASE_SERV" , "localhost");
define("DATABASE_NAME" , "project");
define("DATABASE_USER" , "root");
define("DATABASE_PASS" , "");

define("ADMINS_MANGER_FLAG"    , 0x1);
define("RENTS_MANGER_FLAG"     , 0x2);
define("MATERIALS_MANGER_FLAG" , 0x3);
define("CLIENTS_MANGER_FLAG"   , 0x4);

define("LOG_FILE", "error_log.txt");

define("NUMBER_ELEMENTS_PER_PAGE" , 10);

define("IMG_MAT_DEFAULT" , "default.jpg");
define("IMG_MAX_SIZE" , 3000000);           // Bytes

function LOG_ERROR($msg, $line, $file_name){
    error_log( date("[Y-m-d H:i:s] ") . "$msg - $file_name ($line) \n", 3, LOG_FILE);
}

