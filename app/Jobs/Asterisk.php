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
    }

    public function handle()
    {
        $filesOptions = [
            "connections_id" => $this->scp->getServer()->getConnectionId(),
            "call_at" => $this->item->calldate
        ];
        try {
            $file = $this->scp->download();
            $fileName = explode("/", $file);
            $this->outputName = array_pop($fileName);
            array_pop($fileName);
            $fileName[] = "audio";
            $fileName[] = $this->outputName;
            copy($file, implode("/", $fileName));
            unlink($file);
            $filesOptions["name"] = $this->outputName;
            $this->saveFileInfo($this->item);
            $filesOptions["exception"] = "empty";
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            $filesOptions["exception"] = $exception;
            $this->fail($exception);
        } finally {
            $file = Files::where("name", "=", $this->outputName)->first();
            if(is_null($file)) {
                $file = Files::create($filesOptions);
            } else {
                $file->exception = $filesOptions["exception"];
                $file->save();
            }
            CallInfo::create([
                "file_id" => $file->id,
                "src" => $this->item->src,
                "dst" => $this->item->dst,
                "duration" => $this->item->duration
            ]);
            unset($this->scp, $file);
            gc_collect_cycles();
        }
    }

    private function saveFileInfo($item)
    {
        $name = preg_replace("/\.[0-9a-z]+$/", "", $this->outputName);
        $result = [
            "service" => 'asterisk',
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
