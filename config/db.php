<?php
/**
 * Database Configuration
 * Using mysqli for database connections
 */

class Database
{
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;

    public function __construct()
    {
        // Load from environment variables or set defaults
        $this->host     = $_ENV['DB_HOST'] ?? 'localhost';
        $this->username = $_ENV['DB_USERNAME'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
        $this->database = $_ENV['DB_DATABASE'] ?? 'umuganda_digital';
    }

    /**
     * Get database connection
     */
    public function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->database
            );

            // Check connection
            if ($this->connection->connect_error) {
                die("Connection failed: " . $this->connection->connect_error);
            }

            // Set charset to utf8mb4
            $this->connection->set_charset("utf8mb4");
        }

        return $this->connection;
    }

    /**
     * Close database connection
     */
    public function closeConnection()
    {
        if ($this->connection !== null) {
            $this->connection->close();
            $this->connection = null;
        }
    }

    /**
     * Execute a prepared statement
     */
    public function execute($query, $params = [], $types = '')
    {
        $connection = $this->getConnection();
        $stmt       = $connection->prepare($query);

        if (! $stmt) {
            throw new Exception("Prepare failed: " . $connection->error);
        }

        if (! empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $result = $stmt->execute();

        if (! $result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        return $stmt;
    }

    /**
     * Fetch all results from a query
     */
    public function fetchAll($query, $params = [], $types = '')
    {
        $stmt   = $this->execute($query, $params, $types);
        $result = $stmt->get_result();
        $data   = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }

    /**
     * Fetch single result from a query
     */
    public function fetchOne($query, $params = [], $types = '')
    {
        $stmt   = $this->execute($query, $params, $types);
        $result = $stmt->get_result();
        $data   = $result->fetch_assoc();
        $stmt->close();

        return $data;
    }

    /**
     * Get last inserted ID
     */
    public function getLastInsertId()
    {
        return $this->getConnection()->insert_id;
    }

    /**
     * Get affected rows count
     */
    public function getAffectedRows()
    {
        return $this->getConnection()->affected_rows;
    }

    /**
     * Get PDO connection for classes that require PDO
     */
    public function getPDOConnection()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            return $pdo;
        } catch (PDOException $e) {
            die("PDO Connection failed: " . $e->getMessage());
        }
    }
}

// Global database instance
$db = new Database();

// Global PDO instance for classes that require PDO
$pdo = $db->getPDOConnection();
