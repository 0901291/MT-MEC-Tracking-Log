<?php
require('../../includes/initialize.php');
require(ROOT.'/includes/objects/Entry.php');
require(ROOT.'/includes/objects/User.php');

$method = $_SERVER['REQUEST_METHOD'];
$accept = $_SERVER['HTTP_ACCEPT'];
$key = (isset($_GET['api_key']) ? $_GET['api_key'] : null);

$userId = User::getUserByToken($key, $db->getConnection());

$input = json_decode(file_get_contents('php://input'));
$post = $_POST;
$values = null;
$result = ["No method given."];

$entry = new Entry($db);
$user = new User($db);

if (isset($input)) $values = $input;
    elseif (sizeof($_POST) > 0) $values = $_POST;

if ($userId != null) {
    $entry->id = (isset($_GET['id']) ? $_GET['id'] : null);
    $entry->title = (isset($values['title']) ? htmlentities($values['title']) : null);
    $entry->description = (isset($values['description']) ? htmlentities($values['description']) : null);
    $entry->imgURL = (isset($values['imgURL']) ? htmlentities($values['imgURL']) : null);
    $entry->lng = (isset($values['lng']) ? htmlentities($values['lng']) : null);
    $entry->lat = (isset($values['lat']) ? htmlentities($values['lat']) : null);
    $entry->companies = (isset($values['companies']) ? $values['companies'] : null);
    $entry->dataTypes = (isset($values['dataTypes']) ? $values['dataTypes'] : null);
    $entry->category = (isset($values['category']) ? htmlentities($values['category']) : null);
    $date = (isset($values['date']) ? htmlentities($values['date']) : date("Y-m-d"));
    $time = (isset($values['time']) ? htmlentities($values['time']) : date("H:i:s"));
    $entry->date = date('Y-m-d H:i:s', strtotime("$date $time"));

    switch ($method) {
        case 'GET':
            if ($entry->id != null) {
                $result = $entry->detail();
            } else {
                $status = (isset($_GET['state']) ? $_GET['state'] : 0);
                $result = Entry::getEntries($status, $db->getConnection());
            }
            break;
        case 'PUT':
            $entry->state = ($entry->description == null ? 1 : 2);
            $result = $entry->edit();
                break;
        case 'DELETE':
            $result = $entry->delete();
            break;
        case 'POST':
            $entry->state = ($entry->description == null ? 1 : 2);
            $entry->insert();
            break;
    }
} else {
    http_response_code(403);
    $result = ['Acces denied: invalid api_key'];
}

switch ($accept) {
    case 'application/json':
        print_r(json_encode($result));
        break;
    default:
        print_r(["We don't support this content-type."]);
        http_response_code(415);
}

