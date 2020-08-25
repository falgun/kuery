<?php
declare(strict_types=1);

namespace Falgun\Kuery;

class Configuration
{

    public string $host;
    public string $user;
    public string $password;
    public string $database;
    public int $port;
    public string $characterSet;

    private final function __construct(string $host, string $user, string $password, string $database, int $port, string $characterSet)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->port = $port;
        $this->characterSet = $characterSet;
    }

    public static function new(string $host, string $user, string $password, string $database, int $port = 3306, string $characterSet = 'utf8'): Configuration
    {
        return new static($host, $user, $password, $database, $port, $characterSet);
    }

    public static function fromArray(array $configuration): Configuration
    {
        if (isset($configuration['host']) === false) {
            throw new \Exception('Configuration Array must contain "host" !');
        }
        if (isset($configuration['user']) === false) {
            throw new \Exception('Configuration Array must contain "user" !');
        }
        if (isset($configuration['password']) === false) {
            throw new \Exception('Configuration Array must contain "password" !');
        }
        if (isset($configuration['database']) === false) {
            throw new \Exception('Configuration Array must contain "database" !');
        }

        $host = $configuration['host'];
        $user = $configuration['user'];
        $password = $configuration['password'];
        $database = $configuration['database'];
        $port = $configuration['port'] ?? 3306;
        $characterSet = $configuration['character-set'] ?? 'utf8';

        return new static($host, $user, $password, $database, $port, $characterSet);
    }
}
