<?php

namespace App\Services\Connections\Infrastructure;

use App\Services\Connections\Domains\In\ConnectionInterface;
use App\Services\Connections\Domains\Dto\DB;
use App\Services\Driver;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Illuminate\Queue\SerializesModels;

class Asterisk implements ConnectionInterface
{
    use SerializesModels;
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
    private function installingTable(DB $db): string
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
