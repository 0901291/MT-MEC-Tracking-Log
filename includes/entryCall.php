<?php
require("objects/Entry.php");
require("initialize.php");

if ($_POST) {
    if (isLoggedIn()) {
        $entry = new entry($db->getConnection());
        if ($_POST["method"] == "insert" || $_POST["method"] == "edit") {
            $entry->title = (isset($_POST['title']) ? htmlentities($_POST['title']) : null);
            $entry->description = (isset($_POST['description']) ? htmlentities($_POST['description']) : null);
            $entry->imgURL = (isset($_POST['imgURL']) ? htmlentities($_POST['imgURL']) : null);
            $entry->lng = (isset($_POST['lng']) ? htmlentities($_POST['lng']) : null);
            $entry->lat = (isset($_POST['lat']) ? htmlentities($_POST['lat']) : null);
            $entry->companies = (isset($_POST['companies']) ? $_POST['companies'] : null);
            $entry->dataTypes = (isset($_POST['dataTypes']) ? $_POST['dataTypes'] : null);
            $entry->category = (isset($_POST['category']) ? htmlentities($_POST['category']) : null);
            $entry->state = ($entry -> description == null ? 1 : 2);
            $date = (isset($_POST['date']) ? htmlentities($_POST['date']) : date("Y-m-d"));
            $time = (isset($_POST['time']) ? htmlentities($_POST['time']) : date("H:i:s"));
            $entry->date = date('Y-m-d H:i:s', strtotime("$date $time"));
        }
        switch ($_POST["method"]) {
            case "insert":
                if ($entry -> insert()) header("location: ../index.php");
                break;
            case "edit":
                if (is_numeric($_POST['id'])) {
                    $entry -> id = $_POST['id'];
                    if ($entry -> edit()) header("location: ".ROOT."/entries/".$_POST["id"]);
                }
                break;
            case "delete":
                if (is_numeric($_POST['id'])) {
                    $entry -> id = $_POST['id'];
                    if ($entry -> delete()) header("location: ../entries.php");
                }
                break;
        }
    }
}
