<?php

namespace App\Services\Query\Build;

use App\Services\Query\Query;

class Cisco extends Query
{
    protected array $innerParams = [];

    public function __construct()
    {
        $this->queryMap = [
            "json" => [
                "requestParameters" => array_values($this->innerParams)
            ]
        ];
    }

    public function addFiled(string $name, string $value, string $operator = ""): static
    {
        $this->innerParams[$name][] = [
            "fieldName" => $name,
            "fieldConditions" => [

            ]
        ];
        return $this;
    }

    public function removeField(int|string $field): static
    {
        return $this;
    }

    public function updateField(int|string $field, string $name, string $value, string $operator = ""): static
    {
        return $this;
    }
}
