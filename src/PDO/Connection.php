<?php

declare(strict_types=1);

namespace Lilith\Database\PDO;

use Lilith\Database\ConnectionInterface;
use Lilith\Database\LevelTransactionEnum;
use PDO;

class Connection implements ConnectionInterface
{
    protected readonly PDO $pdo;

    public function __construct(string $dsn)
    {
        $this->pdo = new PDO($dsn);
    }

    public function setLevelTransaction(LevelTransactionEnum $levelTransaction): void
    {
        $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL ' . $levelTransaction->value . ';');
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    public function prepare(string $sql): Statement
    {
        return new Statement($this->pdo->prepare($sql));
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function execute(string $sql): void
    {
        $this->pdo->exec($sql);
    }

    public function getNativeConnection(): PDO
    {
        return $this->pdo;
    }
}
