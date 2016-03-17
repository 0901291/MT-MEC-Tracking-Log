<?php
use PHP_Crypt\PHP_Crypt as PHP_Crypt;

class DataInfo {
    private $conn;
    public $id,
        $name;
    private static $types = ['category', 'datatype', 'company'];

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public static function getDataInfo($conn, $key, $userId, $type, $limit = 0, $offset = 0)
    {
        if (in_array($type, self::$types)) {
            $crypt = new PHP_Crypt($key);
            $query =
                "SELECT
                        d.id,
                        d.name
                    FROM " . DB_PREFIX . $type . " d
                    WHERE d.user_id = ?
                    ORDER BY d.name ASC" .
                ($limit > 0 ? " LIMIT " . $limit . " OFFSET " . $offset : " LIMIT 99999999999999 OFFSET " . $offset);
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->store_result();
                $stmt->bind_result($id, $name);
                $array = [];
                while ($stmt->fetch()) {
                    $array[] = [
                        "id" => $id,
                        "name" => trim($crypt->decrypt(hex2bin($name)))
                    ];
                }
                return $array;
            }
        }
        return 400;
    }

    public function save($key, $userId, $method, $type) {
        if (in_array($type, self::$types)) {
            $crypt = new PHP_Crypt($key);
            $name = bin2hex($crypt->encrypt($this->name));
            $stmt = false;

            switch ($method) {
                case "edit" :
                    $result = $this->detail($key, $userId);
                    if (is_array($result)) {
                        $query = "UPDATE " . DB_PREFIX . $type . " SET name = ? WHERE id = ? AND user_id = ?";
                        if ($stmt = $this->conn->prepare($query)) {
                            $stmt->bind_param("sii", $name, $this->id, $userId);
                            $stmt->execute();
                        }
                    } else {
                        return $result;
                    }
                    break;
                case "insert" :
                    $query = "SELECT d.id FROM ".DB_PREFIX.$type." d WHERE d.name = ? LIMIT 1";
                    if ($stmt = $this->conn->prepare($query)) {
                        $stmt->bind_param("s", $name);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($id);
                        $stmt->fetch();
                        if ($stmt->num_rows == 0) {
                            $query = "INSERT INTO ".DB_PREFIX.$type." (name, user_id) VALUES (?, ?)";
                            if ($stmt = $this->conn->prepare($query)) {
                                $stmt->bind_param("si", $name, $userId);
                                $stmt->execute();
                                $this->id = $stmt->insert_id;
                            }
                        } else {
                            $this->id = $id;
                        }
                    }
                    break;
            }
            if ($stmt) {
                return $this->detail($key, $userId, $type);
            }
        }
        return 400;
    }

    public function detail($key, $userId, $type) {
        $crypt = new PHP_Crypt($key);
        $query = "SELECT
                    d.id,
                    d.name,
                    d.user_id
                FROM ".DB_PREFIX.$type." d
                WHERE d.id = ?
                LIMIT 1";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("i", $this->id);
            $stmt->execute();
            $stmt->store_result();
            $stmt->bind_result($id, $name, $dataInfoUserId);
            $array = "";
            while ($stmt->fetch()) {
                $array = [
                    "id" => $id,
                    "name" => trim($crypt->decrypt(hex2bin($name))),
                ];
            }
            if ($userId == $dataInfoUserId) {
                return $array;
            }
            return 403;
        }
        return 404;
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
