<?php

namespace App\Http\Controllers;
use App\Models\CDR;
use App\Services\Connections\Scp;
use App\Services\Driver;
use App\Services\Hosts\Server;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function test() {
        $db = new \App\Services\Hosts\DB;
        $db->setHost('10.3.0.10')
            ->setPort('3306')
            ->setLogin('user')
            ->setPass('P@ssw0rd1');
        $driver = new Driver($db);
        $driver->setDriver('asterisk', 'mysql','asteriskcdrdb');
        $db = app('db');
        $server = new Server();
        $server->setHost("10.3.0.10")
            ->setLogin('root')
            ->setPass('!DLP$tend%');
        $scp = new Scp($server);
        $items = $db->connection($driver->getConfig())->table('cdr')->where('calldate', '>', '2022-6-05')
            ->orderBy('calldate', 'desc')
            ->get()
            ->filter(function ($item) {
                return (bool)$item->recordingfile === true;
            });
        foreach ($items as $item) {
            $name = preg_replace("/.[a-z0-9]+$/", ".json", $item->recordingfile);
            file_put_contents('/var/www/storage/callInfo/'.$name, print_r(json_encode($item, JSON_FORCE_OBJECT|JSON_PRETTY_PRINT), true));
            $path = date("Y/m/d", strtotime($item->calldate)). "/".$item->recordingfile;
            $scp->setPathDownload($path)
                ->download();
        }
    }

    //
}
