<?php

namespace App\Jobs;

use App\Services\Connections\Scp;
use \Illuminate\Support\Facades\Log;

/**
 * Worker для загрузки файлов c Asterisk
 */

class Uc extends Job
{
    protected Scp $scp;
    protected $item;

    public function __construct($item, $scp)
    {
        $this->scp = unserialize($scp);
        $this->item = $item;
    }

    public function handle()
    {
        try {
            $this->scp->download();
            $this->saveFileInfo($this->item);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            $this->fail($exception);
        } finally {
            unset($this->scp);
            gc_collect_cycles();
        }
    }

    private function saveFileInfo($item)
    {
        $name = preg_replace("/\.[0-9a-z]+$/", "", $item->soundFile);
        $result = [
            "service" => 'uc',
            "calldate" => $item->calldate,
            "src" => $item->src,
            "dst" => $item->callee,
            "duration" => $item->duration,
            "uniqueid" => $item->uniqueid,
            "did" => $item->uniqueid
        ];
        file_put_contents("/var/www/storage/callInfo/$name.json", print_r(json_encode($result, JSON_PRETTY_PRINT), true));
    }
}
