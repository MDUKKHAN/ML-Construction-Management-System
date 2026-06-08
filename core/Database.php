<?php
/**
 * Database Class
 */

class Database {
    private $connection;
    private $statement;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ':' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Database Connection Error: ' . $e->getMessage());
        }
    }

    public function prepare($query) {
        $this->statement = $this->connection->prepare($query);
        return $this;
    }

    public function bind($param, $value, $type = PDO::PARAM_STR) {
        $this->statement->bindValue($param, $value, $type);
        return $this;
    }

    public function execute() {
        return $this->statement->execute();
    }

    public function single() {
        $this->execute();
        return $this->statement->fetch();
    }

    public function all() {
        $this->execute();
        return $this->statement->fetchAll();
    }

    public function rowCount() {
        return $this->statement->rowCount();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollBack();
    }

    public function getConnection() {
        return $this->connection;
    }

    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([__CLASS__, 'sanitize'], $data);
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

?>
