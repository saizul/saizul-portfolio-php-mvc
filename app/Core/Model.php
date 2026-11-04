<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Base Model — thin PDO wrapper.
 * All domain models extend this class and set $table.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------
    // Core CRUD
    // ------------------------------------------------------------------

    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findAll(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query(
            "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}"
        );
        return $stmt->fetchAll();
    }

    public function where(string $column, mixed $value, string $operator = '='): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$column}` {$operator} ?"
        );
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    public function first(string $column, mixed $value): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1"
        );
        $stmt->execute([$value]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $columns = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->db->prepare(
            "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$placeholders})"
        );
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $setParts = array_map(fn($col) => "`{$col}` = ?", array_keys($data));
        $set = implode(', ', $setParts);

        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET {$set} WHERE `{$this->primaryKey}` = ?"
        );

        return $stmt->execute([...array_values($data), $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?"
        );
        return $stmt->execute([$id]);
    }

    public function count(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM `{$this->table}`"
        )->fetchColumn();
    }

    // ------------------------------------------------------------------
    // Pagination
    // ------------------------------------------------------------------

    public function paginate(int $page = 1, int $perPage = 10, string $where = '', array $bindings = [], string $orderBy = 'id', string $direction = 'DESC'): array
    {
        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
        $whereClause = $where ? "WHERE {$where}" : '';

        // Total count
        $countStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM `{$this->table}` {$whereClause}"
        );
        $countStmt->execute($bindings);
        $total = (int) $countStmt->fetchColumn();

        // Rows
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` {$whereClause}
             ORDER BY `{$orderBy}` {$direction}
             LIMIT {$perPage} OFFSET {$offset}"
        );
        $stmt->execute($bindings);
        $rows = $stmt->fetchAll();

        return [
            'data'         => $rows,
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int) ceil($total / $perPage),
        ];
    }

    // ------------------------------------------------------------------
    // Raw query helper
    // ------------------------------------------------------------------

    public function query(string $sql, array $bindings = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }
}
