<?php
declare(strict_types=1);

namespace Falgun\Kuery\Connection;

use Falgun\Kuery\Configuration;

class ConnectionPool
{

    protected array $connectionPool;

    public final function __construct(array $connections = [])
    {
        $this->connectionPool = $connections;
    }

    public static function fromNewMySQL(string $key, Configuration $configuration): self
    {
        $pool = new static();

        $pool->newMySQL($key, $configuration);

        return $pool;
    }

    public function newMySQL(string $key, Configuration $configuration): ConnectionInterface
    {
        return $this->connectionPool[$key] = new MySqlConnection($configuration);
    }

    public function set(string $key, ConnectionInterface $connection): ConnectionInterface
    {
        return $this->connectionPool[$key] = $connection;
    }

    public function get(string $key): ConnectionInterface
    {
        return $this->connectionPool[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->connectionPool[$key]);
    }
}
