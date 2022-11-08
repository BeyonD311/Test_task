<?php

namespace App\Services\Downloading;


use App\Interfaces\ConnectionInterface;
use App\Services\Connections\DTO\Server;
use App\Services\Downloading\Type\Http;
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
        $maxDate = $this->getDate()->getTimestamp();
        $date = new \DateTime('now', $this->timeZone);
        $items = (new \App\Services\Query\Cisco($this->connection))
            ->setPaginate(0, 100)
            ->getItems(date("Y-m-d H:i:s.u", $maxDate), $date->format("Y-m-d H:i:s.u"));
        foreach ($items as $item) {
            if($maxDate < strtotime($item->calldate)) {
                $maxDate = strtotime($item->calldate);
            }
            if(file_exists("/var/www/storage/audio/".md5($item->file)."-".$this->server->getConnectionId().".wav")) {
                continue;
            }
            $item->outputName = md5($item->file)."-".$this->server->getConnectionId().".wav";
            $item->{'connection_id'} = $this->server->getConnectionId();
//            $this->fileDownload($item);
        }
        return new \DateTime(date('Y-m-d H:i:s', $maxDate), $this->timeZone);
    }

    private function fileDownload($item)
    {
        Artisan::call('file', [
            'connections' => serialize($this->server),
            'item' => serialize($item),
            'protocol' => Http::class,
            'type' => "Cisco"
        ]);
    }
}
