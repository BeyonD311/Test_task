<?php

namespace App\Services\Connections;

use App\Interfaces\ConnectionInterface;
use App\Services\Connections\Options\DB;
use App\Services\Driver;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use JetBrains\PhpStorm\Pure;

class Asterisk implements ConnectionInterface
{
    protected DatabaseManager $db;
    protected Driver $driver;

    public function __construct(DB $db)
    {
        $this->db = app('db');
        $this->driver = new Driver($db);
        $this->driver->setDriver('asterisk', 'mysql',$this->installingTable($db));
    }

    public function connection(): Builder
    {
        return $this->db->connection($this->driver->getConfig())->table('cdr');
    }

    /**
     * @param DB $db
     * @return string
     */
    #[Pure] private function installingTable(DB $db): string
    {
        if(empty($db->getSchema())) {
            return 'asteriskcdrdb';
        }
        return  $db->getSchema();
    }

    public function getOptions(): array
    {
        return config("database.connections.asterisk");
    }
}
