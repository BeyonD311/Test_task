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
        $services = Connections::where('power', '=', true)->get();
        $connections = [];
        foreach ($services as $service) {
            $res = $service->toArray();
            $res['dbServer'] = [];
            $res['server'] = [];
            $db = $service->databaseConnection()->first();
            $server = $service->serverConnection()->first();
            if(isset($db)) {
                $res['dbServer'] = $db->toArray();
            }
            if(isset($server)) {
                $res['server'] = $server->toArray();
            }
            $connections[] = $res;
            unset($server, $database);
        }
        app('queue');
        $queueDb = config("queue.connections.database");
        $queueDb['queue'] = 'download';
        config(["queue.connections.database" => $queueDb]);

        foreach ($connections as $connect)
        {
            try {
                // Создание instance по полю name из таблицы connections в db connection
                // Поле соответвует названию класса в app/Services/DataService
                dispatch(new DownloadJob($connect['name'], $connect['server'], $connect['dbServer']))->onConnection('database')->onQueue('download');
            } catch (\Throwable $exception) {
                dump($exception->getMessage(),'File: '.$exception->getFile(),'Line: ', $exception->getLine());
            }
        }

        return 0;
    }


}
