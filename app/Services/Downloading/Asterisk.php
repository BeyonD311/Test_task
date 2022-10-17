<?php

namespace App\Services\Downloading;

use App\Services\Protocols\Scp;
use App\Services\Driver;
use App\Services\Connections\Options\Host;
use App\Services\Protocols\ScpSsh2;
use Illuminate\Support\Facades\Artisan;

class Asterisk extends DataService
{
    protected static string $path;

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
        $driver->setDriver('asterisk-'.$this->db->getId(), 'mysql','asteriskcdrdb');
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
            $page += 1;
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
        $scp = new ScpSsh2($this->server, 'temp');
        $items = $this->crawlingPages();
        if(!empty($items->current())) {
            $date = $items->current()->calldate;
            foreach ($items as $item) {
                if($item->recordingfile != "" && $this->checkFileExists($item->recordingfile)) {
                    $scp->setPathDownload(self::$path.date("Y/m/d", strtotime($item->calldate)). "/".$item->recordingfile);
                    Artisan::call('file', [
                        'connections' => serialize($scp),
                        'item' => $item,
                        'type' => "Asterisk"
                    ]);
                }
            }
            $this->getInstanceLastUpdate()->updateOrCreate($this->db->getId(), $date);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkFileExists(string $name): bool
    {
        $tempName = preg_replace("/\.[a-z0-9]$/", "", $name);
        $wav = "$tempName.wav";
        $mp3 = "$tempName.mp3";
        if(!file_exists("/var/www/storage/audio/".$wav) || !file_exists("/var/www/storage/audio/".$mp3))
        {
            return true;
        }
        return false;
    }
}
