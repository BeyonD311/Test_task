<?php

namespace App\Services\Connections\Options;

class DB extends Host
{
    protected string $table;
    protected string $schema = "";

    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setSchema(string $schema): static
    {
        $this->schema = $schema;
        return $this;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

}
