<?php

namespace App\Services\Query;

use App\Interfaces\ConnectionInterface;
use App\Interfaces\QueryInterface;

abstract class Query implements QueryInterface
{
    public function __construct(
        protected ConnectionInterface $connection
    )
    {
    }
}
