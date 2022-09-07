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

    /**
     * @param Host $host
     * @return mixed
     */
    public function connection(Host $host)
    {
        return $this->connection->connection($host);
    }

    public function getOptions()
    {
        return $this->connection->getOptions();
    }

}
