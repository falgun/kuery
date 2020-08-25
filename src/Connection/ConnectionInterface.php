<?php

namespace Falgun\Kuery\Connection;

interface ConnectionInterface
{

    public function connect():void;

    public function disconnect(): bool;

    public function getConnection(): object;
}
