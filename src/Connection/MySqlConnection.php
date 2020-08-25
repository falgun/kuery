<?php
declare(strict_types=1);

namespace Falgun\Kuery\Connection;

use mysqli;
use Falgun\Kuery\Configuration;

class MySqlConnection implements ConnectionInterface
{

    protected mysqli $connection;
    protected Configuration $configuration;

    public final function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
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
        return $this->connection->close();
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}
