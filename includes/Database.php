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
            error_log("Database Connection Error: " . $e->getMessage());
            echo "Error de conexión a la base de datos. Por favor, contacte al administrador.";
        }

        return $this->conn;
    }

    /**
     * Execute a query
     */
    public function query($sql, $params = []) {
        if (!$this->conn) {
            error_log("Database query attempted without connection");
            return false;
        }
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert data into database
     */
    public function insert($table, $data) {
        if (!$this->conn) {
            error_log("Database insert attempted without connection");
            return false;
        }
        
        // Sanitize table name (alphanumeric and underscores only)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            error_log("Invalid table name: " . $table);
            return false;
        }
        
        $keys = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO `$table` ($keys) VALUES ($placeholders)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Select data from database
     */
    public function select($table, $conditions = [], $columns = '*') {
        if (!$this->conn) {
            error_log("Database select attempted without connection");
            return false;
        }
        
        // Sanitize table name (alphanumeric and underscores only)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            error_log("Invalid table name: " . $table);
            return false;
        }
        
        // For columns, only allow * or alphanumeric with commas, spaces, and underscores
        if ($columns !== '*' && !preg_match('/^[a-zA-Z0-9_,\s]+$/', $columns)) {
            error_log("Invalid columns specification: " . $columns);
            return false;
        }
        
        $sql = "SELECT $columns FROM `$table`";
        
        if (!empty($conditions)) {
            $where = [];
            foreach (array_keys($conditions) as $key) {
                $where[] = "`$key` = :$key";
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
            error_log("Select Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update data in database
     */
    public function update($table, $data, $conditions) {
        if (!$this->conn) {
            error_log("Database update attempted without connection");
            return false;
        }
        
        // Sanitize table name (alphanumeric and underscores only)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            error_log("Invalid table name: " . $table);
            return false;
        }
        
        $set = [];
        foreach (array_keys($data) as $key) {
            $set[] = "`$key` = :$key";
        }
        
        $where = [];
        foreach (array_keys($conditions) as $key) {
            $where[] = "`$key` = :where_$key";
        }
        
        $sql = "UPDATE `$table` SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $where);
        
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
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete data from database
     */
    public function delete($table, $conditions) {
        if (!$this->conn) {
            error_log("Database delete attempted without connection");
            return false;
        }
        
        // Sanitize table name (alphanumeric and underscores only)
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            error_log("Invalid table name: " . $table);
            return false;
        }
        
        $where = [];
        foreach (array_keys($conditions) as $key) {
            $where[] = "`$key` = :$key";
        }
        
        $sql = "DELETE FROM `$table` WHERE " . implode(' AND ', $where);
        
        try {
            $stmt = $this->conn->prepare($sql);
            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }
}
?>
