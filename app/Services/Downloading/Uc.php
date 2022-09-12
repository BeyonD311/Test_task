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
        $date = date('Y-m-d H:i:s', $this->getInstanceLastUpdate()->getTimestamp($this->db->getId()));
        $timeZone = new \DateTimeZone("Europe/Moscow");
        $dateNow = new \DateTime('now', $timeZone);
        $items = $db->connection($driver->getConfig())->table('phone_cdr')
            ->where([
                ['end','>=', date("Y-m-d 00:00:00", strtotime($date))],
                ['end','<=', $dateNow->format('Y-m-d H:i:s')],
                ['disposition','=','ANSWERED']
            ])
            ->orderBy('calldate', 'DESC')
            ->paginate($count, page: $page);
        return $items->items();
    }

    public function download()
    {
        $scp = new UcScp($this->server, 'audio');
        $items = $this->crawlingPages();
        if(!empty($items->current())) {
            $this->getInstanceLastUpdate()->updateOrCreate($this->db->getId(), $items->current()->calldate);
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
        }
    }
}
