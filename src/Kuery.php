<?php
declare(strict_types=1);

namespace Falgun\Kuery;

use mysqli;
use mysqli_stmt;
use mysqli_result;
use Falgun\Kuery\Connection\ConnectionInterface;
use Falgun\Kuery\Exception\InvalidStatementException;
use Falgun\Kuery\Exception\InvalidBindParamException;

class Kuery
{

    protected ConnectionInterface $connection;
    protected mysqli_stmt $stmt;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    public function run(string $sql, array $values = [], string $bind = ''): mysqli_stmt
    {
        $this->stmt = $this->prepare($sql);
        $this->bind($bind, $values);
        $this->execute();

        return $this->stmt;
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

    public function bind(string $bind, array $values): bool
    {
        $valueCount = \count($values);
        $bindCount = \strlen($bind);

        if (empty($valueCount)) {
            return true;
        }

        if ($valueCount !== $bindCount) {
            throw new InvalidBindParamException('Bind Param did not match with values');
        }

        return $this->stmt->bind_param($bind, ...$values);
    }

    public function execute(): bool
    {
        $this->stmt->execute();

        if (!empty($this->stmt->error)) {
            throw new InvalidStatementException($this->stmt->error);
        }
        return true;
    }

    public function getSingleRow(string $class_name = 'stdClass'): ?object
    {
        $result = $this->getResult();

        if ($result === null) {
            return null;
        }

        return $result->fetch_object($class_name);
    }

    public function getAllRows(string $class_name = 'stdClass'): ?array
    {
        $result = $this->getResult();

        if ($result === null) {
            return null;
        }

        $rows = [];

        while ($row = $result->fetch_object($class_name)) {
            if ($row === null) {
                break;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function yieldAllRows(string $class_name = 'stdClass'): \Generator
    {
        $result = $this->getResult();

        if ($result !== null) {
            yield $result->fetch_object($class_name);
        } else {
            yield [];
        }
    }

    public function getResult(): ?mysqli_result
    {
        $result = $this->stmt->get_result();

        if ($result === false) {
            throw new InvalidStatementException('No result rows are available for this query');
        }

        if ($result->num_rows === 0) {
            return null;
        }

        return $result;
    }

    public function closeStatement(): bool
    {
        unset($this->stmt);
        return true;
        // return $this->stmt->close();
    }
}
