<?php

namespace App\Console\Commands;

use App\Models\CallInfo;
use App\Models\Connections;
use App\Models\Files;
use App\Services\Connections\Asterisk;

class FillFiles extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app("db");
        $audioDir = "/var/www/storage/audio";
        $files = array_slice(scandir($audioDir), 3);
        $connections = Connections::where([["power", "=", true]])->get()->map(function ($connection) {
            return Connections::infoFromConnection($connection->id);
        })->toArray();
        $result = $this->splitFiles($files, function ($items)use($audioDir) {
            $result = [];
            $now = strtotime(date('Y-m-d 00:00:00', time()));
            foreach ($items as $file) {
                $splitFile = explode(".", $file);
                $expansion = array_pop($splitFile);
                if($this->checkAsterisk($file)) {
                    if(fileatime($audioDir."/$file") >= $now) {
                        $expansion = "wav";
                    } else {
                        $expansion = "mp3";
                    }
                }
                $name = preg_replace("/\.?-\d*$/","",implode(".",$splitFile)).".$expansion";
                $result[$name] = $file;
            }
            return $result;
        });
        foreach ($result as $sliceFiles) {
            foreach ($connections as $connection) {
                if($connection['name'] === "asterisk") {
                    $asteriskConnect = new Asterisk($connection['database_connection']);
                    $this->asterisk($sliceFiles, $asteriskConnect, $connection['id']);
                } else {
                    $this->cisco($sliceFiles,$connection['id']);
                }
            }
        }

        return 0;
    }

    private function splitFiles($dirFiles, $fn = null): \Generator
    {
        for ($i = 0; $i < ceil(count($dirFiles) / 100); $i++) {
            $files = array_slice($dirFiles, $i * 100, 100);
            if($fn !== null) {
                $result = call_user_func($fn, $files);
            } else {
                $result = $files;
            }
            if(!empty($result)) {
                yield $result;
            }
        }
    }

    private function asterisk(&$files, Asterisk $asterisk, $id): void
    {
        $items = $asterisk->connection()->whereIn("recordingfile", array_keys($files))
            ->get();
        foreach ($items as $item) {
            $file = Files::create([
                "name" => $files[$item->recordingfile],
                "connections_id" => $id,
                "call_at" => $item->calldate,
                "exception" => "empty"
            ]);
            CallInfo::create([
                "src" => $item->src,
                "dst" => $item->dst,
                "file_id" => $file->id,
                "duration" => $item->duration
            ]);
        }
    }
    private function cisco(&$files, $id): void
    {
        $callInfoDir = "/var/www/storage/callInfo/";
        $result = [];

        foreach ($files as $key => $item) {
            if(!$this->checkAsterisk($key)) {
                $result[] = $item;
                unset($files[$key]);
            }
        }
        foreach ($result as $file) {
            $json = explode(".", $file);
            array_pop($json);
            $json[] = "json";
            $json = $callInfoDir.implode(".", $json);
            if(file_exists($json)) {
                $callInfo = json_decode(file_get_contents($json), true);
                if($callInfo["dst"] == null) {
                    $callInfo["dst"] = "empty";
                    $file = Files::create([
                        "name" => $file,
                        "connections_id" => $id,
                        "call_at" => $callInfo['calldate'],
                        "exception" => "empty"
                    ]);
                    CallInfo::create([
                        "src" => $callInfo['src'],
                        "dst" => $callInfo['dst'],
                        "file_id" => $file->id,
                        "duration" => $callInfo['duration']
                    ]);
                }
            }
        }
    }

    private function checkAsterisk(string $fileName): bool
    {
        return !(preg_match("/(\w*-)?(?:\d+-\d+)-(?:\d*\.)*/", $fileName) === 0);
    }
}
