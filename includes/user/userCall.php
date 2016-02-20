<?php
include_once ("../initialize.php");
include_once ("user.php");

if ($_POST) {
    $name = (isset($_POST['name']) ? $_POST['name'] : null);
    $imgURL = (isset($_POST['img']) ? $_POST['img'] : null);
    $email = (isset($_POST['email']) ? $_POST['email'] : null);
    $googleId = (isset($_POST['id']) ? $_POST['id'] : null);

    $db = new database();
    $user = new user($db->getConnection());

    if (isset($_POST['method'])) {
        switch ($_POST['method']) {
            case "logOut":
                if (isLoggedIn()) {
                    $user -> logOut();
                }
                break;
            case "logIn":
                if (!isLoggedIn()) {
                    $user -> googleId = $googleId;
                    if($user -> logIn()) {
                        // iets?
                    }
                }
                break;
            case "insert":
                $user -> name = $name;
                $user -> imgURL = $imgURL;
                $user -> email = $email;
                $user -> googleId = $googleId;
                if ($user -> insert()) {
                    if (!isLoggedIn()) {
                        if($user -> logIn()) {
                            // iets?
                        }
                    }
                }
                break;
            case "edit":
                break;
            case "delete":
                break;
        }
    }
}