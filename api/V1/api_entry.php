<?php
require('../../includes/initialize.php');
require('objects/Entry.php');
require('../../includes/objects/User.php');

$method = $_SERVER['REQUEST_METHOD'];
$accept = isset($_GET['accept']) ? 'application/'.$_GET['accept'] : $_SERVER['HTTP_ACCEPT'];
$friendlyAccept = str_replace('application/', '', $accept);
$apiKey = isset($_GET['api_key']) ? $_GET['api_key'] : null;
$userId = User::getUserByToken($apiKey, $db->getConnection());
$googleId = User::getGoogleIdByUserId($userId, $db->getConnection());
$key = User::getKey($googleId);

$input = json_decode(file_get_contents('php://input'));
$post = $_POST;
$values = null;
$result = [];
$resultCode = 200;

$entry = new Entry($db->getConnection());
$user = new User($db->getConnection());

if (isset($input)) $values = (array)$input;
else $values = $_POST;

define('APIROOT', ROOT.'/api/v1');

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
    $date = (isset($values['date']) ? htmlentities($values['date']) : date('Y-m-d'));
    $time = (isset($values['time']) ? htmlentities($values['time']) : date('H:i:s'));
    $entry->date = date('Y-m-d H:i:s', strtotime($date.' '.$time));
    $entry->userId = $userId;

    switch ($method) {
        case 'GET':
            if ($entry->id != null) {
                $result['item'] = $entry->detail($key, $userId);
                if (!is_array($result['item'])) {
                    $resultCode = $result['item'];
                }
            } else {
                $apiRoot = APIROOT.'/entry.'.$friendlyAccept.'?api_key='.$apiKey;
                $status = isset($_GET['state']) ? $_GET['state'] : 0;
                $limit = isset($_GET['limit']) ? $_GET['limit'] : 0;
                $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
                $allItems = Entry::getEntries($status, $db->getConnection(), $key, $userId);
                $total = count($allItems);
                $limit = $limit == 0 || $limit > $total ? $total : $limit;
                $result['items'] = array_slice($allItems, $offset, $limit);
                $current = count($result['items']);
                $totalPages = ceil($total / $current);
                $currentPage = ceil(($offset + 1) / $limit);
                $nextPageStart = ($offset + $limit) > $total ? $total : ($offset + $limit);
                $nextPage = ceil(($offset + 1) / $limit) + 1 > ceil($total / $current) ? ceil($total / $current) : ceil(($offset + 1) / $limit) + 1;
                $previousPageStart = $offset - $limit < 0 ? 0 : $offset - $limit;
                $previousPage = ceil(($previousPageStart + 1) / $limit) < 1 ? 1 : ceil(($previousPageStart + 1)/ $limit);
                if ($currentPage < 1) $currentPage = 1;
                $result['pagination']['currentPage'] = $currentPage;
                $result['pagination']['currentItems'] = $current;
                $result['pagination']['totalPages'] = $totalPages;
                $result['pagination']['totalItems'] = $total;
                $nextPagination = [
                    'rel' => 'next',
                    'page' => $nextPage,
                    'href' => $apiRoot.($limit < $total ? '&offset='.$nextPageStart.'&limit='.$limit : '')
                ];
                $previousPagination = [
                    'rel' => 'previous',
                    'page' => $previousPage,
                    'href' => $apiRoot.($limit < $total ? '&offset='.$previousPageStart.'&limit='.$limit : '')
                ];
                if ($accept == 'application/json') {
                    $result['links'] = [[
                        'rel' => 'self',
                        'href' => $apiRoot
                    ]];
                    $result['pagination']['links'] = [$previousPagination, $nextPagination];
                }
            }
            break;
        case 'PUT':
            $entry->state = $entry->description == null ? 1 : 2;
            $result['item'] = $entry->save($key, $userId, 'edit');
            if (is_array($result['item'])) {
                $resultCode = 201;
            } else {
                $resultCode = $result;
            }
            break;
        case 'DELETE':
            $result = $entry->delete($key, $userId);
            break;
        case 'POST':
            $entry->state = $entry->description == null ? 1 : 2;
            $result['item'] = $entry->save($key, $userId, 'insert');
            if (is_array($result['item'])) {
                $resultCode = 201;
            } else {
                $resultCode = $result;
            }
            break;
    }
} else {
    $resultCode = 400;
}

if ($accept != 'application/json') {
    $resultCode = 415;
} else {
    header('Content-type: application/json');
}

switch ($resultCode) {
    case 400:
        $result = ["error" => 400, "message" => "Bad request, api_key not valid."];
        http_response_code(400);
        break;
    case 404:
        $result = ["error" => 404, "message" => "Content not found"];
        http_response_code(404);
        break;
    case 403:
        $result = ["error" => 403, "message" => "Access denied"];
        http_response_code(403);
        break;
    case 415:
        $result = ["error" => 415, "message" => "We don't support this content-type."];
        http_response_code(415);
        break;
    case 204:
        $result = ["code" => 204, "message" => "No content"];
        http_response_code(204);
        break;
}

print_r(json_encode($result));

