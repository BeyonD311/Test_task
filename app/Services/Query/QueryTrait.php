<?php

namespace App\Services\Query;

trait QueryTrait
{
    protected array $queryMap = [];

    public function resetQueryMap(): static
    {
        $this->queryMap = [];
    }

}
