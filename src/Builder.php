<?php

declare(strict_types=1);

namespace Lilith\Database;

class Builder
{
    protected string $sql = '';
    protected array $params = [];
    protected string $type = 'execution';

    public function __construct(protected readonly ConnectionInterface $connection) {}

    public function select(string|array $columns): static
    {
        $this->type = 'select';
        $this->sql .= 'SELECT ' . is_string($columns) ? $columns : implode(',', $columns);

        return $this;
    }

    public function update(string $table): static
    {
        $this->sql .= "UPDATE $table";

        return $this;
    }

    public function set(string|array $expr): static
    {
        if (is_string($expr)) {
            $this->sql .= " SET $expr";
        } else {
            $keys = array_keys($expr);
            $sql = '';

            foreach ($keys as $key) {
                $sql .= "$key = :$key,";
            }

            $sql = substr($sql, strlen($sql) - 1, 1);
            $this->bindParams($expr);
            $this->sql .= " SET $sql";
        }

        return $this;
    }

    public function insert(string $table): static
    {
        $this->sql .= "INSERT INTO $table";

        return $this;
    }

    public function values(string|array $values): static
    {
        if (is_string($values)) {
            $this->sql .= " $values";
        } else {
            $columns = array_keys($values);
            $params = array_map(fn ($item) => ':' . $item, $columns);
            $columnsSql = '(' . implode(',', $columns) . ')';
            $paramsSql = '(' . implode(',', $params) . ')';
            $this->bindParams($params);
            $this->sql .=  " $columnsSql VALUES $paramsSql";
        }

        return $this;
    }

    public function delete(): static
    {
        $this->sql .= 'DELETE';

        return $this;
    }

    public function from(string $table): static
    {
        $this->sql .= " FROM $table";

        return $this;
    }

    public function join(string $table, string $onExpr, string $typeJoin = 'JOIN'): static
    {
        $this->sql .= " $typeJoin $table ON $onExpr";

        return $this;
    }

    public function where(string|array $expr): static
    {
        if (is_string($expr)) {
            $this->sql .= " WHERE $expr";
        } else {
            $keys = array_keys($expr);
            $sql = '';

            foreach ($keys as $key) {
                $sql .= "$key = :$key,";
            }

            $sql = substr($sql, strlen($sql) - 1, 1);
            $this->bindParams($expr);
            $this->sql .= " WHERE $sql";
        }

        return $this;
    }

    public function groupBy(string $expr): static
    {
        $this->sql .= " GROUP BY $expr";

        return $this;
    }

    public function having(string $expr): static
    {
        $this->sql .= " HAVING $expr";

        return $this;
    }

    public function orderBy(string $expr): static
    {
        $this->sql .= " ORDER BY $expr";

        return $this;
    }

    public function bindParams(array $params): static
    {
        $this->params += $params;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getSql(): string
    {
        return $this->sql;
    }

    public function prepare(): StatementInterface
    {
        $stmt = $this->connection->prepare($this->sql);

        foreach ($this->params as $key => $value) {
            $stmt->bindParam($key, $value);
        }

        $this->clear();

        return $stmt;
    }

    public function clear(): void
    {
        $this->sql = '';
        $this->params = [];
        $this->type = 'execution';
    }
}
