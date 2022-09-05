<?php

namespace App\Services\Query\Build;

use App\Interfaces\IBuilderQuery;

abstract class Build implements IBuilderQuery
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
