<?php

namespace App\Services\DataService;

use App\Exceptions\Connection;
use App\Services\Connections\Rest;
use App\Services\Hosts\Host;
use Illuminate\Support\Facades\Artisan;

class Cisco extends DataService
{
    private Rest $rest;

    private array $cookie = [
        'JSESSIONID' => NULL
    ];

    protected string $lastUpdateConnection = "server_connection_id";

    public function __construct(
        protected Host $server
    )
    {
        $this->rest = new Rest('https://'.$this->server->getHost().':'.$this->server->getPort().'/ora/');
        parent::__construct();
    }

    public function download()
    {
        $this->sigIn();
        $duration = 0;
        $maxDate = $this->getInstanceLastUpdate()->getTimestamp($this->server->getId());
        foreach ($this->getItems() as $item) {
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

            $this->fileDownload($item);
        }
        $maxDate /= 1000;
        $this->getInstanceLastUpdate()->updateOrCreate($this->server->getId(), date('Y-m-d H:i:s', $maxDate));
    }

    private function sigIn(): void
    {
        $sigIn = $this->rest->send('post', 'authenticationService/authentication/signIn', [
            'json' => [
                "requestParameters" => [
                    'username' => $this->server->getLogin(),
                    'password' => $this->server->getPass()
                ]
            ]
        ]);
        $response = json_decode($sigIn->response()->getBody()->getContents(), true);
        #Cisco code success 2000
        if($response['responseCode'] !== 2000) {
            throw new Connection($response["responseMessage"], $response['responseCode']);
        }

        foreach ($sigIn->response()->getHeader('Set-Cookie') as $cookie) {
            if(str_contains($cookie, 'JSESSIONID')) {
                $params = explode(';', $cookie);
                foreach ($params as $param) {
                    if(str_contains($cookie, 'JSESSIONID')) {
                        $param = explode('=', $param);
                        $this->cookie['JSESSIONID'] = $param[1];
                        break 2;
                    }
                }
            }
        }
    }

    private function getItems(): \Generator
    {
        $lastDate = $this->getInstanceLastUpdate()->getTimestamp($this->server->getId());

        $itemsQuery = $this->rest->send('post', 'queryService/query/getSessions', [
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
                                "fieldConnector" => "OR"
                            ],
                            [
                                "fieldOperator" => "equals",
                                "fieldValues" => [
                                    "CLOSED_ERROR"
                                ]
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
        if($items['responseCode'] !== 2000) {
            throw new Connection($items["responseMessage"], $items['responseCode']);
        }

        foreach ( $items['responseBody']['sessions'] as $item) {
            yield $item;
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
