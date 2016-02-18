<?php
include ("initialize.php");

$name = (isset($_POST['name']) ? $_POST['name'] : null);
$function = (isset($_POST['function']) ? $_POST['function'] : null);
$dataId = (isset($_POST['dataId']) ? $_POST['dataId'] : null);
$id = (isset($_POST['id']) ? $_POST['id'] : null);
$type = (isset($_POST['type']) ? $_POST['type'] : null);

if (isLoggedIn()) {
    if ($function != null) {
        if ($function == "create") {
            create($type,$dataId, $name, $conn);
        } else if ($function == "delete") {
            delete($type, $id, $conn);
        } else if ($function == "edit") {
            edit($type, $id, $name, $conn);
        }
    }
}

function create ($type, $name, $conn) {
    $query = "INSERT INTO $type (name, user_id) VALUES (?, ?)";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('ss', $name, $_SESSION['userId']);
        $stmt -> execute();
    }
    return false;
}

function edit ($type, $id, $name, $conn) {
    $query = "UPDATE $type SET name = ? WHERE id = ?";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('ss', $name, $id);
        $stmt -> execute();
        if ($stmt) {
            return true;
        }
    }
    return false;
}

function delete ($type, $id, $conn) {
    $query = "DELETE FROM ".$type."_has_data WHERE ".$type."_id = ?";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('s', $id);
        $stmt -> execute();
        if ($stmt) {
            $query = "DELETE FROM ".$type." WHERE id = ?";
            if ($stmt = $conn -> prepare($query)) {
                $stmt -> bind_param('s', $id);
                $stmt -> execute();
                if ($stmt) {
                    return true;
                }
            }
        }
    }
    return false;
}