<?php

namespace App\Console\Commands;

use App\Models\Files;

class UpdateCallInfo extends \Illuminate\Console\Command
{
    protected $signature = "updateCallInfo {connection}";

    protected $description = "Разовый запуск для обновления callinfo";

    public function handle()
    {
        foreach ($this->getFiles() as $file) {
            $file = '/var/www/storage/callInfo/'.preg_replace("/\.[a-z0-9]*$/", ".json", $file->name);
            if(file_exists($file)) {
                $json = json_decode(file_get_contents($file), true);
                if(is_array($json)) {
                    $json['connection_id'] = $this->argument("connection");
                    file_put_contents($file, print_r(json_encode($json, JSON_PRETTY_PRINT), true));
                } else {
                    unlink($file);
                }
            }
        }
        return 0;
    }

    /**
     * @param int $page
     * @param int $size
     */
    private function getFiles(): \Generator
    {
        $page = 1;
        $size = 1000;
        while(true) {
            $files = Files::where("connections_id", "=", $this->argument("connection"))
                ->paginate($size, page: $page);
            $files = $files->items();
            if(empty($files)) {
                break;
            }
            foreach ($files as $file) {
                yield $file;
            }
            $page++;
        }
    }
}
