<?php
include_once("initialize.php");
include_once("objects/User.php");

if ($_POST) {
    $name = (isset($_POST['name']) ? htmlentities($_POST['name']) : null);
    $imgURL = (isset($_POST['img']) ? htmlentities($_POST['img']) : null);
    $email = (isset($_POST['email']) ? htmlentities($_POST['email']) : null);
    $googleId = (isset($_POST['id']) ? htmlentities($_POST['id']) : null);

    $user = new user($db->getConnection());

    if (isset($_POST['method'])) {
        switch ($_POST['method']) {
            case "logOut":
                if (isLoggedIn()) {
                    $user -> logOut();
                    return true;
                }
                break;
            case "logIn":
                if (!isLoggedIn()) {
//                    echo "login1";
                    $user -> googleId = $googleId;
                    if($user -> logIn()) {

                    } else {
                        $user -> name = $name;
                        $user -> imgURL = $imgURL;
                        $user -> email = $email;
                        if ($user -> insert()) {
                            if (!isLoggedIn()) {
                                if($user -> logIn()) {
                                    // iets?
                                }
                            }
                        }
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