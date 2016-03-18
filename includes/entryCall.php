<?php
require('initialize.php');
$id = isset($_POST['id']) ? $_POST['id'] : '';
$url = ROOT.'/api/V1/entry/'.$id;
$key = $_SESSION['token'];

if (isset($_POST['method'])) {
    $client = new GuzzleHttp\Client();
    switch ($_POST['method']) {
        case 'insert':
            $response = $client->request('POST', $url, ['json' => $_POST, 'query' => ['api_key' => $key]]);
            $code = $response->getStatusCode();
            break;
        case 'edit':
            $response = $client->request('PUT', $url, ['json' => $_POST, 'query' => ['api_key' => $key]]);
            $code = $response->getStatusCode();
            if ($code == 200) {
                header('Location: '.ROOT.'/entries');
                die();
            }
            break;
        case 'delete':
            $response = $client->request('DELETE', $url, ['query' => ['api_key' => $key, 'id' => $id]]);
            $code = $response->getStatusCode();
            if ($code == 204) {
                header('Location: '.ROOT.'/entries');
                die();
            }
            break;
    }
}

header('Location: '.ROOT);