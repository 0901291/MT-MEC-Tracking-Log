<?php
include("../../includes/initialize.php");
include("../../includes/objects/Entry.php");

$method = $_SERVER["REQUEST_METHOD"];
$accept = $_SERVER["HTTP_ACCEPT"];
$key = $_POST['api_key'];

$id = (isset($_GET['id']) ? $_GET['id'] : null);

$input = json_decode(file_get_contents("php://input"));
$post = $_POST;

$entry = new Entry($db);

switch ($method) {
    case "GET":
        if (!isset($id)) $result = $entry->getEntries(0, $accept, $db);
        elseif (isset($id)) {
            $entry->id = $id;
            $entry->detail();
        }
        break;

}