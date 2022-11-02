<?php

namespace App\Services\Downloading;

use App\Services\Connections\Options\DB;
use App\Services\Connections\Options\Server;
use App\Services\Driver;
use App\Services\Protocols\Scp;
use App\Services\Protocols\ScpSsh2;
use Illuminate\Support\Facades\Artisan;
use App\Services\Query\Asterisk as Query;
use Illuminate\Support\Facades\Log;

class Asterisk extends DataService
{
    protected static string $path;

    protected string $lastUpdateConnection = "database_connection_id";

    public function __construct(
        protected Server $server,
        protected DB $db
    )
    {
        self::$path = env('ASTERISK_DIR');
        parent::__construct();
        $this->timeZone = new \DateTimeZone("Europe/Moscow");
    }

    public function getItems()
    {
        $connection = new \App\Services\Connections\Asterisk($this->db);
        $query = new Query($connection);
        $query->onCrawlingPages();
        $query->setPaginate(1, 100);
        $dateNow = new \DateTime('now', $this->timeZone);
        return $query->getItems($this->getDate()->format('Y-09-01 00:00:00'), $dateNow->format('Y-m-d 23:59:59'));
    }

    /**
     * @return \DateTimeInterface
     * @throws \Exception
     */
    public function download(): \DateTimeInterface
    {
        $scp = new ScpSsh2($this->server, 'temp');
        $items = $this->getItems();
        if(empty($items->current())) {
            return $this->getDate();
        }
        $date = $items->current()->calldate;
        foreach ($items as $item) {
            if($item->file != "" && $this->checkFileExists($item->file)) {
                $scp->setPathDownload(self::$path.date("Y/m/d", strtotime($item->calldate)). "/".$item->file);
                /*Artisan::call('file', [
                    'connections' => serialize($scp),
                    'item' => $item,
                    'type' => "Asterisk"
                ]);*/
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
        $tempName = preg_replace("/\.[a-z0-9]*$/", "", $name);
        $wav = "$tempName-{$this->db->getId()}.wav";
        $mp3 = "$tempName-{$this->db->getId()}.mp3";
        if(file_exists("/var/www/storage/audio/".$wav)) {
            return false;
        } elseif (file_exists("/var/www/storage/audio/".$mp3)) {
            return false;
        }
        return true;
    }
}
