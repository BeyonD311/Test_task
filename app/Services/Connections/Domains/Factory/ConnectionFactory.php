<?php

namespace App\Services\Connections\Domains\Factory;

use App\Interfaces\Host;
use App\Services\Connections\Domains\Dto\Connection;
use App\Services\Connections\Domains\Interfaces\Connection as ConnectionInterface;

class ConnectionFactory
{
    public static function getInstance(string $type, Connection $connection): ConnectionInterface
    {

    }
}
