<?php
class Database
{
    private $host;
    private $user;
    private $pass;
    private $name;
    private $conn;

    /**
     * Database constructor.
     * @param $host string Hostname of the database
     * @param $user string Username of the database
     * @param $pass string Password of the database
     * @param $name string Name of the database
     */
    function __construct($host, $user, $pass, $name) {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->name = $name;
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);
        } catch (mysqli_sql_exception $exception) {
            echo "connection error: ". $exception->getMessage();
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
