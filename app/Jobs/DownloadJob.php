<?php

namespace App\Jobs;
use App\Interfaces\Host;
use App\Models\Connections;
use App\Services\LastUpdate;
use Illuminate\Support\Facades\Log;

class DownloadJob extends Job
{
    protected string $name;
    protected int $id;
    protected string $data;
    protected Host $database;
    protected Host $server;
    protected $lastUpdate;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $id, $data = "")
    {
        $this->name = $name;
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
            $nameDownloading = ucfirst(strtolower($this->name));
            $instance = "App\Services\Downloading\\$nameDownloading";
            $connect = Connections::infoFromConnection($this->id);
            /**@var \App\Services\Downloading\DataService $instance*/
            $instance = match (strtolower($this->name)) {
                'asterisk' => new $instance($connect['server_connection'], $connect['database_connection']),
                'cisco' => new $instance($connect['server_connection']),
                'uc' => new $instance($connect['server_connection'], $connect['database_connection'])
            };
            $connect = $connect['database_connection'] === null ? $connect['server_connection'] : $connect['database_connection'];
            $dateNow = $this->createDate($connect, $instance);
            $instance->setDate($dateNow);
            $dataLastUpdate = $instance->download();
            $this->instanceLustUpdate($instance)->updateOrCreate($connect->getId(), $dataLastUpdate->format("Y-m-d H:i:s"));
        } catch (\Throwable $exception) {
            Log::error(sprintf("Message: %s; \n Line: %d; \n File: %s",
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getFile()
            ));
            $this->fail($exception);
        }
    }

    private function createDate($connect, $instance): \DateTimeInterface
    {
        $timeZone = new \DateTimeZone('Europe/Moscow');
        if($this->data !== "") {
            return new \DateTime($this->data, $timeZone);
        }
        return new \DateTime($this->instanceLustUpdate($instance)->getStringDate($connect->getId()), $timeZone);
    }

    /**
     * @param $instance
     * @return LastUpdate
     */
    private function instanceLustUpdate($instance): LastUpdate
    {
        if(is_null($this->lastUpdate)) {
            $this->lastUpdate = new LastUpdate($instance->getConnectionType());
        }
        return $this->lastUpdate;
    }
}
