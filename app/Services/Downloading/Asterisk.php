<?php

namespace App\Services\Downloading;

use App\Services\Connections\Options\DB;
use App\Services\Connections\Options\Server;
use App\Services\Driver;
use App\Services\Downloading\Type\Scp;
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
        $items = $this->getItems();
        if(empty($items->current())) {
            return $this->getDate();
        }
        $date = $items->current()->calldate;
        foreach ($items as $item) {
            if($item->file != "" && $this->checkFileExists($item->file)) {
                $item->outputName = $this->generateName($item->file);
                $item->file = self::$path.date("Y/m/d", strtotime($item->calldate)). "/".$item->file;
                Artisan::call('file', [
                    'connections' => serialize($this->server),
                    'item' => $item,
                    'protocol' => Scp::class,
                    'type' => "Asterisk"
                ]);
            }
        }
        return new \DateTime($date, $this->timeZone);
    }

    protected function generateName(string $name): string
    {
        $splitName = explode(".", $name);
        $expansion = array_pop($splitName);
        return implode(".", $splitName)."-{$this->db->getConnectionId()}.$expansion";
    }

    /**
     * @param string $name
     * @return bool
     */
    private function checkFileExists(string $name): bool
    {
        $tempName = preg_replace("/\.[a-z0-9]*$/", "", $name);
        $wav = "$tempName-{$this->db->getConnectionId()}.wav";
        $mp3 = "$tempName-{$this->db->getConnectionId()}.mp3";
        if(file_exists("/var/www/storage/audio/".$wav)) {
            return false;
        } elseif (file_exists("/var/www/storage/audio/".$mp3)) {
            return false;
        }
        return true;
    }
}
