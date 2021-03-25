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

    private ConnectionInterface $connection;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $sql
     * @param array<int, mixed> $values
     * @param string $bind
     * @return mysqli_stmt
     */
    public function run(string $sql, array $values = [], string $bind = ''): mysqli_stmt
    {
        $stmt = $this->prepare($sql);
        $this->bind($stmt, $values, $bind);
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

    /**
     * @param mysqli_stmt $stmt
     * @param array<int, mixed> $values
     * @param string $bind
     * @return bool
     * @throws InvalidBindParamException
     * @see https://stackoverflow.com/questions/51138463/mysqli-stmtbind-param-specify-another-data-type-than-s-for-each-paramete
     */
    public function bind(mysqli_stmt $stmt, array $values = [], string $bind = ''): bool
    {
        if (empty($values) && empty($bind)) {
            return true;
        }

        $valueCount = \count($values);
        $bindTypes = $bind ?: \str_repeat('s', $valueCount);
        $bindCount = \strlen($bindTypes);

        if ($valueCount !== $bindCount) {
            throw new InvalidBindParamException('Bind Param did not match with values');
        }

        return $stmt->bind_param($bindTypes, ...$values);
    }

    public function execute(mysqli_stmt $stmt): bool
    {
        $stmt->execute();

        if (!empty($stmt->error)) {
            throw new InvalidStatementException($stmt->error);
        }
        return true;
    }

    public function fetchOne(mysqli_stmt $stmt, string $className = \stdClass::class): ?object
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            return null;
        }

        return $result->fetch_object($className);
    }

    public function fetchOneAsArray(mysqli_stmt $stmt): ?array
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            return null;
        }

        return $result->fetch_assoc();
    }

    public function fetchAll(mysqli_stmt $stmt, string $className = \stdClass::class): array
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            return [];
        }

        $rows = [];

        while ($row = $result->fetch_object($className)) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function fetchAllAsArray(mysqli_stmt $stmt): array
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            return [];
        }

        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function yieldAll(mysqli_stmt $stmt, string $className = \stdClass::class): \Generator
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            yield from [];
            return;
        }

        yield $result->fetch_object($className);
    }

    public function yieldAllAsArray(mysqli_stmt $stmt): \Generator
    {
        $result = $this->getResult($stmt);

        if ($result === null) {
            yield from [];
            return;
        }

        yield $result->fetch_assoc();
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
