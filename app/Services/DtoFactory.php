<?php

namespace App\Services;

class DtoFactory
{
    /**
     * @param string $class
     * @param array $params
     * @return object
     * @throws \ReflectionException
     */
    public static function createDto(string $class, array $params): object
    {
        $class = new \ReflectionClass($class);
        $dto = $class->newInstance();
        foreach ($class->getProperties() as $property) {
            if(isset($params[$property->getName()])) {
                $property->setAccessible(true);
                $property->setValue($dto, $params[$property->getName()]);
            }
        }
        return $dto;
    }
}
