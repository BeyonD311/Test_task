<?php

namespace App\Models;

use App\Services\Dto\Connection;
use App\Services\Factory\Dto;
use App\Services\Dto\DB;
use App\Services\Dto\Server;
use Illuminate\Database\Eloquent\Collection;

class Connections extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'connections';

    protected $fillable = [
        "id",
        "name",
        "mac_address",
        "type_connection"
    ];

    public function databaseConnection()
    {
        return $this->hasOne(DatabaseConnections::class, 'connection_id', 'id');
    }

    public function serverConnection()
    {
        return $this->hasOne(ServerConnections::class, 'connection_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(Files::class, 'connections_id', 'id');
    }

    public function getWorkingConnections(): Collection
    {
        return static::all();
    }

    public function getWorkingConnection(array $options): array
    {
        return $this->where([
            ['id', '=', $options['connection']]
        ])->get()
            ->map(function ($item) use ($options) {
                $items = $item->files()
                    ->where([
                        ["exception", "=", "empty"],
                        ["call_at", ">=", $options['date_from']],
                        ["call_at", "<=", $options['date_to']]
                    ])->paginate($options['size'], page: $options['page']);
                $resultItems = [];
                foreach ($items->items() as $file) {
                    $resultItems[] = [
                        "name" => $file->name,
                        "connection_id" => $options['connection'],
                        "exception" => $file->exception
                    ];
                }
                return [
                    "name" => $item->name,
                    "id" => $item->id,
                    "files" => $resultItems,
                    "download_files" => $items->total(),
                    "page" => $items->currentPage(),
                    "last_page" => $items->lastPage()
                ];
            })->toArray()[0];
    }

    /**
     * Получение информации о соединении
     * @param int $id
     * @return array
     * @throws \App\Exceptions\Connection
     */
    public static function infoFromConnection(int $id = 0): Connection
    {
        $connection = self::with(['serverConnection', 'databaseConnection'])->where([
            ['id', '=', $id]
        ])->first()->toArray();
        if (empty($connection)) {
            throw new \App\Exceptions\Connection("Соединений не найдено", 404);
        }
        if(is_null($connection['database_connection'])) {
            $connection['database_connection'] = [];
        }
        $connection['db'] = Dto::getInstance(DB::class, $connection['database_connection']);
        $connection['server'] = Dto::getInstance(Server::class, $connection['server_connection']);
        unset($connection['database_connection'], $connection['server_connection']);
        return Dto::getInstance(Connection::class, $connection);
    }
}
