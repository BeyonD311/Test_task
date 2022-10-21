<?php

namespace App\Services\Query;

use App\Exceptions\Connection;
use App\Exceptions\Connection as ConnectException;

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
        while (true) {
            $query = $this->connection->connection()->send("POST", "queryService/query/getSessions", $this->makeQuery($from, $to));
            $response = json_decode($query->response()->getBody()->getContents(), true);
            if($response['responseCode'] == 2001) {
                break;
            }
            if($response['responseCode'] < 2000 || $response['responseCode'] > 2001) {
                throw new Connection("", 500);
            }
            $this->paginate['page'] += $this->paginate['size'];
            yield $response['responseBody']['sessions'];
        }
    }
}
