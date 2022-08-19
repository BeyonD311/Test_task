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
        $items = $db->connection($driver->getConfig())->table('cel')
            ->leftJoin('cdr', 'cel.linkedid', '=', 'cdr.uniqueid')
            ->where([
                ['cel.eventtime', '>', $date],
                ['cel.eventtype', '=', "BRIDGE_EXIT"],
                ['cdr.recordingfile', '!=', null]
            ])
            ->orderBy('cel.eventtime', 'DESC')
            ->groupBy('cel.linkedid', 'cel.id')
            ->paginate($count, page: $page);
        if($page > $items->lastPage()) {
            return [];
        }
        return $items->items();
    }

    public function crawlingPages(): \Generator
    {
        $page = 1;
        $count = 10;
        while (true) {
            $items = $this->getItems($page, $count);
            if (empty($items)) {
                break;
            }
            foreach ($items as $item) {
                yield $item;
            }
            $page++;
        }
    }

    public function download()
    {
        $scp = new Scp($this->server, 'audio');
        $items = $this->crawlingPages();
        if(!empty($items->current())) {
            $this->getInstanceLastUpdate()->updateOrCreate($this->db->getId(), $items->current()->eventtime);
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
    }
}
