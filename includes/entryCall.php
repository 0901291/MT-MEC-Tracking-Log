<?php
include_once("objects/Entry.php");
include_once("initialize.php");

if ($_POST) {
    if (isLoggedIn()) {
        $db = new database();
        $entry = new entry($db->getConnection());

        switch ($_POST["method"]) {
            case "insert":
                $entry -> title = (isset($_POST['title']) ? $_POST['title'] : null);
                $entry -> description = (isset($_POST['description']) ? $_POST['description'] : null);
                $entry -> imgURL = (isset($_POST['imgURL']) ? $_POST['imgURL'] : null);
                $entry -> lng = (isset($_POST['lng']) ? $_POST['lng'] : null);
                $entry -> lat = (isset($_POST['lat']) ? $_POST['lat'] : null);
                $entry -> companies = (isset($_POST['companies']) ? $_POST['companies'] : null);
                $entry -> dataTypes = (isset($_POST['dataTypes']) ? $_POST['dataTypes'] : null);
                $entry -> category = (isset($_POST['category']) ? $_POST['category'] : null);
                $entry -> state = ($entry -> description == null ? 1 : 2);
                $date = (isset($_POST['date']) ? $_POST['date'] : date("Y-m-d"));
                $time = (isset($_POST['time']) ? $_POST['time'] : date("H:i:s"));
                $entry -> date = date('Y-m-d H:i:s', strtotime("$date $time"));
                if($entry -> insert()) {
                    header("location: ../../index.php");
                }
                break;
            case "edit":
                if (is_numeric($_POST['id'])) {
                    $entry -> id = $_POST['id'];
                    $entry -> title = (isset($_POST['title']) ? $_POST['title'] : null);
                    $date = (isset($_POST['date']) ? $_POST['date'] : date("Y-m-d"));
                    $time = (isset($_POST['time']) ? $_POST['time'] : date("H:i:s"));
                    $entry -> date = date('Y-m-d H:i:s', strtotime("$date $time"));
                    $entry -> description = (isset($_POST['description']) ? $_POST['description'] : null);
                    $entry -> imgURL = (isset($_POST['imgURL']) ? $_POST['imgURL'] : null);
                    $entry -> lng = (isset($_POST['lng']) ? $_POST['lng'] : null);
                    $entry -> lat = (isset($_POST['lat']) ? $_POST['lat'] : null);
                    $entry -> companies = (isset($_POST['companies']) ? $_POST['companies'] : null);
                    $entry -> dataTypes = (isset($_POST['dataTypes']) ? $_POST['dataTypes'] : null);
                    $entry -> category = (isset($_POST['category']) ? $_POST['category'] : null);
                    $entry -> state = ($entry -> description == "" ? 1 : 2);
                    if ($entry -> edit()) {
                        header("location: ".ROOT."/entries/".$_POST["id"]);
                    }
                }
                break;
            case "delete":
                if (is_numeric($_POST['id'])) {
                    $entry -> id = $_POST['id'];
                    if ($entry -> delete()) {
                        header("location: ../../entries.php");
                    }
                }
                break;
            case "detail":
                if (is_numeric($_POST['id'])) {
                    $entry -> id = $_POST['id'];
                    if ($data = $entry -> detail()) {
                        print_r($data);
                    }
                }
                break;
        }
    }
}