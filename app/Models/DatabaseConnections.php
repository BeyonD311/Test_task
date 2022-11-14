<?php

namespace App\Models;

class DatabaseConnections extends \Illuminate\Database\Eloquent\Model
{
    protected $fillable = [
        "id",
        "connection_id",
        "host",
        "port",
        "login",
        "pass",
        "availability",
        "error",
        "table",
        "schema",
        "created_at",
        "updated_at"
    ];

    public function connection()
    {
        return $this->belongsTo(Connections::class, 'id', 'connection_id');
    }
}
