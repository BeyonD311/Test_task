<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

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
        try {
            $context = stream_context_create($this->context);
            $getFile = file_get_contents($this->item['urls']['wavUrl'], context: $context);
            $fileName = md5($this->item['urls']['wavUrl']);
            $path = '/var/www/storage/audio/'.$fileName.".wav";
            file_put_contents($path, print_r($getFile, true));
            $this->saveFileInfo();
        } catch (\Throwable $exception) {
            Log::error(json_encode($exception, JSON_PRETTY_PRINT));
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
        file_put_contents('/var/www/storage/callInfo/'.md5($this->item['urls']['wavUrl']).".json", print_r(json_encode($result, JSON_PRETTY_PRINT), true));
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
