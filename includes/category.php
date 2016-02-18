<?php
include ("initialize.php");

$name = (isset($_POST['name']) ? $_POST['name'] : null);
$function = (isset($_POST['function']) ? $_POST['function'] : null);
$id = (isset($_POST['id']) ? $_POST['id'] : null);

if (isLoggedIn()) {
    if ($function != null) {
        if ($function == "create") {
            create($name, $conn);
        } else if ($function == "delete") {
            delete($id, $conn);
        } else if ($function == "edit") {
            edit($id, $name, $conn);
        }
    }
}

function create ($name,$conn) {
    $query = "INSERT INTO category (name, user_id) VALUES (?, ?)";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('ss', $name, $_SESSION['userId']);
        $stmt -> execute();
        if ($stmt) {
            return true;
        }
    }
    return false;
}

function edit ($id, $name, $conn) {
    $query = "UPDATE category SET name = ? WHERE id = ?";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('ss', $name, $id);
        $stmt -> execute();
        if ($stmt) {
            return true;
        }
    }
    return false;
}

function delete ($id, $conn) {
    $query = "DELETE FROM category WHERE id = ?";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param('s', $id);
        $stmt -> execute();
        if ($stmt) {
            return true;
        }
    }
    return false;
}