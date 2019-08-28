<?php

namespace S25\SQLite;

class Table
{
    /** @var Db */
    private $db;
    /** @var string */
    private $name;

    public function __construct(Db $db, string $name)
    {
        $this->db = $db;
        $this->name = $name;
    }

    /**
     * @param string|array $columns
     * @param array $where
     * @return Result
     *
     * @throws \Exception
     */
    public function select($columns = '*', array $where = []): Result
    {
        if ($columns !== '*' && is_array($columns) === false) {
            throw new \InvalidArgumentException("Columns must be an array of names or '*'");
        }

        $table = $this->escId($this->name);
        $columnList = $columns === '*' ? '*' : $this->columnList($columns);
        $whereExpr = $this->whereExpr($where);
        $whereExpr = $whereExpr ? "WHERE {$whereExpr}" : "";
        $sql = "SELECT {$columnList} FROM {$table}{$whereExpr}";
        return $this->db->query($sql, $this->whereValues($where));
    }

    /**
     * @param array $values
     * @return int
     *
     * @throws \Exception
     */
    public function insert(array $values): int
    {
        $table = $this->escId($this->name);
        $columnList = $this->columnList(array_keys($values));
        $phList = $this->placeholderList($values);
        $sql = "INSERT INTO {$table} ({$columnList}) VALUES ({$phList})";
        $this->db->query($sql, array_values($values));
        return $this->db->lastInsertRowID();
    }

    /**
     * @param array $set
     * @param array $where
     * @return int
     *
     * @throws \Exception
     */
    public function update(array $set, array $where)
    {
        $table = $this->escId($this->name);
        $setList = $this->setList($set);
        $whereExpr = $this->whereExpr($where);
        $sql = "UPDATE {$table} SET {$setList} WHERE {$whereExpr}";

        $values = array_merge(
            array_values($set),
            $this->whereValues($where)
        );
        $this->db->query($sql, $values);
        return $this->db->changes();
    }

    private function columnList(array $columns): string
    {
        return join(', ', array_map([$this, 'escId'], $columns));
    }

    private function placeholderList(array $values): string
    {
        return join(', ', array_fill(0, count($values), '?'));
    }

    private function setList(array $values): string
    {
        $columns = array_map([$this, 'escId'], array_keys($values));
        return join(', ', array_map(function ($column) {
            return "$column = ?";
        }, $columns));
    }

    private function whereExpr(array $values): string
    {
        $columns = array_map([$this, 'escId'], array_keys($values));
        return join(' AND ', array_map(
            function ($value, $column) {
                return $value === null
                    ? "$column IS NULL"
                    : "$column = ?";
            },
            $values,
            $columns
        ));
    }

    private function whereValues(array $values): array
    {
        return array_values(array_filter($values, function ($value) {
            return $value !== null;
        }));
    }

    private function escId(string $id): string
    {
        return '"' . preg_replace('/"/u', '""', $id) . '"';
    }
}