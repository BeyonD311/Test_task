<?php

namespace App\Services\Factory;

use App\Services\Dto\Connection;
use App\Services\Interfaces\Connection as ConnectionInterface;

class Factory
{
    protected static $namespace;

    /**
     * @throws \ReflectionException
     * @throws \App\Exceptions\Connection
     */
    public static function getInstance(Connection $dto)
    {
        static::$namespace .= ucfirst($dto->name);
        if(!class_exists(static::$namespace))
        {
            throw new \App\Exceptions\Connection("Типа соединения не существует");
        }
        $reflection = new \ReflectionClass(static::$namespace);
        return $reflection->newInstance($dto);
    }
}
