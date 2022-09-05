<?php

namespace App\Http\Controllers;
use App\Models\CDR;
use App\Services\Protocols\Scp;
use App\Services\Downloading\Asterisk;
use App\Services\Driver;
use App\Services\Connections\Server;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Files;

class TestController extends Controller
{
    public function test() {
        /*$host = new \App\Services\Hosts\DB();
        $host->setTable('asteriskcdrdb')
            ->setId(1)
            ->setLogin("user")
            ->setPass('P@ssw0rd1')
            ->setHost("10.3.0.10")
            ->setPort(3306);
        $server = new Server();
        $server->setHost("10.3.0.10")
            ->setPort(22)
            ->setLogin("root")
            ->setPass('!DLP$tend%');
        $asterisk = new Asterisk($server, $host);
        $asterisk->download();*/
    }

    //
}
