<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    protected $table = "files";
    protected $fillable = [
        "name",
        "connections_id",
        "exception",
        "call_at",
        "load_at"
    ];
}
