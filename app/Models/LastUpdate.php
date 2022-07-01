<?php

namespace App\Models;

class LastUpdate extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'last_update';

    protected $fillable = [
        'database_connection_id',
        'server_connection_id',
        'update'
    ];

    public $timestamps = false;
}
