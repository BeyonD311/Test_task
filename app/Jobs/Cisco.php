<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;
use App\Models\Files;

class Cisco extends Job
{
    protected $context;
    protected $item;

    public function __construct($item, $context)
    {
        $this->context = $context;
        $this->item = $item;
    }

    public function handle()
    {
        $name = md5($this->item['urls']['wavUrl'])."-".$this->item['connection_id'].".wav";
        $files = [
            "name" => $name,
            "call_at" => date("Y-m-d H:i:s", ($this->item["sessionStartDate"] / 1000)),
            "connections_id" => $this->item['connection_id']
        ];
        try {
            $getFile = file_get_contents($this->item['urls']['wavUrl'], context: stream_context_create($this->context));
            $path = '/var/www/storage/audio/'.$name;
            file_put_contents($path, print_r($getFile, true));
            $files["exception"] = "empty";
            $files["load_at"] = date("Y-m-d H:i:s");
            $this->saveFileInfo();
        } catch (\Throwable $exception) {
            $files["exception"] = $exception;
            Log::error(json_encode($exception, JSON_PRETTY_PRINT));
        } finally {
            $file = Files::where("name", "=", $name)->first();
            if(is_null($file)) {
                Files::create($files);
            }
        }

    }

    private function saveFileInfo()
    {
        $result = [
            'service' => 'cisco',
            'calldate' => date('Y-m-d H:i:s', ($this->item["sessionStartDate"] / 1000)),
            'duration' => round($this->item['duration'] / 1000),
            'uniqueid' => $this->item['sessionId'],
            'did' => round($this->item['duration'] / 1000)
        ];
        $result = array_merge($result, $this->generatePhone($this->item['tracks']));
        file_put_contents('/var/www/storage/callInfo/'.md5($this->item['urls']['wavUrl'])."-".$this->item['connection_id'].".json", print_r(json_encode($result, JSON_PRETTY_PRINT), true));
    }

    private function generatePhone(array $tracks): array
    {
        $result = [];
        /**
         * Индекс 0 - куда звонит
         * Индекс 1 - кто звонит
         */
        if(count($tracks) > 1) {
            $result["src"] = $tracks[1]["participants"][0]['deviceRef'];
            $result["dst"] = $tracks[0]["participants"][0]['deviceRef'];
        } else {
            $result["src"] = $tracks[0]["participants"][0]['deviceRef'];
            $result["dst"] = null;
        }
        return $result;
    }

}
