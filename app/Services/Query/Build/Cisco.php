<?php

namespace App\Services\Query\Build;

class Cisco extends Build
{
    protected array $innerParams = [];

    public function __construct()
    {
        $this->queryMap = [
            "json" => [
                "requestParameters" => [

                ]
            ]
        ];
    }

    public function addFiled(string $name, string|array $value, string $operator = ""): static
    {
        if($name == 'fieldName') {

        }
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
