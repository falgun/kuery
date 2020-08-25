<?php
declare(strict_types=1);

namespace Falgun\Kuery\Tests;

use Falgun\Kuery\Kuery;
use Falgun\Kuery\Configuration;
use Falgun\Kuery\Connection;
use Falgun\Kuery\Connection\MySqlConnection;
use Falgun\Kuery\Connection\ConnectionInterface;
use PHPUnit\Framework\TestCase;

class KueryTest extends TestCase
{

    public function testBaseKuery()
    {
        $confArray = [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'falgun'
        ];

        $configuration = Configuration::fromArray($confArray);
        $connection = new MySqlConnection($configuration);
        $connection->connect();

        $kuery = new Kuery($connection);

        $kuery->run('INSERT INTO users (username, email) values ("Ataur", "email@site.com")');

        $stmt = $kuery->run('SELECT * FROM users WHERE id = ? LIMIT 1', [1], 'i');
        $this->assertTrue($stmt instanceof \mysqli_stmt);

        $this->assertTrue($kuery->getSingleRow() instanceof \stdClass);
        $kuery->closeStatement();

        $stmt = $kuery->run('SELECT * FROM users WHERE id > ?', [0], 'i');
        $users = $kuery->yieldAllRows();

        $this->assertTrue($users instanceof \Generator);

        $rows = 0;
        foreach ($users as $user) {
            $rows++;
            $this->assertTrue($user instanceof \stdClass);
        }

        $this->assertTrue($rows > 0, 'Did not return any rows, expected all rows');

        $stmt = $kuery->run('SELECT * FROM users WHERE id > ?', [0], 'i');
        $users = $kuery->getAllRows();

        $this->assertTrue(is_array($users));


        $rows = 0;
        foreach ($users as $user) {
            $rows++;
            $this->assertTrue($user instanceof \stdClass);
        }

        $this->assertTrue($rows > 0, 'Did not return any rows, expected all rows');

        
        $stmt = $kuery->run('SELECT * FROM users WHERE id = ?', [0], 'i');
        $users = $kuery->getAllRows();
        
        $this->assertTrue($users === null);

    }
}
