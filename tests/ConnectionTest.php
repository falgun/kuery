<?php
declare(strict_types=1);

namespace Falgun\Kuery\Tests;

use Falgun\Kuery\Configuration;
use PHPUnit\Framework\TestCase;
use Falgun\Kuery\Connection\ConnectionPool;
use Falgun\Kuery\Connection\MySqlConnection;
use Falgun\Kuery\Connection\ConnectionInterface;
use Falgun\Kuery\Connection\FailedToConnectionException;

class ConnectionTest extends TestCase
{

    public function getValidConnection(): MySqlConnection
    {
        $confArray = require __DIR__ . '/config.php';

        $configuration = Configuration::fromArray($confArray);

        $connection = new MySqlConnection($configuration);

        $connection->connect();

        return $connection;
    }

    public function testValidConnection()
    {
        $confArray = require __DIR__ . '/config.php';

        $configuration = Configuration::fromArray($confArray);

        $connection = new MySqlConnection($configuration);

        $mysqli = $connection->getConnection();

        $this->assertTrue($mysqli instanceof \mysqli);
    }

    public function testCharsetSetup()
    {
        $connection = $this->getValidConnection();

        $this->assertSame('utf8mb4', $connection->getConnection()->character_set_name());

        $confArray = require __DIR__ . '/config.php';

        $confArray['character-set'] = 'utf8';

        $configuration = Configuration::fromArray($confArray);

        $connection2 = new MySqlConnection($configuration);
        $this->assertSame('utf8', $connection2->getConnection()->character_set_name());
    }

    public function testInvalidConnection()
    {
        $confArray = [
            'host' => 'localhost',
            'user' => 'invaliduser',
            'password' => 'invalid',
            'database' => 'falgun'
        ];

        $configuration = Configuration::fromArray($confArray);

        $connection = new MySqlConnection($configuration);

        try {
            $connection->connect();
            $this->fail();
        } catch (\Throwable $ex) {
            $this->assertSame(
                "mysqli::__construct(): (HY000/1045): Access denied for user 'invaliduser'@'localhost' (using password: YES)",
                $ex->getMessage()
            );
        }
    }

    public function testValidDisconnect()
    {
        $connection = $this->getValidConnection();

        $result = $connection->disconnect();

        $this->assertTrue($result);
    }

    public function testInvalidDisconnect()
    {
        $confArray = [
            'host' => 'localhost',
            'user' => 'invaliduser',
            'password' => 'invalid',
            'database' => 'falgun'
        ];

        $configuration = Configuration::fromArray($confArray);

        $connection = new MySqlConnection($configuration);

        $result = $connection->disconnect();

        $this->assertTrue($result);
    }
}
