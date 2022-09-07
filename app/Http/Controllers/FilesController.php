<?php

namespace App\Http\Controllers;

use App\Models\Connections;
use App\Services\Connections\Asterisk;
use App\Services\Connections\Cisco;
use App\Services\Connection;
use Illuminate\Http\Request;

class FilesController extends Controller
{
    public function __construct(protected Connections $connections)
    {}


    public function store(Request $request)
    {
        app('db');
        $items = Connections::query()->where(["id", "=", "1"])->get()->chunk(100);
        dump($items);
    }
}
