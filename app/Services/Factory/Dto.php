<?php

namespace App\Services\Factory;
use ReflectionClass;

class Dto
{
    /**
     * @throws \ReflectionException
     */
    public static function getInstance(string $dto, array $values)
    {
        $reflection = new ReflectionClass($dto);
        $dto = $reflection->newInstance();
        foreach ($reflection->getProperties() as $property) {
            if(isset($values[$property->getName()])) {
                $property->setAccessible(true);
                $property->setValue($dto, $values[$property->getName()]);
            }
        }
        return $dto;
    }
}
