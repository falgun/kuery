<?php
declare(strict_types=1);

namespace Falgun\Kuery\Connection;

use Falgun\Kuery\Configuration;

final class ConnectionPool
{

    /** @var array<string, ConnectionInterface> */
    private array $connections;

    /**
     * @param array<string, ConnectionInterface> $connections
     */
    public function __construct(array $connections = [])
    {
        $this->connections = $connections;
    }

    public static function fromNewMySQL(string $key, Configuration $configuration): self
    {
        $pool = new static();

        $pool->newMySQL($key, $configuration);

        return $pool;
    }

    public function newMySQL(string $key, Configuration $configuration): ConnectionInterface
    {
        return $this->connections[$key] = new MySqlConnection($configuration);
    }

    public function set(string $key, ConnectionInterface $connection): ConnectionInterface
    {
        return $this->connections[$key] = $connection;
    }

    public function get(string $key): ConnectionInterface
    {
        return $this->connections[$key];
    }

    public function has(string $key): bool
    {
        return isset($this->connections[$key]);
    }
}
