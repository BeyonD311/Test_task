<?php

namespace App\Services\Interfaces;

use \App\Services\Dto\Connection as ConnectionDTO;

interface Connection
{
    public function __construct(ConnectionDTO $connection);
    public function connection();
    public function getParams(): ConnectionDTO;
    public function getParam(string $param): mixed;
    public function disconnect(): void;
    public function checkConnection(): bool;
}
