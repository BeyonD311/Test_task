<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CDR;

class Cel extends Model
{
    protected $table = "cel";

    protected $fillable = ["eventtime", "eventtype"];

    public function cdr()
    {
        return $this->hasOne(CDR::class, 'uniqueid', 'linkedid');
    }
}
