<?php

namespace App\Services\Query;

use App\Exceptions\Connection;
use App\Exceptions\Connection as ConnectException;
use App\Services\DtoFactory;
use App\Services\FileDTO;

class Cisco extends Query
{
	public function getItems(string $from, string $to): \Generator
    {
        $from = convertDateToMillisecond($from);
        $to = convertDateToMillisecond($to);
        $items = $this->crawlingPage($from, $to);
        foreach ($items as $item) {
            foreach ($item as $i) {
                yield $i;
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
        $clientHttp = clone $this->connection->connection();
        while (true) {
            $clientHttp->setMethod("POST")
                ->setUri("queryService/query/getSessions")
                ->setBody($this->makeQuery($from, $to));
            $response = $clientHttp->execute();
            if($response['responseCode'] == 2001) {
                break;
            }
            if($response['responseCode'] < 2000 || $response['responseCode'] > 2001) {
                throw new Connection("", 500);
            }
            $this->paginate['page'] += $this->paginate['size'];
            $items = [];
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
                $param = array_merge($param, $this->generatePhone($item['tracks']));
                $items[] = DtoFactory::createDto(FileDTO::class, $param);
            }
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
