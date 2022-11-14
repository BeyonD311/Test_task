<?php

namespace App\Jobs;

use App\Exceptions\Connection;
use App\Models\CallInfo;
use App\Models\Files;
use App\Services\File;
use App\Services\Protocols\IProtocols;
use \Illuminate\Support\Facades\Log;

class DownloadFile extends Job
{
    protected IProtocols $protocol;
    public function __construct(string $protocol, string $item, string $connection, protected string $type)
    {
        $this->setProtocol($protocol,$item,$connection);
    }

    public function handle()
    {
        $exception = "empty";
        try {
            $this->protocol->execute();
        } catch (\Throwable $throwable) {
            $exception = $throwable;
            Log::error("Message: {$throwable->getMessage()} \n Line: {$throwable->getLine()}");
        } finally {
            $file = Files::where([
                ["name", "=", $this->protocol->getFile()->outputName]
            ])->first();
            if(is_null($file)) {
                $incomingFile = $this->protocol->getFile();
                $file = Files::create([
                    "name" => $incomingFile->outputName,
                    "connections_id" => $this->protocol->getServer()->getConnectionId(),
                    "exception" => $exception,
                    "call_at" => $incomingFile->calldate
                ]);
                $this->createCallInfo($incomingFile);
                CallInfo::create([
                    "file_id" => $file->id,
                    "src" => $incomingFile->src,
                    "dst" => $incomingFile->dst,
                    "duration" => $incomingFile->duration
                ]);
            } else {
                $file->exception = $exception;
                $file->save();
                throw $exception;
            }
        }
        return 0;
    }

    /**
     * Создает файл CallInfo
     * @param File $file
     */
    private function createCallInfo(File $file): void
    {
        $callInfo = [
            "service" => $this->type,
            "calldate" => $file->calldate,
            "duration" => $file->duration,
            "src" => $file->src,
            "dst" => $file->dst,
            "uniqueid" => $file->uniqueid,
            "connection_id" => $this->protocol->getServer()->getConnectionId()
        ];
        $name = preg_replace("/\.[a-z0-9]*$/", ".json", $file->outputName);
        file_put_contents("/var/www/storage/callInfo/$name", json_encode($callInfo, JSON_PRETTY_PRINT));
    }

    /**
     * @param string $protocol протокол загрузки файлов App\Services\Downloading\Type
     * @param string $item сериализованный FileDTO
     * @param string $connection опции подключения к серверу App\Services\Connections\Options\Server
     * @throws Connection
     */
    private function setProtocol(string $protocol, string $item, string $connection): void
    {
        if(!class_exists($protocol)) {
            throw new Connection("протокол загрузки не найден \n протоколы загрузки расположенны в App\Services\Downloading\Type");
        }
        $connection = unserialize($connection);
        $item = unserialize($item);
        $this->protocol = new $protocol($connection);
        if($this->protocol instanceof \App\Services\Downloading\Type\Http) {
            $this->protocol->setMethod("GET");
            $this->protocol->setUri($item->file);
            $this->protocol->setBody([
                'headers' => [
                    'Authorization' => 'Basic '.base64_encode($connection->getLogin().':'.$connection->getPass()),
                ]
            ]);
        }
        $this->protocol->setFile($item);
    }
}
