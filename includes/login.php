<?php
include("initialize.php");
$db = new database();
$conn = $db ->getConnection();

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
    $query = "SELECT * FROM ".DB_PREFIX."user WHERE ".DB_PREFIX."user.googleId = ?";
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