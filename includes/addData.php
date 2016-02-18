<?php
require("dataInfo.php");

$description = (isset($_POST['description']) || !empty($_POST['description']) ? $_POST['description'] : null);
$category = (isset($_POST['category']) ? $_POST['category'] : null);
$dataTypes = (isset($_POST['data-types']) ? $_POST['data-types'] : null);
$companies = (isset($_POST['companies']) ? $_POST['companies'] : null);
$title = (isset($_POST['title']) ? $_POST['title'] : null);
$date = (isset($_POST['date']) ? $_POST['date'] : null);
$time = (isset($_POST['time']) ? $_POST['time'] : null);
$lat = (isset($_POST['lat']) ? $_POST['lat'] : null);
$lng = (isset($_POST['lng']) ? $_POST['lng'] : null);
$state = ($description == null ? 1 : 2);
$dateTime = date('Y-m-d H:i:s', strtotime("$date $time"));

if (isLoggedIn()) {
    $query = "INSERT INTO ".DB_PREFIX."data (title, date, description, lat, lng, category_id, user_id, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn -> prepare($query)) {
        $stmt -> bind_param("ssssssss", $title, $dateTime, $description, $lat, $lng, $category, $_SESSION['userId'], $state);
        $stmt -> execute();

        if ($dataTypes != null) {
            foreach ($dataTypes as $dataType) {
                $query = "INSERT INTO ".DB_PREFIX."dataType_has_data (dataType_id, data_id) VALUES (?, ".$stmt->insert_id.")";
                if ($stm = $conn -> prepare($query)) {
                    $stm -> bind_param('s', $dataType);
                    $stm -> execute();
                }
            }
        }

        if ($companies != null) {
            foreach ($companies as $company) {
                $query = "INSERT INTO ".DB_PREFIX."company_has_data (company_id, data_id) VALUES (?, ".$stmt->insert_id.")";
                if ($stm = $conn -> prepare($query)) {
                    $stm -> bind_param('s', $company);
                    $stm -> execute();
                }
            }
        }

        header("Location: ../index.php");
    }
}