<?php
class Database
{
    private $user = "root"; // DB USER
    private $host = "localhost"; // DB HOST
    private $pass = "root"; // DB PASSWORD
    private $name = "tracklog"; // DB NAME
    private $conn;

    function __construct() {
        $this -> conn = null;

        try {
            $this -> conn = new mysqli($this->host, $this->user, $this->pass, $this->name);
        } catch (mysqli_sql_exception $exception) {
            echo "connection error: ". $exception->getMessage();
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
