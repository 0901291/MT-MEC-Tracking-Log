<?php
include("initialize.php");

$name = (isset($_POST['name']) ? $_POST['name'] : null);
$imgURL = (isset($_POST['img']) ? $_POST['img'] : null);
$email = (isset($_POST['email']) ? $_POST['email'] : null);
$googleId = (isset($_POST['id']) ? $_POST['id'] : null);

if ($name != null && $imgURL != null && $email != null && $googleId != null) {
    if (checkUser ($googleId, $conn)) {
        logIn($googleId, $conn);
    } else {
        if (createUser ($googleId, $name, $email, $imgURL, $conn)) {
            logIn($googleId, $conn);
        }
    }
}

function checkUser ($googleId, $conn) {
    $query = "SELECT * FROM user WHERE user.googleId = ?";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('s', $googleId);
        $stmt -> execute();
        $stmt -> store_result();
        if  ($stmt -> num_rows > 0) {
            return true;
        }
    }
    return false;
}

function createUser ($googleId, $name, $email, $imgURL, $conn) {
    $query = "INSERT INTO user (name, imgURL, email, googleId) VALUES(?, ?, ?, ?)";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('ssss', $name, $imgURL, $email, $googleId);
        $stmt -> execute();
        if ($stmt) {
            return true;
        }
    }
    return false;
}

function logIn ($googleId, $conn) {
    $query = "SELECT name, imgURL, id FROM user WHERE user.googleId = ?";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('s', $googleId);
        $stmt -> execute();
        $stmt -> store_result();
        $stmt -> bind_result($name, $imgURL, $id);
        if ($stmt -> num_rows == 1) {
            $stmt -> fetch();
            $_SESSION['userId'] = $id;
            $_SESSION['name'] = $name;
            $_SESSION['imgURL'] = $imgURL;
        }
    }
}

function logOut () {
    session_unset();
}
