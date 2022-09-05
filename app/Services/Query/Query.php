<?php

namespace App\Services\Query;

use App\Interfaces\IBuilderQuery;

abstract class Query implements  IBuilderQuery
{
    protected array $queryMap = [];

    public function resetQuery(): void
    {
        $this->queryMap = [];
    }

    public function getQuery(): array
    {
        return $this->queryMap;
    }
}
