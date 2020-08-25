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

    public function testConnection()
    {
        $confArray = [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'falgun'
        ];

        $configuration = Configuration::fromArray($confArray);

        $this->assertObjectHasAttribute('host', $configuration);
        $this->assertTrue($configuration instanceof Configuration);

        $pool = ConnectionPool::fromNewMySQL('test', $configuration);

        $this->assertTrue($pool instanceof ConnectionPool);

        $connection = $pool->get('test');
        $this->assertTrue($connection instanceof MySqlConnection);

        $connection->connect();
        $mysqli = $connection->getConnection();

        $this->assertTrue($mysqli instanceof \mysqli);
    }
}
