<?php
use PHP_Crypt\PHP_Crypt as PHP_Crypt;

class User
{
    private $conn;

    public $googleId;
    public $name;
    public $imgURL;
    public $email;
    public $key;
    public $token;

    function __construct($db)
    {
        $this->conn = $db;
    }

    public function logIn () {
        $query = "SELECT name, imgURL, id, email, token FROM ".DB_PREFIX."user u WHERE u.googleId = ?";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('s', $this->googleId);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($name, $imgURL, $id, $email, $token);
            if ($stmt->num_rows == 1) {
                $stmt->fetch();
                $_SESSION['googleId'] = $this->googleId;
                $_SESSION['key'] = $this->getKey();
                $crypt = new PHP_Crypt($_SESSION['key']);
                $_SESSION['userId'] = $id;
                $_SESSION['name'] = trim($crypt->decrypt(hex2bin($name)));
                $_SESSION['imgURL'] = trim($crypt->decrypt(hex2bin($imgURL)));
                $_SESSION['email'] = trim($crypt->decrypt(hex2bin($email)));
                $_SESSION['token'] = $token;
                return true;
            }
        }
        return false;
    }

    public function insert () {
        $crypt = new PHP_Crypt($this->key);
        $query = "INSERT INTO ".DB_PREFIX."user (name, imgURL, email, googleId, token) VALUES(?, ?, ?, ?)";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param('sssss', bin2hex($crypt->encrypt($this->name)), bin2hex($crypt->encrypt($this->imgURL)), bin2hex($crypt->encrypt($this->email)), $this->googleId, $this->token);
            $stmt->execute();
            if ($stmt) return true;
        }
        return false;
    }

    public function logOut () {
        session_unset();
    }

    public function insertKey() {
        $db = new Database(KDBHST, KDBUSR, KDBPASS, KDBNAME);
        $query = "INSERT INTO tracklog_key (`key`, googleId) VALUES (?, ?)";
        if ($stmt = $db->getConnection()->prepare($query)) {
            $stmt->bind_param('ss', $this->key, $this->googleId);
            $stmt->execute();
            if ($stmt) return true;
            else var_dump($stmt->error);
        }
    }

    public function getKey() {
        $db = new Database(KDBHST, KDBUSR, KDBPASS, KDBNAME);
        $query = "SELECT `key` FROM tracklog_key k WHERE k.googleId = ?";
        if ($stmt = $db->getConnection()->prepare($query)) {
            $stmt->bind_param('s', $_SESSION['googleId']);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($key);
            if ($stmt->num_rows == 1) {
                $stmt->fetch();
                return $key;
            } else return null;
        }
    }

    public static function getUserByToken($token, $conn) {
        $query = "SELECT u.id FROM user u WHERE u.token = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id);
            if ($stmt->num_rows == 1) {
                $stmt->fetch();
                return $id;
            } else return null;
        }
    }
}
