<?php

namespace App\Console\Commands;

use App\Services\Downloading\Asterisk;
use App\Services\Connections\Server;

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
        /*$db = new \App\Services\Hosts\DB;
        $db->setHost('10.3.0.10')
            ->setPort('3306')
            ->setLogin('user')
            ->setPass('P@ssw0rd1')
            ->setId(1);

        $server = new Server();
        $server->setHost("10.3.0.10")
            ->setLogin('root')
            ->setPass('!DLP$tend%');
        $asterisk = new Asterisk($server,$db);
        $asterisk->download();*/
        return 0;
    }
}
