<?php

namespace App\Services\Downloading;

use App\Services\Protocols\Scp;
use App\Services\Driver;
use App\Services\Connections\Host;
use Illuminate\Support\Facades\Artisan;

class Asterisk extends DataService
{
    private static string $path;

    protected string $lastUpdateConnection = "database_connection_id";

    public function __construct(
        protected Host $server,
        protected Host $db
    )
    {
        self::$path = env('ASTERISK_DIR');
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
        $where = [
            ['cdr.calldate', '>=', date("Y-m-d 00:00:00", strtotime($date))],
            ['cdr.calldate', '<=', date('Y-m-d H:i:s')],
            ['cdr.disposition', '=', "ANSWERED"],
            ['cdr.recordingfile', '!=', null]
        ];
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
        $page = 0;
        $count = 100;
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
            $this->getInstanceLastUpdate()->updateOrCreate($this->db->getId(), $items->current()->calldate);
            foreach ($items as $item) {
                if($item->recordingfile != "" && !file_exists("/var/www/storage/audio/".$item->recordingfile)) {
                    $makePath = self::$path.date("Y/m/d", strtotime($item->calldate)). "/".$item->recordingfile;
                    $scp->setPathDownload($makePath);
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
