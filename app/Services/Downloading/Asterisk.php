<?php

namespace App\Services\Downloading;

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
        $this->timeZone = new \DateTimeZone("Europe/Moscow");
    }

    public function getItems($page, $count): array
    {
        $db = app('db');
        $driver = new Driver($this->db);
        $driver->setDriver('asterisk', 'mysql','asteriskcdrdb');
        $dateNow = new \DateTime('now', $this->timeZone);
        $where = [
            ['cdr.calldate', '>=', $this->getDate()->format('Y-m-d 00:00:00')],
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

    /**
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public function download(): \DateTimeInterface
    {
        $scp = new ScpSsh2($this->server, 'temp');
        $items = $this->crawlingPages();
        if(empty($items->current())) {
            return $this->getDate();
        }
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
        return new \DateTime($date, $this->timeZone);
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
