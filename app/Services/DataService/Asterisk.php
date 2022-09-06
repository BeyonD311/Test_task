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

    public function getItems($page, $count): array
    {
        $db = app('db');
        $driver = new Driver($this->db);
        $driver->setDriver('asterisk', 'mysql','asteriskcdrdb');
        $date = date('Y-m-d H:i:s', $this->getInstanceLastUpdate()->getTimestamp($this->db->getId()));
        /**
         * @var \Illuminate\Database\Query\Builder $items
         */
        $timeZone = new \DateTimeZone("Europe/Moscow");
        $dateNow = new \DateTime('now', $timeZone);
        $where = [
            ['cdr.calldate', '>=', date("Y-m-d 00:00:00", strtotime($date))],
            ['cdr.calldate', '<=', $dateNow->format('Y-m-d H:i:s')],
            ['cdr.disposition', '=', "ANSWERED"],
            ['cdr.recordingfile', '!=', null]
        ];
        Log::info(json_encode($where, JSON_PRETTY_PRINT));
        $items = $db->connection($driver->getConfig())->table('cdr')
            ->where($where)
            ->groupBy('cdr.linkedid')
            ->orderBy('cdr.calldate', 'DESC')
            ->paginate($count, page: $page);
        if($page > $items->lastPage()) {
            return [];
        }
        return $items->items();
    }

    public function crawlingPages(): \Generator
    {
        $page = 1;
        $count = 100;
        while (true) {
            $items = $this->getItems($page, $count);
            $page++;
            if (empty($items)) {
                break;
            }
            foreach ($items as $item) {
                yield $item;
            }
        }
    }

    public function download()
    {
        $scp = new Scp($this->server, 'audio');
        $items = $this->crawlingPages();
        if(!empty($items->current())) {
            $this->getInstanceLastUpdate()->updateOrCreate($this->db->getId(), $items->current()->calldate);
            foreach ($items as $item) {
                if($item->recordingfile != "") {
                    Log::info(json_encode($item, JSON_PRETTY_PRINT));
                    $tempName = preg_replace("/\.[a-z0-9]*$/", "", $item->recordingfile);
                    $wav = "$tempName.wav";
                    $mp3 = "$tempName.mp3";
                    unset($tempName);
                    if(!file_exists("/var/www/storage/audio/".$wav) && !file_exists("/var/www/storage/audio/".$mp3)) {
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
        }
    }
}
