<?php

namespace App\Console\Commands;

use App\Jobs\Asterisk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FileDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'file {connections} {item} {type}';

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
            $redisConf = config("queue.connections.redis");
            $redisConf['retry_after'] = '9000';
            $redisConf['block_for'] = 5;
            $redisConf['queue'] = $this->argument('type');
            config(["queue.connections.redis" => $redisConf]);
            $JobClass = "App\Jobs\\".$this->argument('type');
            dispatch(new $JobClass($this->argument('item'), $this->argument('connections')))->onConnection('redis')->onQueue($this->argument('type'));
        } catch (\Throwable $exception) {
            Log::info(json_encode($exception, JSON_PRETTY_PRINT));
        }
        return 0;
    }
}
