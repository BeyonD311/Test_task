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
        $this->connection->connection();
        $this->cookie = $this->connection->getOptions();
        parent::__construct();
    }

    public function download()
    {
        app("db");
        $duration = 0;
        $maxDate = (int)$this->getInstanceLastUpdate()->getTimestamp($this->server->getId());
        $timeZone = new \DateTimeZone('Europe/Moscow');
        $date = new \DateTime('now', $timeZone);
        $flagEmpty = false;
        $items = (new \App\Services\Query\Cisco($this->connection))
            ->setPaginate(0, 1000)
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
            $this->fileDownload($item);
        }
        if($flagEmpty === false) {
            $maxDate /= 1000;
            $this->getInstanceLastUpdate()->updateOrCreate($this->server->getId(), date('Y-m-d H:i:s', $maxDate));
        }
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
