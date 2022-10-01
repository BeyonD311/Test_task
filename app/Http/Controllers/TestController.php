<?php

namespace App\Http\Controllers;

use App\Services\Connections\Options\Server;
use App\Services\Query\Asterisk as QueryAsterisk;
use App\Services\Query\Cisco as QueryCisco;
use App\Services\Connections\Asterisk;
use App\Services\Connections\Cisco;
use App\Models\Connections;

class TestController extends Controller
{
    public function __construct(protected Connections $connections)
    {}

    public function test() {
        app('db');
        $info = $this->connections->infoFromConnection(2);
        $cisco = new Cisco($info['server_connection']);
        $query = new \App\Services\Query\Cisco($cisco);
        $query->setPaginate(1,10);
        foreach ($query->getItems("2022-01-01", "2022-12-31") as $key => $file) {
            dump($key);
        }
    }

    //
}
