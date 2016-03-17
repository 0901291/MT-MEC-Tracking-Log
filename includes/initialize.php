<?php
session_start();
require('settings.php');
require('functions.php');
require('objects/Database.php');
require('crypto/phpCrypt.php');
require(__DIR__.'/../vendor/autoload.php');

$db = new Database(DBHST, DBUSR, DBPASS, DBNAME);
