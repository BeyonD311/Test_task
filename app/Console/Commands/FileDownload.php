<?php

namespace App\Console\Commands;

use App\Jobs\DownloadFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FileDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file {item}';

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
        try {
            app('queue');
            $file = unserialize($this->argument('item'));
            $redisConf = config("queue.connections.redis");
            $redisConf['queue'] = $file->queue;
            $redisConf['retry_after'] = 9999;
            config(["queue.connections.redis" => $redisConf]);
            $handlerDownload = new DownloadFile($this->argument('item'));
            dispatch($handlerDownload)->onConnection('redis')->onQueue($file->queue);
        } catch (\Throwable $exception) {
            Log::error(sprintf("Message: %s \n; File: %s \n; Line: %s \n", $exception->getMessage(), $exception->getFile(), $exception->getLine()));
            throw $exception;
        }
        return 0;
    }
}
