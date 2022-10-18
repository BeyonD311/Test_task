<?php

namespace App\Jobs;
use App\Interfaces\Host;
use App\Models\Connections;
use Illuminate\Support\Facades\Log;

class DownloadJob extends Job
{
    protected string $name;
    protected int $id;
    protected Host $database;
    protected Host $server;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $id)
    {
        $this->name = $name;
        $this->id = $id;
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
            $instance = match (strtolower($this->name)) {
                'asterisk' => new $instance($connect['server_connection'], $connect['database_connection']),
                'cisco' => new $instance($connect['server_connection']),
                'uc' => new $instance($connect['server_connection'], $connect['database_connection'])
            };
             $instance->download();
        } catch (\Throwable $exception) {
            Log::error(sprintf("Message: %s; \n Line: %d; \n File: %s",
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getFile()
            ));
            $this->fail($exception);
        }
    }
}
