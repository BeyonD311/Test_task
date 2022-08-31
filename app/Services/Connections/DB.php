<?php

namespace App\Services\Connections;

class DB extends Host
{
    protected string $table;

    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

}
