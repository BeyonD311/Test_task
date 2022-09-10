<?php

namespace App\Services\Query;

use App\Exceptions\Connection as ConnectException;

class Cisco extends Query
{
	public function getItems(string $from, string $to): \Generator
    {
        $from = convertDateToMillisecond($from);
        $to = convertDateToMillisecond($to);
        $response = $this->connection->connection()->send("POST", "queryService/query/getSessions", $this->makeQuery($from, $to));
        $items = json_decode($response->response()->getBody()->getContents(), true);
        if($items['responseCode'] < 2000 && $items['responseCode'] >= 3000) {
            throw new ConnectException($items["responseMessage"], $items['responseCode']);
        }

        if(!isset($items['responseBody'])) {
            return;
        }

        foreach ( $items['responseBody']['sessions'] as $item) {
            yield $item;
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
                                "fieldValues" => [$from, $to]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
