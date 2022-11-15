<?php

namespace App\Services\Factory;

use App\Services\Dto\Connection;
use App\Services\Interfaces\QueryInterface;

class QueryFactory
{
    public static function getInstance(Connection $connection): QueryInterface
    {
        $nameClass = ucfirst($connection->name);
        $queryClass = "App\Services\Query\\$nameClass";
        if(!class_exists($queryClass)) {
            throw new \Exception("Query Class not found $queryClass");
        }
        return new $queryClass;
    }
}
