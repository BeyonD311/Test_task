<?php

namespace App\Services\Downloading;

use App\Services\Protocols\UcScp;
use App\Services\Driver;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Uc extends Asterisk
{
    protected string $lastUpdateConnection = "database_connection_id";

    public function getItems($page, $count): array
    {
        app('db');
        $db = app('db');
        $driver = new Driver($this->db);
        $driver->setDriver('uc', 'mysql','voicetech');
        $dateNow = new \DateTime('now', $this->timeZone);
        $items = $db->connection($driver->getConfig())->table('phone_cdr')
            ->where([
                ['end','>=', $this->getDate()->format('Y-m-d 00:00:00')],
                ['end','<=', $dateNow->format('Y-m-d H:i:s')],
                ['disposition','=','ANSWERED']
            ])
            ->orderBy('calldate', 'DESC')
            ->paginate($count, page: $page);
        return $items->items();
    }

    public function download(): \DateTimeInterface
    {
        $scp = new UcScp($this->server, 'audio');
        $items = $this->crawlingPages();
        if(empty($items->current())) {
            return $this->getDate();
        }
        $date = $items->current()->calldate;
        foreach ($items as $item) {
            if($item->soundFile != "") {
                Log::info(json_encode($item, JSON_PRETTY_PRINT));
                $tempName = explode("/", $item->soundFile)[1];
                $wav = "$tempName.wav";
                $mp3 = "$tempName.mp3";
                unset($tempName);
                if(!file_exists("/var/www/storage/audio/".$wav) && !file_exists("/var/www/storage/audio/".$mp3)) {
                    $path = self::$path.$item->soundFile.".wav";
                    $scp->setPathDownload($path);
                    $scp->download();
                    Artisan::call('file', [
                        'connections' => serialize($scp),
                        'item' => $item,
                        'type' => "Uc"
                    ]);
                }
            }
        }
        return new \DateTime($date, $this->timeZone);
    }
}
