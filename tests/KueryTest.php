<?php
declare(strict_types=1);

namespace Falgun\Kuery\Tests;

use Falgun\Kuery\Kuery;
use Falgun\Kuery\Connection;
use Falgun\Kuery\Configuration;
use PHPUnit\Framework\TestCase;
use Falgun\Kuery\Connection\MySqlConnection;
use Falgun\Kuery\Connection\ConnectionInterface;
use Falgun\Kuery\Exception\InvalidStatementException;
use Falgun\Kuery\Exception\InvalidBindParamException;

class KueryTest extends TestCase
{

    public static function setUpBeforeClass(): void
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

        $kuery->run('TRUNCATE users');
    }

    public function getKuery(): Kuery
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

        return new Kuery($connection);
    }

    public function testInsert()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('INSERT INTO users (username, email) values ("Ataur", "email@site.com")');
        $this->assertTrue($stmt instanceof \mysqli_stmt);
        $this->assertTrue($stmt->insert_id > 0);
    }

    public function testSelectAfterInsert()
    {
        $kuery = $this->getKuery();

        $insertStmt = $kuery->run('INSERT INTO users (username, email) values ("SelectAtaur", "email@site.com")');

        $selectStmt = $kuery->run('SELECT * FROM users WHERE username = ? LIMIT 1', ['SelectAtaur'], 's');
        $this->assertTrue($selectStmt instanceof \mysqli_stmt);
        $user = $kuery->getSingleRow($selectStmt);

        $this->assertSame('SelectAtaur', $user->username);
    }

    public function testAllRows()
    {
        $kuery = $this->getKuery();

        $insertStmt = $kuery->run('INSERT INTO users (username, email) values ("SelectAllAtaur", "email@site.com")');

        $stmt = $kuery->run('SELECT * FROM users WHERE id > ?', [0], 'i');
        $users = $kuery->getAllRows($stmt);

        $this->assertTrue(is_array($users));

        $rows = 0;
        foreach ($users as $user) {
            $rows++;
            $this->assertTrue($user instanceof \stdClass);
        }

        $this->assertTrue($rows > 0, 'Did not return any rows, expected all rows');
    }

    public function testYieldAllRows()
    {
        $kuery = $this->getKuery();


        $insertStmt = $kuery->run('INSERT INTO users (username, email) values ("YieldAllAtaur", "email@site.com")');

        $stmt = $kuery->run('SELECT * FROM users WHERE id > ?', [0], 'i');
        $users = $kuery->yieldAllRows($stmt);

        $this->assertTrue($users instanceof \Generator);

        $rows = 0;
        foreach ($users as $user) {
            $rows++;
            $this->assertTrue($user instanceof \stdClass);
        }

        $this->assertTrue($rows > 0, 'Did not return any rows, expected all rows');
    }

    public function testInvalidPrepare()
    {
        $kuery = $this->getKuery();

        $this->expectException(InvalidStatementException::class);

        $kuery->prepare('SELECT FROM table');
    }

    public function testInvalidBindParams()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->prepare('SELECT * FROM users');

        $this->expectException(InvalidBindParamException::class);

        $kuery->bind($stmt, 'i', ['a', 'b']);
    }

//    public function testInvalidExecute()
//    {
//        $kuery = $this->getKuery();
//
//        $this->expectException(InvalidStatementException::class);
//
//        $kuery->execute();
//    }

    public function testFetchResultOnNonSelectQuery()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('UPDATE users SET id = 1 WHERE id = 0');

        try {
            $kuery->getResult($stmt);
            $this->fail();
        } catch (InvalidStatementException $ex) {
            $this->assertSame('Result rows are only available for SELECT queries!', $ex->getMessage());
        }
    }

    public function testSingleRowNotFound()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('SELECT * FROM users WHERE id = 0 LIMIT 1');

        $user = $kuery->getSingleRow($stmt);

        $this->assertSame(null, $user);
    }

    public function testMultiRowNotFound()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('SELECT * FROM users WHERE id = 0');

        $users = $kuery->getAllRows($stmt);

        $this->assertSame([], $users);
    }

    public function testYieldRowNotFound()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('SELECT * FROM users WHERE id = 0');

        $users = $kuery->yieldAllRows($stmt);

        foreach ($users as $user) {
            $this->fail('Found data in empty yield');
        }

        $this->assertTrue($users instanceof \Generator);
    }

    public function testNonBindStmt()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('SELECT * FROM users WHERE id = 0');

        $result = $kuery->execute($stmt);

        $this->assertSame(true, $result);
    }
}
