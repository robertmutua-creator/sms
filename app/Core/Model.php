<?php

namespace App\Core;

use PDO;
use PDOException;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected ?int $schoolId;

    public function __construct(?int $schoolId = null)
    {
        $this->db = Database::getConnection();
        $this->schoolId = $schoolId;
    }

    public function create(array $data): bool
    {
        if ($this->schoolId !== null && !isset($data['school_id'])) {
            $data['school_id'] = $this->schoolId;
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = ['id' => $id];

        if ($this->schoolId !== null) {
            $sql .= " AND school_id = :school_id";
            $params['school_id'] = $this->schoolId;
        }
        $sql .= " LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result !== false ? $result : null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function findAll(array $criteria = []): array
    {
        $where = [];
        $params = [];

        if ($this->schoolId !== null) {
            $where[] = "school_id = :school_id_scope";
            $params['school_id_scope'] = $this->schoolId;
        }

        foreach ($criteria as $key => $value) {
            $where[] = "{$key} = :criteria_{$key}";
            $params["criteria_{$key}"] = $value;
        }

        $whereString = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        $sql = "SELECT * FROM {$this->table} {$whereString}";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error [findAll]: " . $e->getMessage());
            return [];
        }
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        foreach (array_keys($data) as $key) {
            $fields[] = "{$key} = :{$key}";
        }
        $fieldString = implode(', ', $fields);

        $sql = "UPDATE {$this->table} SET {$fieldString} WHERE id = :id";
        $params = $data;
        $params['id'] = $id;

        if ($this->schoolId !== null) {
            $sql .= " AND school_id = :school_id";
            $params['school_id'] = $this->schoolId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $params = ['id' => $id];

        if ($this->schoolId !== null) {
            $sql .= " AND school_id = :school_id";
            $params['school_id'] = $this->schoolId;
        }

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function count(array $criteria = []): int
    {
        $where = [];
        $params = [];

        if ($this->schoolId !== null) {
            $where[] = "school_id = :school_id";
            $params['school_id'] = $this->schoolId;
        }

        foreach ($criteria as $key => $value) {
            $where[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $whereString = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        $sql = "SELECT COUNT(*) FROM {$this->table} {$whereString}";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }
}
