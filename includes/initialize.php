<?php
session_start();
require('settings.php');
require('functions.php');
require('objects/Database.php');

$dbhost = "localhost"; // Hostname of the database
$dbuser = "root"; // Username of the database
$dbpass = "root"; // Password of the database
$dbname = "tracklog"; // Name of the database
$db = new Database($dbhost, $dbuser, $dbpass, $dbname);
