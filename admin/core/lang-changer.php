<?php
require_once("lang.php");


if($_SERVER["REQUEST_METHOD"] == "POST") {

    if(isset($_POST['wanted_lang']))
    {

        $lang = $_POST['wanted_lang'];

        if($lang == LANG_EN || $lang == LANG_FR)    // Check if the wanted lang is support , we don't trust the user
        {

            if( ($lang_data = load_lang($_COOKIE[LANG_COOKIE_NAME])) == null)
                echo "Error while loading lang data";
            else {
                setcookie(LANG_COOKIE_NAME, $lang, LANG_COOKIE_LIFE, "/"); 

                $_SESSION["LANG_DATA"] = null;      // Free the old one
                $_SESSION["LANG_DATA"] = $lang_data;    
            }
                     
        }

    }

    
    header('Location: ../');   
}
