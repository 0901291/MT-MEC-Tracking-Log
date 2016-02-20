<?php
$dbhost = "localhost"; // DB HOST
$dbuser = "root"; // DB USER
$dbpass = "root"; // DB PASSWORD
$dbname = "tracklog"; // DB NAME
$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
define("DB_PREFIX", "tracklog_"); // DB PREFIX
define("DEBUG", false);

class database {
    private $user = "root"; // DB USER
    private $host = "localhost"; // DB HOST
    private $pass = ""; // DB PASSWORD
    private $name = "tracklog"; // DB NAME
    public $conn;

    public function getConnection () {
        $this -> $conn = null;

        try {
            $this -> conn = new mysqli($this->host, $this->user, $this->pass, $this->name);
        } catch (mysqli_sql_exception $exception) {
            echo "connection error: ". $exception->getMessage();
        }
    }
}