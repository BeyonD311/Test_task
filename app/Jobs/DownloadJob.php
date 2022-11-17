<?php

namespace App\Jobs;
use App\Models\Connections;
use App\Services\FacadeConnection;
use App\Models\LastUpdate;
use App\Services\Query\ContextQuery;
use Illuminate\Support\Facades\Log;

class DownloadJob extends Job
{
    protected int $id;
    protected array $options;
    private \DateTimeZone $timeZone;
    public $timeout = 10100;

    const AUDIO_PATH = "/var/www/storage/audio/";

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $options = [])
    {
        $this->id = $id;
        $this->options = $options;
        $this->timeZone = new \DateTimeZone('Europe/Moscow');
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
            if(empty($this->options)) {
                $this->regularDownload($dto);
            } else {
                $this->longDownload($dto);
            }
            /**
             * Получение последней даты звонка и текущей даты
             */
        } catch (\Throwable $exception) {
            $log = sprintf("Message: %s; \n Line: %d; \n File: %s",
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getFile()
            );
            Log::error($log);
            $this->fail($exception);
        } finally {
            gc_collect_cycles();
            gc_mem_caches();
        }
    }

    private function currentDate(): \DateTime
    {
        return new \DateTime("now", $this->timeZone);
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

    /**
     * Для загрузки файлов каждые пол часа основная очередь
     * @param $dto
     * @throws \App\Exceptions\Connection
     * @throws \ReflectionException
     * @throws \Throwable
     */
    private function regularDownload($dto)
    {
        $lastUpdate = LastUpdate::getLastUpdate($dto->id)->modify("-2 hours");
        $lastUpdateMS = $lastUpdate->getTimestamp();
        $currentDate = $this->currentDate();
        // Создание выборки
        $queryContext = $this->makeQueryContext($dto);
        $download = FacadeConnection::makeDownload();
        /**
         * @var \App\Services\DTO\File $item
         */
        $items = $queryContext->getItems($lastUpdate->format("Y-m-d H:i:s"), $currentDate->format("Y-m-d H:i:s"));
        foreach ($items as $item) {
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
//        LastUpdate::setLastUpdate($dto->id, date("Y-m-d H:i:s", $lastUpdateMS));
    }

    /**
     * Дополнительная очередь для загрузки недостающих файлов в отрыве от сновной очереди
     * @param $dto
     */
    private function longDownload($dto)
    {
        // Создание выборки
        $queryContext = $this->makeQueryContext($dto);
        $download = FacadeConnection::makeDownload();
        /**
         * @var \App\Services\DTO\File $item
         */
        $items = $queryContext->getItems($this->options['date_from']->format("Y-m-d H:i:s"), $this->options['date_to']->format("Y-m-d H:i:s"));
        foreach ($items as $item) {
            if($this->checkFile($item->outputName)) {
                continue;
            }
            $item->queue = $this->options['queue'];
            $download->setFile($item);
            $download->download();
        }
    }

    /**
     * @param $dto
     * @return ContextQuery
     * @throws \App\Exceptions\Connection
     * @throws \ReflectionException
     */
    private function makeQueryContext($dto): ContextQuery
    {
        // Создание выборки
        $queryClass = FacadeConnection::getQueryInstance($dto);
        $connection = FacadeConnection::getConnection($dto);
        return FacadeConnection::makeQueryContext($queryClass, $connection);
    }
}
