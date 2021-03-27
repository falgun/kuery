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

// create Kuery Object with connection
$kuery = new Kuery($connection);

// get all active users
$stmt = $kuery->run('SELECT * FROM users WHERE status = ? ORDER BY id asc', [1], 'i');
$users = $kuery->fetchAll($stmt); // array of objects

// get a single user who has id = 1
$stmt = $kuery->run('SELECT * FROM users WHERE id = ? ORDER BY id asc LIMIT 1', [1], 'i');
$user = $kuery->fetchOne($stmt); // single object

// insert a row
$kuery->run('INSERT INTO users (username, email) values (?, ?)', ['Bob', 'bob@email.com']);

// update a row
$kuery->run('UPDATE users set status = ? WHERE id = ?', [0, 99]);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
