<?php

namespace App\Services\Connections\Domains\Factory;
use ReflectionClass;

class Dto
{
    /**
     * @throws \ReflectionException
     */
    public static function getInstance(string $dto, array $values)
    {
        $reflection = new ReflectionClass($dto);
        foreach ($reflection->getProperties() as $property) {
            if(isset($values[$property->getName()])) {
                $property->setValue($values[$property->getName()]);
            }
        }
        return $reflection->newInstance();
    }
}
