<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/pro/admin/config.php");

$db_connection = new mysqli(DATABASE_SERV, DATABASE_USER, DATABASE_PASS);

if ($db_connection->connect_error) {
    die("Connection failed: " . $db_connection->connect_error);
  }