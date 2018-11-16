<?php
declare(strict_types=1);
namespace Ciebit\News\Storages\Database;

use PDOStatement;
use function implode;

abstract class SqlFilters
{
    private $bindList; #Array
    private $filtersSql; #Array
    private $limit; #int
    private $joinSql; #: array
    private $offset; #int
    private $orderBy; #Array
    private $valueKey = 0;

    protected function addBind(string $key, int $type, $value): self
    {
        $this->bindList[] = [
            'key' => $key,
            'value' => $value,
            'type' => $type
        ];
        return $this;
    }

    protected function addFilter(string $key, string $sql, int $type, $value): self
    {
        $this->addBind($key, $type, $value);
        $this->addSqlFilter($sql);
        return $this;
    }

    protected function addSqlFilter(string $sql): self
    {
        $this->filtersSql[] = $sql;
        return $this;
    }

    protected function addSqlJoin(string $sql): self
    {
        $this->joinSql[] = $sql;
        return $this;
    }

    protected function bind(PDOStatement $statment): self
    {
        if (! is_array($this->bindList)) {
            return $this;
        }
        foreach ($this->bindList as $bind) {
            $statment->bindValue(":{$bind['key']}", $bind['value'], $bind['type']);
        }
        return $this;
    }

    protected function generateSqlFilters(): string
    {
        if (empty($this->filtersSql)) {
            return '1';
        }
        return implode(' AND ', $this->filtersSql);
    }

    protected function generateSqlLimit(): string
    {
        $init = (int) $this->offset;
        $sql =
            $this->limit === null
            ? ''
            : "LIMIT {$init},{$this->limit}";
        return $sql;
    }

    protected function generateSqlJoin(): string
    {
        if (! is_array($this->joinSql)) {
            return '';
        }

        return implode(' ', $this->joinSql);
    }

    protected function generateOrder(): string
    {
        if (empty($this->orderBy)) {
            return '';
        }
        $array = array_map(function($item) {
            return implode(" ", $item);
        }, $this->orderBy);

        $sql = "ORDER BY " . implode(', ', $array);
        return $sql;
    }

    protected function generateValueKey(): string
    {
        return 'value_'. $this->valueKey++;
    }

    protected function setLimit(int $total): self
    {
        $this->limit = $total;
        return $this;
    }

    protected function setOffset(int $lineInit): self
    {
        $this->offset = $lineInit;
        return $this;
    }

    public function orderBy(string $column, string $order = "ASC"): self
    {
        $this->orderBy[] = [$column, $order];
        return $this;
    }
}
