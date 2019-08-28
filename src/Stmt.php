<?php

namespace S25\SQLite;

class Stmt
{
    /** @var Db */
    private $db;
    /** @var \SQLite3Stmt */
    private $stmt;

    public function __construct(Db $db, \SQLite3Stmt $stmt)
    {
        $this->db = $db;
        $this->stmt = $stmt;
    }

    public function bind(array $values, int $offset = 1): self
    {
        foreach ($values as $name => $value) {
            $this->bindValue(is_integer($name) ? $name + $offset : $name, $value);
        }
        return $this;
    }

    public function bindParam($sql_param, &$param): bool
    {
        return $this->stmt->bindParam($sql_param, $param, $this->guessType($param));
    }

    public function bindValue($sql_param, $value): bool
    {
        return $this->stmt->bindValue($sql_param, $value, $this->guessType($value));
    }

    public function clear(): bool
    {
        return $this->stmt->clear();
    }

    public function close(): bool
    {
        return $this->stmt->close();
    }

    /**
     * @return Result
     *
     * @throws \Exception
     */
    public function execute()
    {
        return new Result($this->stmt->execute());
    }

    public function paramCount(): int
    {
        return $this->stmt->paramCount();
    }

    public function readOnly(): bool
    {
        return $this->stmt->readOnly();
    }

    public function reset(): bool
    {
        return $this->stmt->reset();
    }

    private function guessType($value)
    {
        if (is_null($value)) {
            return SQLITE3_NULL;
        }
        if (is_int($value) || is_bool($value)) {
            return SQLITE3_INTEGER;
        }
        if (is_float($value)) {
            return SQLITE3_FLOAT;
        }
        if (is_string($value)) {
            return SQLITE3_TEXT;
        }
        throw new \InvalidArgumentException('Argument is of invalid type ' . gettype($value));
    }
}