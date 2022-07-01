<?php

namespace App\Services;

use App\Interfaces\Host;
use \App\Interfaces\ConnectionInterface;
# Для дальнейшей модарнизации
class Connection implements ConnectionInterface
{
    public function __construct
    (
        protected Host $server,
        protected Host $db
    )
    {}

    public function connect()
    {
        // TODO: Implement connect() method.
    }

    public function getStatus()
    {
        // TODO: Implement getStatus() method.
    }

    public function disconnect()
    {
        // TODO: Implement disconnect() method.
    }

    public function checkConnection()
    {
        // TODO: Implement checkConnection() method.
    }
}
