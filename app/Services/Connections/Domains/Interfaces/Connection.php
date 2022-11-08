<?php

namespace App\Services\Connections\Domains\Interfaces;
use \App\Services\Connections\Domains\Dto\Connection as DTO;

interface Connection
{
    public function __construct(DTO $connection);
    public function connection();
    public function getOptions(): DTO;
}
