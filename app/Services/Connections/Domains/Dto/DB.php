<?php

namespace App\Services\Connections\Domains\Dto;

class DB
{
    public string $host;
    public int $port;
    public string $login;
    public string $pass;
    public string $table;
    public string $schema;
}
