# Kuery

SQL statement executer.

## Install
 *Please note that PHP 8.0 or higher is required.*

Via Composer

``` bash
$ composer require falgunphp/kuery
```

## Usage
```php
<?php
use Falgun\Kuery\Kuery;
use Falgun\Kuery\Configuration;
use Falgun\Kuery\Connection\MySqlConnection;

$confArray = [
      'host' => 'localhost',
      'user' => 'username',
      'password' => 'password',
      'database' => 'falgun'
  ];

// build configuration class
$configuration = Configuration::fromArray($confArray);

// build connection class
$connection = new MySqlConnection($configuration);

// attemp to connect
$connection->connect();

// get all active users
$stmt = $kuery->run('SELECT * FROM users WHERE status = ? ORDER BY id asc', [1], 'i');
$users = $kuery->fetchAll($stmt); //array

// get a single user who has id = 1
$stmt = $kuery->run('SELECT * FROM users WHERE id = ? ORDER BY id asc LIMIT 1', [1], 'i');
$user = $kuery->fetchOne($stmt);

// insert a row
$kuery->run('INSERT INTO users (username, email) values ("UserName", "email@site.com")');

// update a row
$kuery->run('UPDATE users set status = 0 WHERE id = 1');
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
