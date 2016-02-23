<?php
require("initialize.php");
require("objects/User.php");
require('crypto/CryptLib/CryptLib.php');

if ($_POST) {
    $name = isset($_POST['name']) ? htmlentities($_POST['name']) : null;
    $imgURL = isset($_POST['img']) ? htmlentities($_POST['img']) : null;
    $email = isset($_POST['email']) ? htmlentities($_POST['email']) : null;
    $googleId = isset($_POST['id']) ? htmlentities($_POST['id']) : null;

    $user = new User($db->getConnection());
    if (isset($_POST['method'])) {
        switch ($_POST['method']) {
            case "logOut":
                if (isLoggedIn()) {
                    $user->logOut();
                    return true;
                }
                break;
            case "logIn":
                if (!isLoggedIn()) {
                    $user->googleId = $googleId;
                    if ($user->logIn()) return true;
                    else {
                        $cryptLib = new \CryptLib\CryptLib;
                        $user->key = $cryptLib->getRandomToken(16);
                        $user->insertKey();
                        $user->name = $name;
                        $user->imgURL = $imgURL;
                        $user->email = $email;
                        if ($user->insert()) if (!isLoggedIn()) if ($user->logIn()) return true;
                    }
                }
                break;
        }
    }
}
