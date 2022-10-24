<?php

namespace App\Jobs;

use App\Models\CallInfo;
use App\Models\Files;
use App\Services\Protocols\Scp;
use App\Services\File;
use \Illuminate\Support\Facades\Log;

/**
 * Worker для загрузки файлов c Asterisk
 */

class Asterisk extends Job
{
    protected Scp $scp;
    protected $item;
    protected string $outputName;

    public function __construct($item, $scp)
    {
        $this->scp = unserialize($scp);
        $this->item = $item;
        $this->outputName = $this->scp->generateOutputName();
    }

    public function handle()
    {
        $filesOptions = [
            "connections_id" => $this->scp->getServer()->getConnectionId(),
            "call_at" => $this->item->calldate,
            "name" => $this->outputName
        ];
        try {
            $file = $this->scp->download();
            copy($file, "/var/www/storage/audio/".$filesOptions['name']);
            unlink($file);
            $filesOptions["exception"] = "empty";
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            $filesOptions["exception"] = $exception;
            $this->fail($filesOptions["exception"]);
        } finally {
            $file = Files::where("name", "=", $filesOptions['name'])->first();
            if(is_null($file)) {
                $file = Files::create($filesOptions);
            } else {
                $file->exception = $filesOptions["exception"];
                $file->save();
            }
            if($filesOptions["exception"] == "empty") {
                $this->saveFileInfo($this->item);
                CallInfo::create([
                    "file_id" => $file->id,
                    "src" => $this->item->src,
                    "dst" => $this->item->dst,
                    "duration" => $this->item->duration
                ]);
            }
            unset($this->scp, $file);
            gc_collect_cycles();
        }
    }

    private function saveFileInfo($item)
    {
        $name = preg_replace("/\.[0-9a-z]+$/", "", $this->outputName);
        $result = [
            "service" => 'asterisk',
            'connection_id' => $this->scp->getServer()->getConnectionId(),
            "calldate" => $item->calldate,
            "src" => $item->src,
            "dst" => $item->dst,
            "duration" => $item->duration,
            "uniqueid" => $item->uniqueid,
            "did" => $item->did
        ];
        file_put_contents("/var/www/storage/callInfo/$name.json", print_r(json_encode($result, JSON_PRETTY_PRINT), true));
    }

}
