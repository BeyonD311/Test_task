<?php

namespace App\Jobs;

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
        $this->outputName = $this->buildOutputName();
    }

    public function handle()
    {
        $filesOptions = [
            "name" => $this->outputName,
            "connections_id" => $this->scp->getServer()->getConnectionId(),
            "call_at" => $this->item->calldate
        ];
        try {
            $path = "/var/www/storage/audio";
            $this->scp->download();
            File::rename($path."/".$this->item->recordingfile, $path."/".$this->outputName);
            $this->saveFileInfo($this->item);
            $filesOptions["exception"] = "empty";
            $filesOptions["load_at"] = date("Y-m-d H:i:s");
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            $filesOptions["exception"] = $exception;
            $this->fail($exception);
        } finally {
            $file = Files::where("name", "=", $this->outputName)->first();
            if(is_null($file)) {
                Files::create($filesOptions);
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
            "calldate" => $item->calldate,
            "src" => $item->src,
            "dst" => $item->dst,
            "duration" => $item->duration,
            "uniqueid" => $item->uniqueid,
            "did" => $item->did
        ];
        file_put_contents("/var/www/storage/callInfo/$name.json", print_r(json_encode($result, JSON_PRETTY_PRINT), true));
    }

    private function buildOutputName(): string
    {
        $name = explode('.', $this->item->recordingfile);
        $expansion = array_pop($name);
        $connectionId = $this->scp->getServer()->getConnectionId();
        $name[] = array_pop($name)."-$connectionId";
        $name[] = $expansion;
        $name = implode('.', $name);
        unset($expansion, $connectionId);
        return  $name;
    }

}
