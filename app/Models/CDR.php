<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Model;

class CDR extends Model
{
    protected $table = "cdr";

    protected $fillable = ["calldate"];

    protected $connection = "mysql";
}
