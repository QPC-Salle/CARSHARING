<?php
/**
 * Database connection class
 * Handles database connections and general SQL functions
 */
class Database {
    private $host = "localhost";
    private $db_name = "carsharing";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }

    /**
     * Execute a query
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            echo "Query Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Insert data into database
     */
    public function insert($table, $data) {
        $keys = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO $table ($keys) VALUES ($placeholders)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            echo "Insert Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Select data from database
     */
    public function select($table, $conditions = [], $columns = '*') {
        $sql = "SELECT $columns FROM $table";
        
        if (!empty($conditions)) {
            $where = [];
            foreach (array_keys($conditions) as $key) {
                $where[] = "$key = :$key";
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Select Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Update data in database
     */
    public function update($table, $data, $conditions) {
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "$key = :$key";
        }
        
        $where = [];
        foreach (array_keys($conditions) as $key) {
            $where[] = "$key = :where_$key";
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $where);
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":where_$key", $value);
            }
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Update Error: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Delete data from database
     */
    public function delete($table, $conditions) {
        $where = [];
        foreach (array_keys($conditions) as $key) {
            $where[] = "$key = :$key";
        }
        
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $where);
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch(PDOException $e) {
            echo "Delete Error: " . $e->getMessage();
            return false;
        }
    }
}
?>
