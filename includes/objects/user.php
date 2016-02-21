<?php

class User
{
    private $conn;

    public $googleId;
    public $name;
    public $imgURL;
    public $email;

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
                $_SESSION['userId'] = $id;
                $_SESSION['name'] = $name;
                $_SESSION['imgURL'] = $imgURL;
                $_SESSION['email'] = $email;
                $_SESSION['token'] = $token;
                return true;
            }
        }
        return false;
    }

    public function insert () {
        $query = "INSERT INTO ".DB_PREFIX."user (name, imgURL, email, googleId) VALUES(?, ?, ?, ?)";
        if ($stmt = $this -> conn -> prepare($query)) {
            $stmt -> bind_param('ssss', $this->name, $this->imgURL, $this->email, $this->googleId);
            $stmt -> execute();
            if ($stmt) return true;
        }
        return false;
    }

    public function logOut () {
        session_unset();
    }
}
