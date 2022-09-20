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
        $info = $this->connections->infoFromConnection(1);
        /*$connection = match(strtolower($info['name'])) {
            "asterisk" => new QueryAsterisk(new Asterisk($info['database_connection'])),
            "cisco" => new QueryCisco(new Cisco($info['server_connection']))
        };*/
        $asterisk = new \App\Services\Downloading\Asterisk($info['server_connection'], $info['database_connection']);
        $asterisk->download();
    }

    //
}
