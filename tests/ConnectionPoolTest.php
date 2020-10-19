<?php
declare(strict_types=1);

namespace Falgun\Kuery\Tests;

use PHPUnit\Framework\TestCase;
use Falgun\Kuery\Configuration;
use Falgun\Kuery\Connection\ConnectionPool;
use Falgun\Kuery\Connection\MySqlConnection;

final class ConnectionPoolTest extends TestCase
{

    public function getConfiguration(): Configuration
    {
        $confArray = [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'falgun'
        ];

        return Configuration::fromArray($confArray);
    }

    public function testMySqlContruct()
    {
        $configuration = $this->getConfiguration();

        $pool = ConnectionPool::fromNewMySQL('test', $configuration);

        $this->assertTrue($pool instanceof ConnectionPool);

        $this->assertSame(true, $pool->has('test'));

        $connection = $pool->get('test');
        $this->assertTrue($connection instanceof MySqlConnection);

        $pool->set('another', new MySqlConnection($configuration));

        $this->assertSame(true, $pool->has('another'));

        $pool->newMySQL('yet-another', $configuration);

        $this->assertSame(true, $pool->has('yet-another'));
    }
}
