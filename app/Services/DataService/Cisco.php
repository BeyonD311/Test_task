<?php

namespace App\Services\DataService;

use App\Exceptions\Connection;
use App\Interfaces\DataServices;
use App\Services\Connections\Rest;
use App\Services\Hosts\Host;
use App\Services\LastUpdateServer;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class Cisco implements DataServices
{
    private Rest $rest;

    private array $cookie = [
        'JSESSIONID' => NULL
    ];

    public function __construct(
        protected Host $server
    )
    {
        $this->rest = new Rest('https://'.$this->server->getHost().':'.$this->server->getPort().'/ora/');
    }

    public function download()
    {
        $this->sigIn();
        $duration = 0;
        $maxDate = LastUpdateServer::getTime($this->server->getId());
        foreach ($this->getItems() as $item) {
            $this->fileDownload($item);
            foreach ($item['tracks'] as $track) {
                $duration += $track['trackDuration'];
            }
            $item['duration'] = $duration;
            $this->saveJson($item);
            if($maxDate < $item['sessionStartDate']) {
                $maxDate = $item['sessionStartDate'];
            }
        }
        Log::info(json_encode($this->getItems(), JSON_PRETTY_PRINT));
        LastUpdateServer::updateOrCreate($this->server->getId(), (int)$maxDate);
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
        $lastDate = LastUpdateServer::getTime($this->server->getId());

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
                                "fieldValues" => [strtotime($lastDate) * 1000, time() * 1000]
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
        $context = stream_context_create([
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
        ]);
        $getFile = file_get_contents($item['urls']['wavUrl'], context: $context);
        $fileName = md5($item['urls']['wavUrl']);
        $path = '/var/www/storage/audio/'.$fileName.".wav";
        file_put_contents($path, print_r($getFile, true));
    }

    private function saveJson(array $item)
    {
        $result = [
            'calldate' => date('Y-m-d H:i:s', $item["sessionStartDate"]),
            'duration' => round($item['duration'] / 1000),
        ];
        file_put_contents('/var/www/storage/callInfo/'.md5($item['urls']['wavUrl']).".json", print_r(json_encode($result, JSON_PRETTY_PRINT), true));
    }
}
