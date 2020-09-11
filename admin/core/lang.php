<?php

require_once("config.php");

// Load lang file to Key,Value array 
function load_lang($lang) 
{
    if( !($lang_file = fopen(LANG_FILE , "r")) ) {
        echo "Error while opening language file !";
        return null;
    }

    $lang_array = array();
    $read = false;

    while( ($line = fgets($lang_file)) !== false)
    {
        $line = trim($line);                                    // Remove all spaces in the line

        if(empty($line))
            continue;

        if($line[0] == LANG_FILE_COMMENT)                       // It's a comment then pass
            continue;                                           

        if(!$read)
        {
            if(!strcasecmp($line , $lang))                       // Compare without case in mind , if it's a language chunk title then start reading
                $read = true;   
        }else 
        {
            
            if(!strcasecmp(trim($line) , LANG_PART_END) )       // Its the end of Language chunk , stop reading then
                $read = false;
            else                                                // Not yet the end , read the line and parse then put it function
            {                                               
                $str = explode(" = " , $line);                    // Cut the line in half , 1st half is the key , 2nd is the value
                $lang_array[$str[0]] = $str[1];                 // Push the new data to the array

            }
        }

    }

    fclose($lang_file);

    return $lang_array;
}

function lang_init()
{

    
    setcookie(LANG_COOKIE_NAME, LANG_COOKIE_DEFAULT, LANG_COOKIE_LIFE, "/"); 
    
    if(isset($_SESSION["LANG_DATA"])) // Mybe it is set , then free it
        $_SESSION["LANG_DATA"] = null;

    if( ($_SESSION["LANG_DATA"] = load_lang($_COOKIE[LANG_COOKIE_NAME])) == null)
        echo "Error while loading lang data";

}
