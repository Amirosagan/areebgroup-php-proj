<?php

require_once 'interfaces/RepositoryInterface.php';

abstract class BaseRepository implements RepositoryInterface
{
    protected $database;
    protected $tableName;
    protected $primaryKey = 'id';

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Find a record by ID
     */
    public function findById(int $id): ?array
    {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error in findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Find all records
     */
    public function findAll(): array
    {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("SELECT * FROM {$this->tableName} ORDER BY {$this->primaryKey} DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in findAll: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new record
     */
    public function create(array $data)
    {
        try {
            $fields = array_keys($data);
            $placeholders = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO {$this->tableName} (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
            
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);
            
            if ($stmt->execute(array_values($data))) {
                return $conn->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("Error in create: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a record by ID
     */
    public function update(int $id, array $data): bool
    {
        try {
            $fields = array_keys($data);
            $setClause = implode(' = ?, ', $fields) . ' = ?';
            
            $sql = "UPDATE {$this->tableName} SET {$setClause} WHERE {$this->primaryKey} = ?";
            
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);
            
            $values = array_values($data);
            $values[] = $id;
            
            return $stmt->execute($values);
        } catch (Exception $e) {
            error_log("Error in update: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a record by ID
     */
    public function delete(int $id): bool
    {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("DELETE FROM {$this->tableName} WHERE {$this->primaryKey} = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error in delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count total records
     */
    public function count(): int
    {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("SELECT COUNT(*) FROM {$this->tableName}");
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error in count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Execute a custom query
     */
    protected function executeQuery(string $sql, array $params = []): array
    {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in executeQuery: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Execute a custom query and return single result
     */
    protected function executeQuerySingle(string $sql, array $params = []): ?array
    {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (Exception $e) {
            error_log("Error in executeQuerySingle: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Execute a custom query and return count
     */
    protected function executeQueryCount(string $sql, array $params = []): int
    {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error in executeQueryCount: " . $e->getMessage());
            return 0;
        }
    }
}
?> 