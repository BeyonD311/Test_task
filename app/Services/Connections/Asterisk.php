<?php

namespace App\Services\Connections;

use App\Services\Interfaces\Connection as ConnectionInterface;
use App\Services\Dto\Connection as ConnectionDTO;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\SerializesModels;

class Asterisk implements ConnectionInterface
{
    use SerializesModels;
    protected DatabaseManager $db;
    protected string $connectionName;

    /**
     * @param ConnectionDTO $connection
     */
    public function __construct(
        protected ConnectionDTO $connection
    )
    {
        $this->db = app('db');
        $this->setConfig();
    }

    public function connection(): Builder
    {
        return $this->db->connection($this->connectionName)->table($this->connection->db->table);
    }

    /**
     * Установка соединения с database из параметров конфигурации @ConnectionDTO
     * @return string
     */
    private function setConfig(): void
    {
        $db = $this->connection->db;
        $this->connectionName = __CLASS__.$this->connection->id;
        $config = config("database.connections.mysql");
        $config['host'] = $db->host;
        $config['port'] = $db->port;
        $config['database'] = $db->schema;
        $config['username'] = $db->login;
        $config['password'] = $db->pass;
        config(["database.connections.$this->connectionName" => $config]);
    }

    public function getParams(): ConnectionDTO
    {
        return $this->connection;
    }

    public function getParam(string $param): mixed
    {
        if(isset($this->connection->{$param})) {
            return $this->connection->{$param};
        }
        return null;
    }

    public function disconnect(): void
    {
        $this->db->disconnect($this->connectionName);
    }

    public function checkConnection(): bool
    {
        try {
            $this->db->connection($this->connectionName)->getPdo();
        } catch (\Exception $exception) {
            return false;
        }
        return true;
    }
}
