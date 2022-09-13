<?php

namespace App\Console\Commands;

use App\Models\Files;
use App\Services\Connections\Options\DB;
use App\Services\Downloading\Asterisk;
use App\Services\Connections\Options\Server;
use Illuminate\Support\Facades\Log;

class Test extends \Illuminate\Console\Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

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
        $audioDir = "/var/www/storage/audio";
        $callInfoDir = "/var/www/storage/callInfo/";
        $readDir = new \DirectoryIterator($audioDir);
        while ($readDir->valid())
        {
            $item = $readDir->current();
            $jsonName = preg_replace("/\.[a-z0-9]*$/", ".json", $item->getFilename());
            if(!file_exists($callInfoDir.$jsonName)) {
                $readDir->next();
                continue;
            }
            $file = Files::where([
                ['name', "=", $item->getFilename()]
            ])->first();
            if($file !== null) {
                $readDir->next();
                continue;
            }
            $info = json_decode(file_get_contents($callInfoDir.$jsonName), true);
            if(!isset($info['service'])) {
                $readDir->next();
                continue;
            }
            if($file === null) {
                $connection_id = $info['service'] === 'asterisk' ? 1 : 2;
                Files::create([
                    'name' => $item->getFilename(),
                    'connections_id' => $connection_id,
                    'exception' => 'empty',
                    'call_at' => $info['calldate'],
                    'load_at' => $info['calldate']
                ]);
            }
            $readDir->next();
        }
        return 0;
    }
}
