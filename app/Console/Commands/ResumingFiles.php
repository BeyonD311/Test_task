<?php

namespace App\Console\Commands;

use App\Jobs\DownloadJob;
use App\Models\Connections;

class ResumingFiles extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resumingFiles';

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
                $timeZone = new \DateTimeZone('Europe/Moscow');
                $data = new \DateTime(date("Y-m-d H:i:s", strtotime(date("Y-m-d 00:00:00")) - 86400),$timeZone);
                dispatch(new DownloadJob($connect['name'], $connect['id'], $data->format("Y-m-d H:i:s")))->onConnection('database')->onQueue('download');
            } catch (\Throwable $exception) {
                dump($exception->getMessage(),'File: '.$exception->getFile(),'Line: ', $exception->getLine());
            }
        }

        return 0;
    }
}
