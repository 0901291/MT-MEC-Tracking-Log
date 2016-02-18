<?php
require("initialize.php");
$typeName = ($type == "company" ? "company_id" : "dataType_id");
$query = "INSERT INTO ".$type."_has_data (data_id, ".$typeName.") VALUES (?, (SELECT max(id) FROM $type))";
if ($stmt = $conn -> prepare($query)) {
    $stmt -> bind_param('s', $dataId);
    $stmt -> execute();
    if ($stmt) {
        return true;
    }
}