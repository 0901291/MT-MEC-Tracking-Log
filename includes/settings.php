<?php
$dbhost = "localhost"; // DB HOST
$dbuser = "root"; // DB USER
$dbpass = ""; // DB PASSWORD
$dbname = "tracklog"; // DB NAME
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
define("DB_PREFIX", ""); // DB PREFIX
