<?php

namespace S25\SQLite;

class Db extends \SQLite3
{
    /** @var Table */
    private $tables;

    #region Additional methods

    /**
     * Execute callable(self $db) with foreign keys check turned off
     * @param callable $callable
     *
     * @throws \Exception
     */
    public function woFkCheck(callable $callable)
    {
        $oldValue = $this->querySingle('PRAGMA foreign_keys');
        $this->exec('PRAGMA foreign_keys = OFF');
        call_user_func($callable, $this);
        $this->exec('PRAGMA foreign_keys = ' . $oldValue);
    }

    public function table($name): Table
    {
        $table = $this->tables[$name] ?? new Table($this, $name);
        $this->tables[$name] = $table;
        return $table;
    }

    #endregion

    #region Overridden constructor and methods

    public function __construct(string $filename, ?int $flags = null, ?string $encryption_key = null)
    {
        parent::__construct($filename, $flags, $encryption_key);
        $this->enableExceptions(true);
        $this->exec('PRAGMA foreign_keys = ON');
    }

    /**
     * @param string $query
     * @return Stmt
     *
     * @throws \Exception
     */
    public function prepare($query): Stmt
    {
        return new Stmt($this, parent::prepare($query));
    }

    /**
     * @param string $query
     * @param array $values
     * @return Result
     *
     * @throws \Exception
     */
    public function query($query, array $values = []): Result
    {
        if ($values === []) {
            return new Result(parent::query($query));
        } else {
            return $this->prepare($query)->bind($values)->execute();
        }
    }

    # endregion
}