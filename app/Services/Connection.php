<?php

namespace App\Services;

use App\Interfaces\Host;
use \App\Interfaces\ConnectionInterface;

class Connection
{
    protected ConnectionInterface $connection;

    public function setConnection(ConnectionInterface $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    public function connection()
    {
        return $this->connection->connection();
    }

    public function getOptions()
    {
        return $this->connection->getOptions();
    }

}
