<?php

namespace App\Services\Query\Build;

use App\Services\Query\Query;

class Asterisk extends Query
{
    public function addFiled(string $name, string $value, string $operator = ""): static
    {
        if($operator === "") {
            throw new \App\Exceptions\Query("Нет оператора", 409);
        }
        $this->queryMap[] = [$name,$operator, $value];
        return $this;
    }

    public function removeField(string|int $field): static
    {
        if(isset($this->queryMap[$field]))
        {
            unset($this->queryMap[$field]);
        }
        return $this;
    }

    public function updateField(int|string $field, string $name, string $value, string $operator = ""): static
    {
        if(isset($this->queryMap[$field]))
        {
            $this->queryMap[$field] = [$name, $operator, $value];
        }
        return $this;
    }
}
