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
        $confArray = require __DIR__ . '/config.php';

        $configuration = Configuration::fromArray($confArray);
        $connection = new MySqlConnection($configuration);
        $connection->connect();

        $kuery = new Kuery($connection);

        $kuery->run('TRUNCATE users');
    }

    public function getKuery(): Kuery
    {
        $confArray = require __DIR__ . '/config.php';

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
        $user = $kuery->fetchOne($selectStmt);

        $this->assertSame('SelectAtaur', $user->username);
    }

    public function testSelectAsArrayAfterInsert()
    {
        $kuery = $this->getKuery();

        $insertStmt = $kuery->run('INSERT INTO users (username, email) values ("SelectAtaur", "email@site.com")');

        $selectStmt = $kuery->run('SELECT * FROM users WHERE username = ? LIMIT 1', ['SelectAtaur'], 's');
        $this->assertTrue($selectStmt instanceof \mysqli_stmt);

        $user = $kuery->fetchOneAsArray($selectStmt);

        $this->assertSame('SelectAtaur', $user['username']);
    }

    public function testAllRows()
    {
        $kuery = $this->getKuery();

        $insertStmt = $kuery->run('INSERT INTO users (username, email) values ("SelectAllAtaur", "email@site.com")');

        $stmt = $kuery->run('SELECT * FROM users WHERE id > ?', [0], 'i');
        $users = $kuery->fetchAll($stmt);

        $this->assertTrue(is_array($users));

        $rows = 0;
        foreach ($users as $user) {
            $rows++;
            $this->assertTrue($user instanceof \stdClass);
        }

        $this->assertTrue($rows > 0, 'Did not return any rows, expected all rows');
    }

    public function testAllRowsAsArray()
    {
        $kuery = $this->getKuery();

        $insertStmt = $kuery->run('INSERT INTO users (username, email) values ("SelectAllAtaur", "email@site.com")');

        $stmt = $kuery->run('SELECT * FROM users WHERE id > ?', [0], 'i');
        $users = $kuery->fetchAllAsArray($stmt);

        $this->assertTrue(is_array($users));

        $rows = 0;
        foreach ($users as $user) {
            $rows++;
            $this->assertIsArray($user);
        }

        $this->assertTrue($rows > 0, 'Did not return any rows, expected all rows');
    }

    public function testYieldAllRows()
    {
        $kuery = $this->getKuery();

        $insertStmt = $kuery->run('INSERT INTO users (username, email) values ("YieldAllAtaur", "email@site.com")');

        $stmt = $kuery->run('SELECT * FROM users WHERE id > ?', [0], 'i');
        $users = $kuery->yieldAll($stmt);

        $this->assertTrue($users instanceof \Generator);

        $rows = 0;
        foreach ($users as $user) {
            $rows++;
            $this->assertTrue($user instanceof \stdClass);
        }

        $this->assertTrue($rows > 0, 'Did not return any rows, expected all rows');
    }

    public function testYieldAllRowsAsArray()
    {
        $kuery = $this->getKuery();

        $insertStmt = $kuery->run('INSERT INTO users (username, email) values ("YieldAllAtaur", "email@site.com")');

        $stmt = $kuery->run('SELECT * FROM users WHERE id > ?', [0], 'i');
        $users = $kuery->yieldAllAsArray($stmt);

        $this->assertTrue($users instanceof \Generator);

        $rows = 0;
        foreach ($users as $user) {
            $rows++;
            $this->assertIsArray($user);
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

        $kuery->bind($stmt, ['a', 'b'], 'i');
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

        $user = $kuery->fetchOne($stmt);

        $this->assertSame(null, $user);
    }

    public function testSingleArrayRowNotFound()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('SELECT * FROM users WHERE id = 0 LIMIT 1');

        $user = $kuery->fetchOneAsArray($stmt);

        $this->assertSame(null, $user);
    }

    public function testMultiRowNotFound()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('SELECT * FROM users WHERE id = 0');

        $users = $kuery->fetchAll($stmt);

        $this->assertSame([], $users);
    }

    public function testYieldRowNotFound()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('SELECT * FROM users WHERE id = 0');

        $users = $kuery->yieldAll($stmt);

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

    public function testInvalidBindType()
    {
        $kuery = $this->getKuery();

        $kuery->run('INSERT INTO users (username, email, score) values ("AtaurScore", "email@site.com", 2.01)');

        try {
            $kuery->run('SELECT * FROM users LIMIT ?,?', ['0', '5'], 'sz');
            $this->fail();
        } catch (\Throwable $ex) {
            $this->assertSame(
                'mysqli_stmt::bind_param(): Argument #1 ($types) '
                . 'must only contain the "b", "d", "i", "s" type specifiers',
                $ex->getMessage()
            );
        }
    }

    public function testMultipleExecution()
    {
        $kuery = $this->getKuery();

        $id = 1;

        $stmt = $kuery->prepare('SELECT * FROM users WHERE id = ?');
        $kuery->bind($stmt, [&$id], 'i');

        $kuery->execute($stmt);
        $result = $kuery->fetchOne($stmt);

        $this->assertSame(1, $result->id);

        $id = 2;
        $kuery->execute($stmt);
        $result2 = $kuery->fetchOne($stmt);

        $this->assertSame(2, $result2->id);
    }

    public function testBindAutoResulation()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run(
            'SELECT * FROM users WHERE id < ? AND score > ? AND status = ?',
            [10, 1.1, 0]
        );

        $users = $kuery->fetchAll($stmt);

        $this->assertTrue(!empty($users));
    }

    public function testEmptyBind()
    {
        $kuery = $this->getKuery();

        $stmt = $kuery->run('SELECT * FROM users');

        $this->assertTrue($kuery->bind($stmt));
    }
}
