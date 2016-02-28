<?php
session_start();
require('settings.php');
require('functions.php');
require('objects/Database.php');
require('crypto/phpCrypt.php');

$db = new Database(DBHST, DBUSR, DBPASS, DBNAME); // defined in includes/settings.php
