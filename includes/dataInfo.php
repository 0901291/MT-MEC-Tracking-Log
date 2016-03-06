<?php
require("initialize.php");
use PHP_Crypt\PHP_Crypt as PHP_Crypt;
$crypt = new PHP_Crypt($_SESSION['key']);

$name = isset($_POST['name']) ? bin2hex($crypt->encrypt($_POST['name'])) : null;
$function = isset($_POST['function']) ? $_POST['function'] : null;
$dataId = isset($_POST['dataId']) ? bin2hex($crypt->encrypt($_POST['dataId'])) : null;
$id = isset($_POST['id']) ? $_POST['id'] : null;
$type = isset($_POST['type']) ? $_POST['type'] : null;

if (isLoggedIn()) {
    if ($function != null) {
        if ($function == "create") {
            create($type, $name, $db->getConnection());
        } else if ($function == "delete") {
            delete($type, $id, $db->getConnection());
        } else if ($function == "edit") {
            edit($type, $id, $name, $db->getConnection());
        }
    }
}

function create ($type, $name, $conn) {
    $query = "INSERT INTO ".DB_PREFIX."$type (name, user_id) VALUES (?, ?)";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param('ss', $name, $_SESSION['userId']);
        $stmt->execute();
        if ($stmt)
            echo $stmt->insert_id;
            return true;
    }
    return false;
}

function edit ($type, $id, $name, $conn) {
    $query = "UPDATE ".DB_PREFIX."$type SET name = ? WHERE id = ?";
    if ($stmt = $conn -> prepare($query)) {
        $stmt->bind_param('ss', $name, $id);
        $stmt->execute();
        if ($stmt) return true;
    }
    return false;
}

function delete ($type, $id, $conn) {
    if ($type == "company" || $type == "datatype") {
        $query = "DELETE FROM ".DB_PREFIX.$type."_has_data WHERE ".$type."_id = ?";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param('s', $id);
            $stmt->execute();
        }
    }
    $query = "DELETE FROM ".DB_PREFIX.$type." WHERE id = ?";
    if ($stmt = $conn -> prepare($query)) {
        $stmt->bind_param('s', $id);
        $stmt->execute();
        if ($stmt) return true;
    }
    return false;
}
