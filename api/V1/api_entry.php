<?php
require('../../includes/initialize.php');
require('objects/Entry.php');
require('../../includes/objects/User.php');

use PHP_Crypt\PHP_Crypt as PHP_Crypt;

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

$crypt = new PHP_Crypt($key);

if (isset($input)) $values = (array)$input;
else $values = $_POST;

$where = '';

if (isset($_GET['startDate'])) {
    $startDate = date('Y-m-d', strtotime($_GET['startDate'])).' 00:01:00';
    if(isset($_GET['endDate'])) {
        $endDate = date('Y-m-d', strtotime($_GET['endDate'])).' 23:59:00';
        $where .= ' AND (d.date BETWEEN \''.$startDate.'\' AND \''.$endDate.'\')';
    } else {
        $where .= ' AND d.date > \''.$startDate.'\'';
    }
} else if (isset($_GET['endDate'])) {
    $where .= ' AND d.date < \''.$endDate.'\'';
}

if (isset($_GET['dataTypes'])) {
    $dataTypes = explode(',', $_GET['dataTypes']);
    $where .= ' AND (';
    foreach ($dataTypes as $k => $dataType) {
        $dataType= bin2hex($crypt->encrypt($dataType));
        if ($k > 0) {
            $where .= ' OR dt.name = \''.$dataType.'\'';
        } else {
            $where .= ' dt.name = \''.$dataType.'\'';
        }
    }
    $where .= ')';
}

if (isset($_GET['companies'])) {
    $companies = explode(',', $_GET['companies']);
    $where .= ' AND (';
    foreach ($companies as $k => $company) {
        $company= bin2hex($crypt->encrypt($company));
        if ($k > 0) {
            $where .= ' OR c.name = \''.$company.'\'';
        } else {
            $where .= ' c.name = \''.$company.'\'';
        }
    }
    $where .= ')';
}

if (isset($_GET['category'])) {
    $category = bin2hex($crypt->encrypt($_GET['category']));
    $where .= ' AND ca.name = \''.$category.'\'';
}

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
    $date = isset($values['date']) ? htmlentities($values['date']) : date('Y-m-d');
    $date = str_replace('/', '-', $date);
    $dateInfo = date_parse($date);
    if ($dateInfo['hour'] == false) {
        $time = isset($values['time']) ? ' '.htmlentities($values['time']) : ' '.date('H:i:s');
    } else {
        $time = '';
    }
    $date = date('Y-m-d H:i:s', strtotime($date.$time));
    $entry->date = $date;
    $entry->userId = $userId;

    switch ($method) {
        case 'GET':
            if ($entry->id != null) {
                $result['item'] = $entry->detail($crypt, $userId);
                if (!is_array($result['item'])) {
                    $resultCode = $result['item'];
                }
            } else {
                $apiRoot = APIROOT.'/entry.'.$friendlyAccept.'?api_key='.$apiKey;
                $status = isset($_GET['state']) ? $_GET['state'] : 0;
                $limit = isset($_GET['limit']) ? $_GET['limit'] : 0;
                $offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
                $allItems = Entry::getEntries($crypt, $status, $db->getConnection(), $key, $userId, $where);
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
            $result['item'] = $entry->save($crypt, $key, $userId, 'edit');
            if (is_array($result['item'])) {
                $resultCode = 201;
            } else {
                $resultCode = $result;
            }
            break;
        case 'DELETE':
            $resultCode = $entry->delete($crypt, $key, $userId);
            break;
        case 'POST':
            $entry->state = $entry->description == null ? 1 : 2;
            $result['item'] = $entry->save($crypt, $key, $userId, 'insert');
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

switch($accept) {
    case 'application/json':
        print_r(json_encode($result));
        break;
}

