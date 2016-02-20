<?php
$dbhost = "localhost"; // DB HOST
$dbuser = "root"; // DB USER
$dbpass = "root"; // DB PASSWORD
$dbname = "tracklog"; // DB NAME
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
define("DB_PREFIX", ""); // DB PREFIX
define("DEBUG", false);
define("ROOT", "http://localhost/TrackLog");