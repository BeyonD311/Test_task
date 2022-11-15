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
    protected $signature = 'resumingFiles {date_from?} {date_to?} {queue?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запуск очереди с параметрами даты {date_from?} {date_to?} {--queue=название очереди в которой должны загружаться файлы}';

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
                $options = array_merge($this->makeParamDate(), $this->makeQueue());
                dispatch(new DownloadJob($connect['id'], $options))->onConnection('database')->onQueue('download');
            } catch (\Throwable $exception) {
                dump($exception->getMessage(),'File: '.$exception->getFile(),'Line: ', $exception->getLine());
            }
        }

        return 0;
    }

    /**
     * Формирует дату в зависимости от аргументов
     * @return array
     * @throws \Exception
     */
    private function makeParamDate(): array
    {
        $timeZone = new \DateTimeZone('Europe/Moscow');
        if(is_null($this->argument('date_from'))) {
            $options['date_from'] = new \DateTime(date("Y-m-d 00:00:00"),$timeZone);
            $options['date_from']->modify("-1 day");
        } else {
            $options['date_from'] = new \DateTime($this->argument('date_from'));
        }
        if(is_null($this->argument('date_to'))) {
            $options['date_to'] = new \DateTime(date("Y-m-d 00:00:00"),$timeZone);
        } else {
            $options['date_to'] = new \DateTime($this->argument('date_to'));
        }

        if($options['date_from']->getTimestamp() > $options['date_to']->getTimestamp()) {
            throw new \Exception("аргумент date_from не может быть больше чем date_to");
        }

        return $options;
    }

    private function makeQueue(): array
    {
        if(is_null($this->argument('queue'))) {
            $options["queue"] = "longDownload";
        } else {
            $options["queue"] = $this->argument('queue');
        }
        return $options;
    }
}
