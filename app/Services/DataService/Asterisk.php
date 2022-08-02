<?php

namespace App\Services\DataService;

use App\Services\Connections\Scp;
use App\Services\Driver;
use App\Services\Hosts\Host;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Asterisk extends DataService
{
    const path = "/var/spool/asterisk/monitor/";

    protected string $lastUpdateConnection = "database_connection_id";

    public function __construct(
        protected Host $server,
        protected Host $db
    )
    {
        parent::__construct();
    }

    private function getItems(): array
    {
        $db = app('db');
        $driver = new Driver($this->db);
        $driver->setDriver('asterisk', 'mysql','asteriskcdrdb');
        $date = date('Y-m-d H:i:s', $this->getInstanceLastUpdate()->getTimestamp($this->db->getId()));
        $items = $db->connection($driver->getConfig())->table('cdr')
            ->where('calldate', '>', $date)
            ->orderBy('calldate', 'desc')
            ->get();
        return $items->toArray();
    }

    public function download()
    {
        $scp = new Scp($this->server, 'audio');
        $items = $this->getItems();
        if(!empty($items)) {
            $this->getInstanceLastUpdate()->updateOrCreate($this->db->getId(), $items[0]->calldate);
        }
        foreach ($items as $item) {
            if($item->recordingfile != "") {
                $path = self::path.date("Y/m/d", strtotime($item->calldate)). "/".$item->recordingfile;
                $scp->setPathDownload($path);
                Artisan::call('file', [
                    'connections' => serialize($scp),
                    'item' => $item,
                    'type' => "Asterisk"
                ]);
            }
        }
    }

    private function saveFileInfo($item)
    {
        $name = preg_replace("/\.[0-9a-z]+$/", "", $item->recordingfile);
        $result = [
            "service" => 'asterisk',
            "calldate" => $item->calldate,
            "src" => $item->src,
            "dst" => $item->dst,
            "duration" => $item->duration,
            "uniqueid" => $item->uniqueid,
            "did" => $item->did
        ];
        file_put_contents("/var/www/storage/callInfo/$name.json", print_r(json_encode($result, JSON_PRETTY_PRINT), true));
    }
}
