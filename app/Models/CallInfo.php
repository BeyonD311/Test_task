<?php

namespace App\Models;

class CallInfo extends \Illuminate\Database\Eloquent\Model
{
    protected $table = "call_info";
    protected $fillable = [
        "src",
        "dst",
        "duration",
        "file_id"
    ];

    public $timestamps = false;

    public function file()
    {
        return $this->belongsTo(Files::class, 'id', 'file_id');
    }
}
