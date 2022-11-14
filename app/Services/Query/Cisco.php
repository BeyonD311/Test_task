<?php

namespace App\Services\Query;

use App\Exceptions\Connection;
use App\Services\Factory\Dto as DtoFactory;
use App\Services\Dto\File;

class Cisco extends Query
{
	public function getItems(string $from, string $to): \Generator
    {
        $from = convertDateToMillisecond($from);
        $to = convertDateToMillisecond($to);
        $items = $this->crawlingPage($from, $to);
        foreach ($items as $collectionItems) {
            foreach ($collectionItems as $item)
            {
                yield $item;
            }
        }
    }

    private function makeQuery(int $from, int $to): array
    {
        return [
            "json" => [
                "requestParameters" => [
                    [
                        "fieldName" => "sessionState",
                        "fieldConditions" => [
                            [
                                "fieldOperator" => "equals",
                                "fieldValues" => [
                                    "CLOSED_NORMAL"
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
                                "fieldValues" => [$from, $to]
                            ]
                        ]
                    ]
                ],
                "pageParameters" => [
                    "offset" => $this->paginate['page'],
                    "limit" => $this->paginate['size']
                ],
                "sortParameters" => [
                    "byFieldName" => "sessionState",
                    "order" => "CLOSED_NORMAL"
                ]
            ]
        ];
    }

    public function getNumbersOfRecords(string $from, string $to): int
    {
        $from = convertDateToMillisecond($from);
        $to = convertDateToMillisecond($to);
        $total = 0;
        $items = $this->crawlingPage($from, $to);
        foreach ($items as $item) {
            $total += count($item);
        }
        return $total;
    }

    private function crawlingPage(int $from, int $to): \Generator
    {
        /**@var \Illuminate\Http\Client\PendingRequest $clientHttp*/
        $clientHttp = $this->connection->connection();
        $query = $this->makeQuery($from, $to);
        $query["json"]["pageParameters"]["offset"] = $query["json"]["pageParameters"]["offset"] - 1;
        $query['cookies'] = $this->connection->cookie;
        $items = [];
        while (true) {
            $response = $clientHttp->send("POST", "queryService/query/getSessions", $query)->json();
            if($response['responseCode'] == 2001) {
                break;
            }
            if($response['responseCode'] < 2000 || $response['responseCode'] > 2001) {
                throw new Connection("", 500);
            }
            $this->paginate['page'] += $this->paginate['size'];
            foreach ($response['responseBody']['sessions'] as $item) {
                $param = [
                    "file" => $item['urls']['wavUrl'],

                ];
                $duration = 0;
                foreach ($item['tracks'] as $track) {
                    $duration += $track['trackDuration'];
                }
                $param['duration'] = $duration / 1000;
                $param['calldate'] = date("Y-m-d H:i:s", $item["sessionStartDate"] / 1000);
                $param['uniqueid'] = md5($item['urls']['wavUrl']);
                $param['outputName'] = md5($item['urls']['wavUrl'])."-".$this->connection->getParam('id').".wav";
                $param['connection_id'] = $this->connection->getParam('id');
                $param['downloadMethod'] = $this->connection->getParam("type_connection");
                $param['queue'] = "Cisco";
                $param['options']['cookie'] = $this->connection->cookie;
                $param = array_merge($param, $this->generatePhone($item['tracks']));
                $items[] = DtoFactory::getInstance(File::class, $param);
            }
            $query["json"]["pageParameters"]["offset"] += $query["json"]["pageParameters"]['limit'];
            yield $items;
        }
    }

    private function generatePhone(array $tracks): array
    {
        $result = [];
        /**
         * Индекс 0 - куда звонит
         * Индекс 1 - кто звонит
         */
        if(count($tracks) > 1) {
            $result["src"] = $tracks[1]["participants"][0]['deviceRef'];
            $result["dst"] = $tracks[0]["participants"][0]['deviceRef'];
        } else {
            $result["src"] = $tracks[0]["participants"][0]['deviceRef'];
            $result["dst"] = "empty";
        }
        return $result;
    }
}
