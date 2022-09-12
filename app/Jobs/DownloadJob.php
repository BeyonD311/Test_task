<?php

namespace App\Jobs;
use App\Interfaces\Host;
use App\Services\Connections\Options\DB;
use App\Services\Connections\Options\Server;
use Illuminate\Support\Facades\Log;

class DownloadJob extends Job
{
    protected string $name;
    protected Host $database;
    protected Host $server;

    public $timeout = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $server = [], $database = [])
    {
        $this->name = $name;
        if(!empty($server)) {
            $this->server = $this->createServer($server);
        }
        if(!empty($database)) {
            $this->database = $this->databaseServerCreate($database);
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $nameDownloading = ucfirst(strtolower($this->name));
        $instance = "App\Services\Downloading\\$nameDownloading";
        try {
             $instance = match (strtolower($this->name)) {
                'asterisk' => new $instance($this->server, $this->database),
                'cisco' => new $instance($this->server)
             };
             $instance->download();
        } catch (\Throwable $exception) {
            $this->fail($exception);
        }
    }


    private function createServer(array $server): Host
    {
        $serverDto = new Server();
        return $serverDto->setHost($server['host'])
            ->setId($server['id'])
            ->setPort($server['port'])
            ->setLogin($server['login'])
            ->setPass($server['pass'])
            ->setConnectionId($server['connection_id']);
    }

    private function databaseServerCreate(array $database): Host
    {
        $databaseDto = new DB();
        return $databaseDto->setHost($database['host'])
            ->setPort($database['port'])
            ->setLogin($database['login'])
            ->setPass($database['pass'])
            ->setId($database['id'])
            ->setTable($database['table'])
            ->setConnectionId($database['connection_id']);
    }
}
