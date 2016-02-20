<?php
require("initialize.php");
//
//$description = (isset($_POST['description']) || !empty($_POST['description']) ? $_POST['description'] : null);
//$category = (isset($_POST['category']) ? $_POST['category'] : null);
//$dataTypes = (isset($_POST['data-types']) ? $_POST['data-types'] : null);
//$companies = (isset($_POST['companies']) ? $_POST['companies'] : null);
//$title = (isset($_POST['title']) ? $_POST['title'] : null);
//$date = (isset($_POST['date']) ? $_POST['date'] : null);
//$time = (isset($_POST['time']) ? $_POST['time'] : null);
//$lat = (isset($_POST['lat']) ? $_POST['lat'] : null);
//$lng = (isset($_POST['lng']) ? $_POST['lng'] : null);
//$state = ($description == null ? 1 : 2);
//$dateTime = date('Y-m-d H:i:s', strtotime("$date $time"));
//
//if (isLoggedIn()) {
//    $query = "INSERT INTO ".DB_PREFIX."data (title, date, description, lat, lng, category_id, user_id, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
//    if ($stmt = $conn -> prepare($query)) {
//        $stmt -> bind_param("ssssssss", $title, $dateTime, $description, $lat, $lng, $category, $_SESSION['userId'], $state);
//        $stmt -> execute();
//
//        if ($dataTypes != null) {
//            foreach ($dataTypes as $dataType) {
//                $query = "INSERT INTO ".DB_PREFIX."datatype_has_data (dataType_id, data_id) VALUES (?, ".$stmt->insert_id.")";
//                if ($stm = $conn -> prepare($query)) {
//                    $stm -> bind_param('s', $dataType);
//                    $stm -> execute();
//                }
//            }
//        }
//
//        if ($companies != null) {
//            foreach ($companies as $company) {
//                $query = "INSERT INTO ".DB_PREFIX."company_has_data (company_id, data_id) VALUES (?, ".$stmt->insert_id.")";
//                if ($stm = $conn -> prepare($query)) {
//                    $stm -> bind_param('s', $company);
//                    $stm -> execute();
//                }
//            }
//        }
//
//        header("Location: ../index.php");
//    }
//}


class entry {
    public static function getEntries($status, $conn) {
        $status = htmlentities($status);
        $state = ($status != 0 ? "AND WHERE = ".$status : "");

        if (is_numeric($status)) {
            $query = "SELECT d.id, d.title, d.date, d.description, d.imgURL, d.lng, d.lat, group_concat(DISTINCT c.name), group_concat(DISTINCT c.id), group_concat(DISTINCT dt.name), group_concat(DISTINCT dt.id), ca.name from ".DB_PREFIX."data d
            LEFT OUTER JOIN (".DB_PREFIX."datatype_has_data dhd
                LEFT OUTER JOIN ".DB_PREFIX."datatype dt
                ON dt.id = dhd.dataType_id)
            on dhd.data_id = d.id
            LEFT OUTER JOIN (".DB_PREFIX."company_has_data chd
                LEFT OUTER JOIN ".DB_PREFIX."company c
                ON c.id = chd.company_id)
            on chd.data_id = d.id
            LEFT OUTER JOIN ".DB_PREFIX."category ca
            ON ca.id = d.category_id
            WHERE d.user_id = ? ".$state."
            GROUP BY d.id
            ORDER BY d.date ASC";
            if ($stmt = $conn -> prepare($query)) {
                $stmt -> bind_param("i", $_SESSION['userId']);
                $stmt -> execute();
                $stmt -> store_result();
                $stmt -> bind_result($id, $title, $date, $description, $imgURL, $lng, $lat, $company, $companyId, $dataType, $dataTypeId, $category);
                $array = [];
                while ($stmt -> fetch()) {
                    $companies = explode(",", $company);
                    $dataTypes = explode(",", $dataType);
                    $array[] = ["id" => $id,
                        "title" => $title,
                        "date" => $date,
                        "description" => $description,
                        "imgURL" => $imgURL,
                        "location" => [
                            "lat" => $lat,
                            "lng" => $lng
                        ],
                        "companies" => $companies,
                        "dataTypes" => $dataTypes,
                        "category" => $category
                    ];
                }
                return json_encode($array);
            }
        }
        return false;
    }

    public function insert ($data, $dataTypes, $companies, $conn) {
        $query = "INSERT INTO ".DB_PREFIX."data (title, date, description, lat, lng, category_id, user_id, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn -> prepare($query)) {
            $stmt -> bind_param("ssssssss", $data["title"], $data["dateTime"], $data["description"], $data["lat"], $data["lng"], $data["category"], $_SESSION['userId'], $data["state"]);
            $stmt -> execute();
            if ($dataTypes != null) {
                foreach ($dataTypes as $dataType) {
                    $query = "INSERT INTO ".DB_PREFIX."datatype_has_data (dataType_id, data_id) VALUES (?, ".$stmt->insert_id.")";
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
        }
    }

    public function edit ($data, $id, $conn) {
        $query = "UPDATE ".DB_PREFIX."data SET title = ?, date = ?, description = ?, lat = ?, lng = ?, category_id = ?, user_id = ?, state = ? WHERE id = ?";
        if ($stmt = $conn -> prepare($query)) {
            $stmt -> bind_param("ssssssss", $data["title"], $data["dateTime"], $data["description"], $data["lat"], $data["lng"], $data["category"], $_SESSION['userId'], $data["state"], $id);
            $stmt -> execute();
            if ($data['dataTypes'] != null) {
                foreach ($data['dataTypes'] as $dataType) {
                    $query = "UPDATE ".DB_PREFIX."datatype SET name = ? WHERE id = ?";
                    if ($stm = $conn -> prepare($query)) {
                        $stm -> bind_param('si', $dataType['name'], $dataType['id']);
                        $stm -> execute();
                    }
                }
            }
            if ($data['companies'] != null) {
                foreach ($data['companies'] as $company) {
                    $query = "UPDATE ".DB_PREFIX."company SET name = ? WHERE id = ?";
                    if ($stm = $conn -> prepare($query)) {
                        $stm -> bind_param('si', $company['name'], $company['id']);
                        $stm -> execute();
                    }
                }
            }
        }
    }

    public function detail ($id, $conn) {
        $query = "SELECT d.id, d.title, d.date, d.description, d.imgURL, d.lng, d.lat, group_concat(DISTINCT c.name), group_concat(DISTINCT c.id), group_concat(DISTINCT dt.name), group_concat(DISTINCT dt.id), ca.name from ".DB_PREFIX."data d
            LEFT OUTER JOIN (".DB_PREFIX."datatype_has_data dhd
                LEFT OUTER JOIN ".DB_PREFIX."datatype dt
                ON dt.id = dhd.dataType_id)
            on dhd.data_id = d.id
            LEFT OUTER JOIN (".DB_PREFIX."company_has_data chd
                LEFT OUTER JOIN ".DB_PREFIX."company c
                ON c.id = chd.company_id)
            on chd.data_id = d.id
            LEFT OUTER JOIN ".DB_PREFIX."category ca
            ON ca.id = d.category_id
            WHERE d.user_id = ? AND d.id = ?
            GROUP BY d.id
            LIMIT 1";
        if ($stmt = $conn -> prepare($query)) {
            $stmt -> bind_param("ii", $_SESSION['userId'], $id);
            $stmt -> execute();
            $stmt -> store_result();
            $stmt -> bind_result($id, $title, $date, $description, $imgURL, $lng, $lat, $company, $companyId, $dataType, $dataTypeId, $category);
            $array = [];
            while ($stmt -> fetch()) {
                $companies = explode(",", $company);
                $dataTypes = explode(",", $dataType);
                $companiesId = explode(",", $companyId);
                $dataTypesId = explode(",", $dataTypeId);
                $companyArray = [];
                $dataTypesArray = [];
                for ($i = 0; $i < sizeof($companies); $i++) {
                    $companyArray[] = [
                        "name" => $companies[$i],
                        "id" => $companiesId[$i]
                    ];
                }
                for ($i = 0; $i < sizeof($dataTypes); $i++) {
                    $dataTypesArray[] = [
                        "name" => $dataTypes[$i],
                        "id" => $dataTypesId[$i]
                    ];
                }
                $array[] = ["id" => $id,
                    "title" => $title,
                    "date" => $date,
                    "description" => $description,
                    "imgURL" => $imgURL,
                    "location" => [
                        "lat" => $lat,
                        "lng" => $lng
                    ],
                    "companies" => $companyArray,
                    "dataTypes" => $dataTypesArray,
                    "category" => $category
                ];
            }
        }
    }

    public function delete ($id, $conn) {
        $query = "DELETE FROM data WHERE id = ? AND user_id = ?";
        if ($stmt = $conn -> prepare($query)) {
            $stmt -> bind_param("ii", $id, $_SESSION['userId']);
            $stmt -> execute();
        }
    }
}