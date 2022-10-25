<?php

namespace App\Console\Commands;

use App\Jobs\DownloadJob;
use App\Models\Connections;
use Illuminate\Console\Command;

class Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download';

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
        $connections = Connections::where('power', '=', true)->get();
        app('queue');
        $queueDb = config("queue.connections.database");
        $queueDb['queue'] = 'download';
        $queueDb['retry_after'] = '10100';
        config(["queue.connections.database" => $queueDb]);
        foreach ($connections as $connect)
        {
            try {
                // Создание instance по полю name из таблицы connections в db connection
                // Поле соответвует названию класса в app/Services/Downloading
//                dispatch(new DownloadJob($connect['name'], $connect['id']))->onConnection('database')->onQueue('download');
                (new DownloadJob($connect['name'], $connect['id']))->handle();
            } catch (\Throwable $exception) {
                dump($exception->getMessage(),'File: '.$exception->getFile(),'Line: ', $exception->getLine());
            }
        }

        return 0;
    }


}
