<?php

namespace S25\SQLite
{
  class Result
  {
    /** @var \SQLite3Result  */
    private $result;

    #region Additional methods

    public function yieldRows($mode = SQLITE3_BOTH): \Generator
    {
      while (true)
      {
        $row = $this->row($mode);

        if ($row === false)
        {
          break;
        }

        yield $row;
      }
    }

    public function yieldValue($column = 0): \Generator
    {
      foreach ($this->yieldRows() as $row)
      {
        yield $row[$column] ?? null;
      }
    }

    public function rows($mode = SQLITE3_BOTH): array
    {
      return iterator_to_array($this->yieldRows($mode));
    }

    public function column($column = 0): array
    {
      return iterator_to_array($this->yieldValue($column));
    }

    public function value($column = 0)
    {
      $row = $this->row();
      return $row === false ? false : ($row[$column] ?? null);
    }

    public function row($mode = SQLITE3_BOTH)
    {
      return $this->result->fetchArray($mode);
    }

    #endregion

    #region Overridden methods

    public function __construct(\SQLite3Result $result)
    {
      $this->result = $result;
    }

    public function numColumns()
    {
      return $this->result->numColumns();
    }

    public function columnName($column_number)
    {
      return $this->result->columnName($column_number);
    }

    // TODO: SQLite 3 maybe does not have column types, only affinities
    public function columnType($column_number)
    {
      return $this->result->columnType($column_number);
    }

    // public function fetchArray($mode = SQLITE3_BOTH)

    public function reset()
    {
      return $this->result->reset();
    }

    public function finalize ()
    {
      return $this->result->finalize();
    }

    #endregion
  }
}