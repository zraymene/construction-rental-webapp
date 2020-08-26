<?php

define("DATABASE_SERV" , "localhost");
define("DATABASE_NAME" , "project");
define("DATABASE_USER" , "root");
define("DATABASE_PASS" , "");

$db_connection = new mysqli(DATABASE_SERV, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($db_connection->connect_error) {
    die("Connection failed: " . $db_connection->connect_error);
  }