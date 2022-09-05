<?php

namespace App\Interfaces;

interface IBuilderQuery
{
    /**
     * Создание полей
     * @param string $name
     * @param string|array $value
     * @param string $operator - используется для состовления полей в db
     * @return $this
     */
    public function addFiled(string $name,string|array $value, string $operator = ""): static;

    /**
     * @param string|int $field
     * @return mixed
     */
    public function removeField(string|int $field): static;
    public function updateField(string|int $field, string $name,string $value, string $operator = ""): static;
    public function resetQuery(): void;
    public function getQuery(): array;
}
