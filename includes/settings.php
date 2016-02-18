<?php
$dbhost = ""; // DB HOST
$dbuser = ""; // DB USER
$dbpass = ""; // DB PASSWORD
$dbname = ""; // DB NAME
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
define("DB_PREFIX", ""); // DB PREFIX
define("DEBUG", false);
