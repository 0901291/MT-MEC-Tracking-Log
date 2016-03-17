<?php
use PHP_Crypt\PHP_Crypt as PHP_Crypt;
require 'DataInfo.php';

class Entry {
    private $conn;
    public $id,
        $title,
        $date,
        $description,
        $imgURL,
        $lng,
        $lat,
        $companies,
        $dataTypes,
        $category,
        $state,
        $token;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function getEntries($status, $conn, $key, $userId, $limit = 0, $offset = 0) {
        $status = htmlentities($status);
        $state = $status != 0 ? "AND WHERE = ".$status : "";
        $crypt = new PHP_Crypt($key);
        if (is_numeric($status)) {
            $query =
                "SELECT
                    d.id,
                    d.title,
                    d.date,
                    d.description,
                    d.imgURL,
                    d.lng,
                    d.lat,
                    group_concat(DISTINCT c.name),
                    group_concat(DISTINCT dt.name),
                    ca.name,
                    ca.id,
                    d.state,
                    d.timestamp
                FROM ".DB_PREFIX."data d
                LEFT OUTER JOIN (".DB_PREFIX."datatype_has_data dhd
                    LEFT OUTER JOIN ".DB_PREFIX."datatype dt
                    ON dt.id = dhd.dataType_id)
                ON dhd.data_id = d.id
                LEFT OUTER JOIN (".DB_PREFIX."company_has_data chd
                    LEFT OUTER JOIN ".DB_PREFIX."company c
                    ON c.id = chd.company_id)
                ON chd.data_id = d.id
                LEFT OUTER JOIN ".DB_PREFIX."category ca
                ON ca.id = d.category_id
                WHERE d.user_id = ? ".$state."
                GROUP BY d.id
                ORDER BY d.date DESC".
                ($limit > 0 ? " LIMIT ".$limit." OFFSET ".$offset : " LIMIT 99999999999999 OFFSET ".$offset);
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id, $title, $date, $description, $imgURL, $lng, $lat, $company, $dataType, $category, $categoryId, $state, $timestamp);
                $array = [];
                while ($stmt->fetch()) {
                    $_companies = strlen($company) > 0 ? explode(",", $company) : [];
                    $companies = [];
                    foreach($_companies as $_company) $companies[] = trim($crypt->decrypt(hex2bin($_company)));
                    $_dataTypes = strlen($dataType) > 0 ? explode(",", $dataType) : [];
                    $dataTypes = [];
                    foreach($_dataTypes as $_dataType) $dataTypes[] = trim($crypt->decrypt(hex2bin($_dataType)));
                    $categoryArray = !empty($category) && !empty($categoryId) ? [
                        "name" => trim($crypt->decrypt(hex2bin($category))),
                        "id" => $categoryId
                    ] : null;

                    $array[] = [
                        "id" => $id,
                        "title" => trim($crypt->decrypt(hex2bin($title))),
                        "date" => date("d-m-Y H:i", strtotime($date)),
                        "description" => trim($crypt->decrypt(hex2bin($description))),
                        "imgURL" => trim($crypt->decrypt(hex2bin($imgURL))),
                        "location" => [
                            "lat" => trim($crypt->decrypt(hex2bin($lat))),
                            "lng" => trim($crypt->decrypt(hex2bin($lng)))
                        ],
                        "companies" => $companies,
                        "dataTypes" => $dataTypes,
                        "category" => $categoryArray,
                        "state" => $state
                    ];
                }
                return $array;
            }
        }
        return 400;
    }

    public function save($key, $userId, $method) {
        $crypt = new PHP_Crypt($key);
        $title = bin2hex($crypt->encrypt($this->title));
        $description = bin2hex($crypt->encrypt($this->description));
        $lat = bin2hex($crypt->encrypt($this->lat));
        $lng = bin2hex($crypt->encrypt($this->lng));
        $stmt = false;

        $_category = new DataInfo($this->conn);
        $_category->name = $this->category;
        $this->category = $_category->save($key, $userId, 'insert', 'category')['id'];

        switch ($method) {
            case "edit" :
                $result = $this->detail($key,$userId);
                if (is_array($result)) {
                    $query = "UPDATE ".DB_PREFIX."data SET title = ?, date = ?, description = ?, lat = ?, lng = ?, category_id = ?, state = ? WHERE id = ? AND user_id = ?";
                    if ($stmt = $this-> conn->prepare($query)) {
                        $stmt->bind_param("sssssiiii", $title, $this->date, $description, $lat, $lng, $this->category, $this->state, $this->id, $userId);
                        $stmt->execute();
                        $query = "DELETE cd FROM " . DB_PREFIX . "company_has_data cd INNER JOIN " . DB_PREFIX . "data d ON d.id = cd.data_id WHERE cd.data_id = ? AND d.user_id = ?";
                        if ($stmt_delete = $this->conn->prepare($query)) {
                            $stmt_delete->bind_param("ii", $this->id, $userId);
                            $stmt_delete->execute();
                        }
                        $query = "DELETE dd FROM " . DB_PREFIX . "datatype_has_data dd INNER JOIN " . DB_PREFIX . "data d ON d.id = dd.data_id WHERE dd.data_id = ? AND d.user_id = ?";
                        if ($stmt_delete = $this->conn->prepare($query)) {
                            $stmt_delete->bind_param("ii", $this->id, $userId);
                            $stmt_delete->execute();
                        }
                    }
                } else {
                    return $result;
                }
                break;
            case "insert" :
                $query = "INSERT INTO ".DB_PREFIX."data (title, date, description, lat, lng, category_id, user_id, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = $this->conn->prepare($query)) {
                    $stmt->bind_param("ssssssss", $title, $this->date, $description, $lat, $lng, $this->category, $userId, $this->state);
                    $stmt->execute();
                    $this->id = $stmt->insert_id;
                }
                break;
        }
        if ($this->dataTypes != null) {
            if (gettype($this->dataTypes) == 'string') $this->dataTypes = unserialize($this->dataTypes);
            foreach ($this->dataTypes as $dataType) {
                $_dataType = new DataInfo($this->conn);
                $_dataType->name = $dataType;
                $dataType = $_dataType->save($key, $userId, 'insert', 'datatype');
                $dataType = htmlentities($dataType['id']);
                $query = "INSERT INTO ".DB_PREFIX."datatype_has_data (dataType_id, data_id) VALUES (?, ?)";
                if ($stm = $this->conn->prepare($query)) {
                    $stm->bind_param('si', $dataType, $this->id);
                    $stm->execute();
                }
            }
        }
        if ($this->companies != null) {
            if (gettype($this->companies) == 'string') $this->companies = unserialize($this->companies);
            foreach ($this->companies as $company) {
                $_company = new DataInfo($this->conn);
                $_company->name = $company;
                $company = $_company->save($key, $userId, 'insert', 'company');
                $company = htmlentities($company['id']);
                $query = "INSERT INTO ".DB_PREFIX."company_has_data (company_id, data_id) VALUES (?, ?)";
                if ($stm = $this->conn->prepare($query)) {
                    $stm->bind_param('si', $company, $this->id);
                    $stm->execute();
                }
            }
        }
        if ($stmt) {
            return $this->detail($key, $userId);
        } else {
            return 400;
        }
    }

    public function detail($key, $userId) {
        $crypt = new PHP_Crypt($key);
        $query = "SELECT d.id, d.title, d.date, d.description, d.imgURL, d.lng, d.lat, group_concat(DISTINCT c.name), group_concat(DISTINCT c.id), group_concat(DISTINCT dt.name), group_concat(DISTINCT dt.id), ca.name, ca.id, d.timestamp, d.user_id from ".DB_PREFIX."data d
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
            WHERE d.id = ?
            GROUP BY d.id
            LIMIT 1";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt -> bind_param("i",  $this->id);
            $stmt -> execute();
            $stmt -> store_result();
            $stmt -> bind_result($id, $title, $date, $description, $imgURL, $lng, $lat, $company, $companyId, $dataType, $dataTypeId, $category, $categoryId, $timestamp, $dataUserId);
            $array = "";
            while ($stmt->fetch()) {
                $companies = strlen($company) > 0 ? explode(",", $company) : null;
                $dataTypes = strlen($dataType) > 0 ? explode(",", $dataType) : null;
                $companiesId = strlen($companyId) > 0 ? explode(",", $companyId) : null;
                $dataTypesId = strlen($dataTypeId) > 0 ? explode(",", $dataTypeId) : null;
                $companyArray = [];
                $dataTypesArray = [];
                for ($i = 0; $i < sizeof($companies); $i++) {
                    $companyArray[] = [
                        "name" => trim($crypt->decrypt(hex2bin($companies[$i]))),
                        "id" => $companiesId[$i]
                    ];
                }
                for ($i = 0; $i < sizeof($dataTypes); $i++) {
                    $dataTypesArray[] = [
                        "name" => trim($crypt->decrypt(hex2bin($dataTypes[$i]))),
                        "id" => $dataTypesId[$i]
                    ];
                }
                $categoryArray = !empty($category) && !empty($categoryId) ? [
                    "name" => trim($crypt->decrypt(hex2bin($category))),
                    "id" => $categoryId
                ] : null;
                $array = ["id" => $id,
                    "title" => trim($crypt->decrypt(hex2bin($title))),
                    "date" => $date,
                    "description" => trim($crypt->decrypt(hex2bin($description))),
                    "imgURL" => trim($crypt->decrypt(hex2bin($imgURL))),
                    "location" => [
                        "lat" => trim($crypt->decrypt(hex2bin($lat))),
                        "lng" => trim($crypt->decrypt(hex2bin($lng)))
                    ],
                    "companies" => $companyArray,
                    "dataTypes" => $dataTypesArray,
                    "category" => $categoryArray,
                ];
            }
            if ($userId == $dataUserId) {
                return $array;
            } else {
                return 403;
            }
        }
        return 404;
    }

    public function delete ($key, $userId) {
        $result = $this->detail($key, $userId);
        if (is_array($result)) {
            $query = "DELETE cd FROM ".DB_PREFIX."company_has_data cd INNER JOIN ".DB_PREFIX."data d ON d.id = cd.data_id WHERE cd.data_id = ? AND d.user_id = ?";
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param("ii", $this->id, $userId);
                $stmt->execute();
            }
            $query = "DELETE dd FROM ".DB_PREFIX."datatype_has_data dd INNER JOIN ".DB_PREFIX."data d ON d.id = dd.data_id WHERE dd.data_id = ? AND d.user_id = ?";
            if ($stmt = $this->conn->prepare($query)) {
                $stmt -> bind_param("ii", $this->id, $userId);
                $stmt -> execute();
            }
            $query = "DELETE FROM ".DB_PREFIX."data WHERE id = ? AND user_id = ?";
            if ($stmt = $this->conn->prepare($query)) {
                $stmt -> bind_param("ii", $this->id, $userId);
                $stmt -> execute();
                if ($stmt) return 204;
            }
        }
        return $result;
    }
}
