<?php

namespace App\Interfaces;

interface IBuilderQuery
{
    /**
     * Создание полей
     * @param string $name
     * @param string $value
     * @param string $operator - используется для состовления полей в db
     * @return $this
     */
    public function addFiled(string $name,string $value, string $operator = ""): static;
}
