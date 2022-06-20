<?php

declare(strict_types=1);

namespace Lilith\Database;

interface ConnectionInterface
{
    public function setLevelTransaction(LevelTransactionEnum $levelTransaction): void;
    public function beginTransaction(): void;
    public function rollback(): void;
    public function commit(): void;
    public function inTransaction(): bool;
    public function prepare(string $sql): StatementInterface;
    public function lastInsertId(): string;
    public function execute(string $sql): void;
}
