<?php

namespace App\Jobs;

use App\Models\CallInfo;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Files;

class Cisco extends Job
{
    protected $context;
    protected $item;
    protected $options;

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
            $client = new Client([
                'verify' => false,
                'cookies' => true
            ]);
            $file = $client->request('GET', $this->item['urls']['wavUrl'], $this->context);
            file_put_contents('/var/www/storage/temp/'.$name, print_r($file->getBody()->getContents(), true));
            $files["exception"] = "empty";
            $this->options = $this->saveFileInfo();
            copy('/var/www/storage/temp/'.$name, '/var/www/storage/audio/'.$name);
            unlink('/var/www/storage/temp/'.$name);
        } catch (\Throwable $exception) {
            $files["exception"] = $exception;
            Log::error(json_encode($exception->getMessage(), JSON_PRETTY_PRINT));
        } finally {
            $file = Files::where("name", "=", $name)->first();
            if(is_null($file)) {
                $file = Files::create($files);
                $info = $this->generatePhone($this->item['tracks']);
                CallInfo::create([
                    "file_id" => $file->id,
                    "src" => $info['src'],
                    "dst" => $info['dst'] == null ? "empty":$info['dst'],
                    "duration" => round($this->item['duration'] / 1000)
                ]);
            }
        }

    }

    private function saveFileInfo(): array
    {
        $result = [
            'service' => 'cisco',
            'connection_id' => $this->item['connection_id'],
            'calldate' => date('Y-m-d H:i:s', ($this->item["sessionStartDate"] / 1000)),
            'duration' => round($this->item['duration'] / 1000),
            'uniqueid' => $this->item['sessionId'],
            'did' => round($this->item['duration'] / 1000)
        ];
        $result = array_merge($result, $this->generatePhone($this->item['tracks']));
        file_put_contents('/var/www/storage/callInfo/'.md5($this->item['urls']['wavUrl'])."-".$this->item['connection_id'].".json", print_r(json_encode($result, JSON_PRETTY_PRINT), true));
        return $result;
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
