<?php
define("DB_PREFIX", "tracklog_"); // DB PREFIX
define("DEBUG", false);
define("ROOT", "http://localhost/TrackLog");

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

