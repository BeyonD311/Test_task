<?php

namespace App\Services\Downloading;


use App\Interfaces\ConnectionInterface;
use App\Services\Protocols\Http;
use App\Services\Connections\Options\Server;
use Illuminate\Support\Facades\Artisan;
use App\Exceptions\Connection as ConnectException;

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

    public function download()
    {
        $duration = 0;
        $maxDate = $this->getInstanceLastUpdate()->getTimestamp($this->server->getId());
        $flagEmpty = false;
        foreach ($this->getItems($this->connection->connection()) as $item) {
            if(isset($item['isEmpty']) && $item['isEmpty'] === true) {
                $flagEmpty = true;
                break;
            }
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

    private function getItems(Http $http): \Generator
    {
        $lastDate = $this->getInstanceLastUpdate()->getTimestamp($this->server->getId());

        $itemsQuery = $http->send('post', 'queryService/query/getSessions', [
            "json" => [
                "requestParameters" => [
                    [
                        "fieldName" => "sessionState",
                        "fieldConditions" => [
                            [
                                "fieldOperator" => "equals",
                                "fieldValues" => [
                                    "CLOSED_NORMAL"
                                ],
                            ]
                        ],
                        "paramConnector" => "AND"
                    ],
                    [
                        "fieldName" => "sessionStartDate",
                        "fieldConditions" => [
                            [
                                "fieldOperator" => "between",
                                "fieldValues" => [($lastDate + 1) * 1000, (int)(time()."999")]
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        $items = json_decode($itemsQuery->response()->getBody()->getContents(), true);
        if($items['responseCode'] < 2000 && $items['responseCode'] >= 3000) {
            throw new ConnectException($items["responseMessage"], $items['responseCode']);
        }
        if(isset($items['responseBody'])) {
            foreach ( $items['responseBody']['sessions'] as $item) {
                yield $item;
            }
        } else {
            yield ["isEmpty" => true];
        }
    }

    private function fileDownload(array $item)
    {
        $context = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Cookie: JSESSIONID='.$this->cookie['JSESSIONID'],
                    'Authorization: Basic '.base64_encode($this->server->getLogin().':'.$this->server->getPass()),
                    'Content-type: audio/basic'
                ]
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        Artisan::call('file', [
            'connections' => $context,
            'item' => $item,
            'type' => "Cisco"
        ]);
    }
}
