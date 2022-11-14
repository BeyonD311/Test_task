<?php

namespace App\Jobs;
use App\Interfaces\Host;
use App\Models\Connections;
use App\Services\Factory\ConnectionFactory;
use App\Models\LastUpdate;
use App\Services\Query\Asterisk;
use App\Services\Query\Cisco;
use App\Services\Query\ContextQuery;
use Illuminate\Support\Facades\Log;

class DownloadJob extends Job
{
    protected int $id;
    protected string $data;
    protected $lastUpdate;

    const AUDIO_PATH = "/var/www/storage/audio/";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $data = "")
    {
        $this->id = $id;
        $this->data = $data;
        $this->lastUpdate = null;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            app('db');
            $dto = Connections::infoFromConnection($this->id);
            $nameClass = ucfirst($dto->name);
            $queryClass = "App\Services\Query\\$nameClass";
            if(!class_exists($queryClass)) {
                throw new \Exception("Query Class not found $queryClass");
            }
            /**
             * Получение последней даты звонка и текущей даты
             */
            $lastUpdate = LastUpdate::getLastUpdate($dto->id)->modify("-2 hours");
            $lastUpdateMS = $lastUpdate->getTimestamp();
            $currentDate = $this->currentDate();
            // Создание выборки
            $queryContext = new ContextQuery();
            $connection = ConnectionFactory::getInstance($dto);
            $queryClass = new $queryClass;
            $queryContext->setContext($queryClass, $connection);
            $queryContext->setOptions();
            $download = new \App\Services\Downloading\DownloadFile();
            /**
             * @var \App\Services\DTO\File $item
             */
            foreach ($queryContext->getItems($lastUpdate->format("Y-m-d H:i:s"), $currentDate->format("Y-m-d H:i:s")) as $item) {
                if($this->checkFile($item->outputName)) {
                    continue;
                }
                $download->setFile($item);
                $download->download();
                $calldate = strtotime($item->calldate);
                if($calldate > $lastUpdateMS) {
                    $lastUpdateMS = $calldate;
                }
            }
//            LastUpdate::setLastUpdate($dto->id, date("Y-m-d H:i:s", $lastUpdateMS));
        } catch (\Throwable $exception) {
            $log = sprintf("Message: %s; \n Line: %d; \n File: %s",
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getFile()
            );
            Log::error($log);
            $this->fail($exception);
        }
    }

    private function currentDate(): \DateTime
    {
        $timeZone = new \DateTimeZone('Europe/Moscow');
        return new \DateTime("now", $timeZone);
    }

    /**
     * проверка файла на существование
     * @param string $name
     * @return bool
     */
    private function checkFile(string $name): bool
    {
        $name = preg_replace("/\.[a-z0-9]*$/", "", $name);
        $wav = $name.".wav";
        $mp3 = $name.".mp3";
        if(file_exists(static::AUDIO_PATH.$wav)) {
            return true;
        }
        if(file_exists(static::AUDIO_PATH.$mp3)) {
            return true;
        }
        return false;
    }
}
