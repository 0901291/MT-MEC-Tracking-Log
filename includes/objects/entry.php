<?php

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
        $state;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function getEntries($status, $acceptType, $conn) {
        $status = htmlentities($status);
        $state = $status != 0 ? "AND WHERE = ".$status : "";

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
                    d.state
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
                ORDER BY d.date DESC";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("i", $_SESSION['userId']);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id, $title, $date, $description, $imgURL, $lng, $lat, $company, $dataType, $category, $categoryId, $state);
                $array = [];
                while ($stmt->fetch()) {
                    $companies = strlen($company) > 0 ? explode(",", $company) : null;
                    $dataTypes = strlen($dataType) > 0 ? explode(",", $dataType) : null;
                    $categoryArray = !empty($category) && !empty($categoryId) ? [
                        "name" => $category,
                        "id" => $categoryId
                    ] : null;

                    $array[] = [
                        "id" => $id,
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
                        "category" => $categoryArray,
                        "state" => $state
                    ];
                }
                switch($acceptType) {
                    case "json":
                        return json_encode($array);
                        break;
                    default:
                        return $array;
                }
            }
        }
        return false;
    }

    public function insert () {
        $query = "INSERT INTO ".DB_PREFIX."data (title, date, description, lat, lng, category_id, user_id, state) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("ssssssss", $this->title, $this->date, $this->description, $this->lat, $this->lng, $this->category, $_SESSION['userId'], $this->state);
            $stmt->execute();
            if ($this->dataTypes != null) {
                foreach ($this->dataTypes as $dataType) {
                    $dataType = htmlentities($dataType);
                    $query = "INSERT INTO ".DB_PREFIX."datatype_has_data (dataType_id, data_id) VALUES (?, ".$stmt->insert_id.")";
                    if ($stm = $this->conn->prepare($query)) {
                        $stm->bind_param('s', $dataType);
                        $stm->execute();
                    }
                }
            }
            if ($this->companies != null) {
                foreach ($this->companies as $company) {
                    $company = htmlentities($company);
                    $query = "INSERT INTO ".DB_PREFIX."company_has_data (company_id, data_id) VALUES (?, ".$stmt->insert_id.")";
                    if ($stm = $this-> conn -> prepare($query)) {
                        $stm->bind_param('s', $company);
                        $stm->execute();
                    }
                }
            }
            return true;
        }
    }

    public function edit () {
        $query = "UPDATE ".DB_PREFIX."data SET title = ?, date = ?, description = ?, lat = ?, lng = ?, category_id = ?, user_id = ?, state = ? WHERE id = ? AND user_id = ?";
        if ($stmt = $this-> conn->prepare($query)) {
            $stmt -> bind_param("sssssiiiii", $this->title, $this->date, $this->description, $this->lat, $this->lng, $this->category, $_SESSION['userId'], $this ->state, $this->id, $_SESSION["userId"]);
            $stmt -> execute();
            $query = "DELETE cd FROM ".DB_PREFIX."company_has_data cd INNER JOIN ".DB_PREFIX."data d ON d.id = cd.data_id WHERE cd.data_id = ? AND d.user_id = ?";
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param("ii", $this->id, $_SESSION['userId']);
                $stmt->execute();
            }
            $query = "DELETE dd FROM ".DB_PREFIX."datatype_has_data dd INNER JOIN ".DB_PREFIX."data d ON d.id = dd.data_id WHERE dd.data_id = ? AND d.user_id = ?";
            if ($stmt = $this->conn->prepare($query)) {
                $stmt->bind_param("ii", $this->id, $_SESSION['userId']);
                $stmt->execute();
            }
            if ($stmt) {
                if ($this->dataTypes != null) {
                    foreach ($this->dataTypes as $dataType) {
                        $dataType = htmlentities($dataType);
                        $query = "INSERT INTO " . DB_PREFIX . "datatype_has_data (dataType_id, data_id) VALUES (?, ?)";
                        if ($stm = $this->conn->prepare($query)) {
                            $stm->bind_param('ss', $dataType, $this->id);
                            $stm->execute();
                        }
                    }
                }
                if ($this->companies != null) {
                    foreach ($this->companies as $company) {
                        $company = htmlentities($company);
                        $query = "INSERT INTO " . DB_PREFIX . "company_has_data (company_id, data_id) VALUES (?, ?)";
                        if ($stm = $this->conn->prepare($query)) {
                            $stm->bind_param('ss', $company, $this->id);
                            $stm->execute();
                        }
                    }
                }
                return true;
            }
        }
        return true;
    }

    public function detail () {
        $query = "SELECT d.id, d.title, d.date, d.description, d.imgURL, d.lng, d.lat, group_concat(DISTINCT c.name), group_concat(DISTINCT c.id), group_concat(DISTINCT dt.name), group_concat(DISTINCT dt.id), ca.name, ca.id from ".DB_PREFIX."data d
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
        if ($stmt = $this->conn->prepare($query)) {
            $stmt -> bind_param("ii", $_SESSION['userId'], $this->id);
            $stmt -> execute();
            $stmt -> store_result();
            $stmt -> bind_result($id, $title, $date, $description, $imgURL, $lng, $lat, $company, $companyId, $dataType, $dataTypeId, $category, $categoryId);
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
                $array = ["id" => $id,
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
                    "category" => [
                        "name" => $category,
                        "id" => $categoryId
                    ]
                ];
            }
            return $array;
        }
        return true;
    }

    public function delete () {
        $query = "DELETE cd FROM ".DB_PREFIX."company_has_data cd INNER JOIN ".DB_PREFIX."data d ON d.id = cd.data_id WHERE cd.data_id = ? AND d.user_id = ?";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("ii", $this->id, $_SESSION['userId']);
            $stmt->execute();
        }
        $query = "DELETE dd FROM ".DB_PREFIX."datatype_has_data dd INNER JOIN ".DB_PREFIX."data d ON d.id = dd.data_id WHERE dd.data_id = ? AND d.user_id = ?";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt -> bind_param("ii", $this->id, $_SESSION['userId']);
            $stmt -> execute();
        }
        $query = "DELETE FROM ".DB_PREFIX."data WHERE id = ? AND user_id = ?";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt -> bind_param("ii", $this->id, $_SESSION['userId']);
            $stmt -> execute();
            if ($stmt) return true;
        }
    }
}
