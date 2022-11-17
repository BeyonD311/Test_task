<?php

namespace App\Jobs;

use App\Models\CallInfo;
use App\Models\Files;
use App\Services\DTO\File;
use Exception;
use \Illuminate\Support\Facades\Log;

class DownloadFile extends Job
{
    protected File $file;
    public $timeout = 10100;

    public function __construct(string $item)
    {
        $this->file = unserialize($item);
    }

    public function handle()
    {
        $exception = "empty";
        try {
            /**@var \App\Services\Protocols\IProtocols $protocol*/
            $protocolClass = "App\Services\Downloading\Type\\".ucfirst($this->file->downloadMethod);
            if(!class_exists($protocolClass)) {
                throw new Exception("Протокол загрузки файла не обнаружен");
            }
            if(!file_exists("/var/www/storage/audio/{$this->file->outputName}")) {
                $protocol = new $protocolClass($this->file);
                $protocol->execute();
            }
        } catch (\Throwable $throwable) {
            $exception = $throwable->getMessage();
            $log = sprintf("Message: %s; \n Line: %d; \n File: %s",
                $throwable->getMessage(),
                $throwable->getFile(),
                $throwable->getFile()
            );
            Log::error($log);
        } finally {
            if(file_exists("/var/www/storage/audio/{$this->file->outputName}")) {
                $exception = "empty";
            }
            $fileCreate = Files::where([
                ["name", "=", $this->file->outputName]
            ])->first();
            if(is_null($fileCreate)) {
                $fileCreate = Files::create([
                    "name" => $this->file->outputName,
                    "connections_id" => $this->file->connection_id,
                    "exception" => $exception,
                    "call_at" => $this->file->calldate
                ]);
            } else {
                $fileCreate->exception = $exception;
                $fileCreate->save();
            }

            if($exception === "empty") {
                CallInfo::create([
                    "file_id" => $fileCreate->id,
                    "src" => $this->file->src,
                    "dst" => $this->file->dst,
                    "duration" => $this->file->duration
                ]);
                $this->createCallInfo();
            }
        }
        return 0;
    }

    private function createCallInfo(): void
    {
        $callInfo = [
            "service" => $this->file->connection_name,
            "calldate" => $this->file->calldate,
            "duration" => $this->file->duration,
            "src" => $this->file->src,
            "dst" => $this->file->dst,
            "uniqueid" => $this->file->uniqueid,
            "connection_id" => $this->file->connection_id
        ];
        $name = preg_replace("/\.[a-z0-9]*$/", ".json", $this->file->outputName);
        file_put_contents("/var/www/storage/callInfo/$name", json_encode($callInfo, JSON_PRETTY_PRINT));
    }
}
