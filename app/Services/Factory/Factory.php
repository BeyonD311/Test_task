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
        $name = ucfirst($dto->name);
        static::$namespace .= ucfirst($dto->name);
        if(!class_exists(static::$namespace))
        {
            throw new \App\Exceptions\Connection("Типа соединения не существует ". static::$namespace);
        }
        $reflection = new \ReflectionClass(static::$namespace);
        static::$namespace = str_replace($name, "", static::$namespace);
        return $reflection->newInstance($dto);
    }
}
