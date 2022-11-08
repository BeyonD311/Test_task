<?php

namespace App\Services\Connections\Domains\Dto;

class Connection
{
    public int $id;
    public string $type_connection;
    public string $name;
    public Server $server;
    public DB $db;
}
