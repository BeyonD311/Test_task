<?php

namespace App\Models;

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
}
