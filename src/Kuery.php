<?php
declare(strict_types=1);

namespace Falgun\Kuery;

use mysqli_stmt;
use mysqli_result;
use Falgun\Kuery\Connection\ConnectionInterface;
use Falgun\Kuery\Exception\InvalidStatementException;
use Falgun\Kuery\Exception\InvalidBindParamException;

final class Kuery
{

    protected ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function run(string $sql, array $values = [], string $bind = ''): mysqli_stmt
    {
        $stmt = $this->prepare($sql);
        $this->bind($stmt, $bind, $values);
        $this->execute($stmt);

        return $stmt;
    }

    public function prepare(string $sql): mysqli_stmt
    {
        $stmt = $this->connection->getConnection()->prepare($sql);

        if ($stmt === false) {
            throw new InvalidStatementException(<<<TEXT
                There is something wrong with your sql : {$sql}<br>
                {$this->connection->getConnection()->error}
                TEXT);
        }
        return $stmt;
    }

    public function bind(mysqli_stmt $stmt, string $bind, array $values): bool
    {
        $valueCount = \count($values);
        $bindCount = \strlen($bind);

        if (empty($valueCount)) {
            return true;
        }

        if ($valueCount !== $bindCount) {
            throw new InvalidBindParamException('Bind Param did not match with values');
        }

        return $stmt->bind_param($bind, ...$values);
    }

    public function execute(mysqli_stmt $stmt): bool
    {
        $stmt->execute();

        if (!empty($stmt->error)) {
            throw new InvalidStatementException($stmt->error);
        }
        return true;
    }

    public function getSingleRow(mysqli_stmt $stmt, string $class_name = \stdClass::class): ?object
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            return null;
        }

        return $result->fetch_object($class_name);
    }

    public function getAllRows(mysqli_stmt $stmt, string $class_name = \stdClass::class): array
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            return [];
        }

        $rows = [];

        while ($row = $result->fetch_object($class_name)) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function yieldAllRows(mysqli_stmt $stmt, string $class_name = \stdClass::class): \Generator
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            yield from [];
            return;
        }

        yield $result->fetch_object($class_name);
    }

    public function getResult(mysqli_stmt $stmt): ?mysqli_result
    {
        $result = $stmt->get_result();

        if ($result === false) {
            throw new InvalidStatementException('Result rows are only available for SELECT queries!');
        }

        if ($result->num_rows === 0) {
            return null;
        }

        return $result;
    }
}
