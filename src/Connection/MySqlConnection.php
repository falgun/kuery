<?php
declare(strict_types=1);

namespace Falgun\Kuery\Connection;

use mysqli;
use Falgun\Kuery\Configuration;

final class MySqlConnection implements ConnectionInterface
{

    private ?mysqli $connection;
    private Configuration $configuration;

    public final function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->connection = null;
    }

    public function connect(): void
    {
        $connection = new mysqli(
            $this->configuration->host,
            $this->configuration->user,
            $this->configuration->password,
            $this->configuration->database,
            $this->configuration->port
        );

        if (!empty($connection->connect_errno)) {
            throw new FailedToConnectionException($connection->connect_error);
        }

        $connection->set_charset($this->configuration->characterSet);

        $this->connection = $connection;
    }

    public function disconnect(): bool
    {
        if (isset($this->connection) === false) {
            return true;
        }

        return $this->connection->close();
    }

    public function getConnection(): mysqli
    {
        if (isset($this->connection) === false) {
            $this->connect();
        }

        return $this->connection;
    }
}
