<?php

namespace App\Services\Factory;

use App\Services\Dto\Connection;
use \App\Services\Interfaces\Connection as ConnectionInterface;

class ConnectionFactory extends Factory
{
    protected static $namespace = "App\Services\Connections\\";

    /**
     * @param Connection $dto
     * @return ConnectionInterface
     * @throws \App\Exceptions\Connection
     * @throws \ReflectionException
     */
    public static function getInstance(Connection $dto): ConnectionInterface
    {
        $object = parent::getInstance($dto);
        if($object instanceof ConnectionInterface) {
            return $object;
        }
        throw new \App\Exceptions\Connection("Неверный тип");
    }
}
