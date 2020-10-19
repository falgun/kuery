<?php
declare(strict_types=1);

namespace Falgun\Kuery\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Falgun\Kuery\Configuration;

final class ConfigurationTest extends TestCase
{

    public function testFromArray()
    {
        $confArray = [
            'host' => 'localhost',
            'user' => 'root',
            'password' => '',
            'database' => 'falgun'
        ];

        $configuration = Configuration::fromArray($confArray);

        $this->assertTrue($configuration instanceof Configuration);

        $this->assertSame('localhost', $configuration->host);
        $this->assertSame('root', $configuration->user);
        $this->assertSame('', $configuration->password);
        $this->assertSame('falgun', $configuration->database);
        $this->assertSame(3306, $configuration->port);
        $this->assertSame('utf8mb4', $configuration->characterSet);
    }

    public function testFromStaticNew()
    {
        $configuration = Configuration::new('localhost', 'root', '', 'falgun');

        $this->assertTrue($configuration instanceof Configuration);

        $this->assertSame('localhost', $configuration->host);
        $this->assertSame('root', $configuration->user);
        $this->assertSame('', $configuration->password);
        $this->assertSame('falgun', $configuration->database);
        $this->assertSame(3306, $configuration->port);
        $this->assertSame('utf8mb4', $configuration->characterSet);
    }

    public function testNonDefault()
    {

        $configuration = Configuration::fromArray([
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'database' => 'falgun',
                'port' => 100,
                'character-set' => 'utf8',
        ]);
        $this->assertSame(100, $configuration->port);
        $this->assertSame('utf8', $configuration->characterSet);

        $configuration2 = Configuration::new('localhost', 'invalid', 'pass', 'database', 100, 'utf8');

        $this->assertSame(100, $configuration2->port);
        $this->assertSame('utf8', $configuration2->characterSet);
    }

    public function testHostNotProvided()
    {
        try {
            Configuration::fromArray([]);
            $this->fail();
        } catch (InvalidArgumentException $ex) {
            $this->assertSame('Configuration Array must contain "host"!', $ex->getMessage());
        }
    }

    public function testUserNotProvided()
    {
        try {
            Configuration::fromArray(['host' => '']);
            $this->fail();
        } catch (InvalidArgumentException $ex) {
            $this->assertSame('Configuration Array must contain "user"!', $ex->getMessage());
        }
    }

    public function testPasswordNotProvided()
    {
        try {
            Configuration::fromArray(['host' => '', 'user' => '']);
            $this->fail();
        } catch (InvalidArgumentException $ex) {
            $this->assertSame('Configuration Array must contain "password"!', $ex->getMessage());
        }
    }

    public function testDatabaseNotProvided()
    {
        try {
            Configuration::fromArray(['host' => '', 'user' => '', 'password' => '']);
            $this->fail();
        } catch (InvalidArgumentException $ex) {
            $this->assertSame('Configuration Array must contain "database"!', $ex->getMessage());
        }
    }
}
