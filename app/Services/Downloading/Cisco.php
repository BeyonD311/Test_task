<?php

namespace App\Services\Downloading;


use App\Interfaces\ConnectionInterface;
use App\Services\Protocols\Http;
use App\Services\Connections\Options\Server;
use Illuminate\Support\Facades\Artisan;
use App\Exceptions\Connection as ConnectException;
use Illuminate\Support\Facades\Log;

class Cisco extends DataService
{

    protected string $lastUpdateConnection = "server_connection_id";
    protected array $cookie;
    protected ConnectionInterface $connection;

    public function __construct(
        protected Server $server
    )
    {
        $this->connection = new \App\Services\Connections\Cisco($this->server);
        $this->cookie = $this->connection->getOptions();
        parent::__construct();
    }

    public function download(): \DateTimeInterface
    {
        app("db");
        $duration = 0;
        $maxDate = $this->getDate()->getTimestamp();
        $date = new \DateTime('now', $this->timeZone);
        $items = (new \App\Services\Query\Cisco($this->connection))
            ->setPaginate(0, 100)
            ->getItems(date("Y-m-d H:i:s.u", $maxDate), $date->format("Y-m-d H:i:s.u"));
        $maxDate = (int)($maxDate."000");

        foreach ($items as $item) {
            if(empty($item['urls']['wavUrl'])) {
                continue;
            }
            foreach ($item['tracks'] as $track) {
                $duration += $track['trackDuration'];
            }
            $item['duration'] = $duration;
            if($maxDate < $item['sessionStartDate']) {
                $maxDate = $item['sessionStartDate'];
            }
            if(file_exists("/var/www/storage/audio/".md5($item['urls']['wavUrl'])."-".$this->server->getConnectionId().".wav")) {
                continue;
            }
            $item['connection_id'] = $this->server->getConnectionId();
//            $this->fileDownload($item);
        }

        $maxDate /= 1000;

        return new \DateTime(date('Y-m-d H:i:s', $maxDate), $this->timeZone);
    }

    private function fileDownload(array $item)
    {
        $context = [
            'headers' => [
                'Cookie' => 'JSESSIONID='.$this->cookie['JSESSIONID'],
                'Authorization' => 'Basic '.base64_encode($this->server->getLogin().':'.$this->server->getPass()),
            ]
        ];
        Artisan::call('file', [
            'connections' => $context,
            'item' => $item,
            'type' => "Cisco"
        ]);
    }
}
