<?php
/*
 * @author ZEROUAL AYMENE <aymenezeroual@gmail.com>
 */
session_start();

$_SESSION = array();

session_destroy();

header("location: login.php");