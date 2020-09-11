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

define("NUMBER_ELEMENTS_PER_PAGE" , 20);

define("IMG_MAT_DEFAULT" , "default.jpg");
define("IMG_MAX_SIZE" , 3000000);               // Bytes

define("LANG_FILE"         , __DIR__ . "/languages.lang");
define("LANG_PART_END"     , "LANG_END");       
define("LANG_FILE_COMMENT" , "#"); 
// Supported languages 
define("LANG_EN"           , "EN");
define("LANG_FR"           , "FR");
//                            
define("LANG_COOKIE_NAME"  , "lang-cookie");
define("LANG_COOKIE_DEFAULT" , LANG_EN);
define("LANG_COOKIE_LIFE"   , 2147483647); // Make the cookie lasts till January 19, 2038 at 4:14:07 AM , Eternal Cookie , The number is Max number that an int can hold 2^(31) - 1

function LANG($key)
{
    echo $_SESSION['LANG_DATA'][$key];
}

function LANG_1($key)
{
    echo "\"" . $_SESSION['LANG_DATA'][$key] . "\"";
}

function LANG_R($key)
{
    return $_SESSION['LANG_DATA'][$key];
}


function LOG_ERROR($msg, $line, $file_name){
    error_log( date("[Y-m-d H:i:s] ") . "$msg - $file_name ($line) \n", 3, LOG_FILE);
}

