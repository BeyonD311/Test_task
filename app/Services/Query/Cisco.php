<?php

namespace App\Services\Query;

use App\Exceptions\Connection as ConnectException;
use App\Interfaces\ConnectionInterface;
use App\Interfaces\QueryInterface;
use App\Services\Connection as FacadeConnection;
use phpDocumentor\Reflection\Types\Integer;

class Cisco implements QueryInterface
{
    public function __construct(
        protected ConnectionInterface $connection
    )
	{
	}

	public function getItems(string $from, string $to): \Generator
    {
        $from = convertDateToMillisecond($from);
        $to = convertDateToMillisecond($to);
        $response = $this->connection->connection()->send("POST", "queryService/query/getSessions", $this->query($from, $to));
        $items = json_decode($response->response()->getBody()->getContents(), true);
        if($items['responseCode'] < 2000 && $items['responseCode'] >= 3000) {
            throw new ConnectException($items["responseMessage"], $items['responseCode']);
        }

        if(!isset($items['responseBody'])) {
            yield [];
            return 0;
        }

        foreach ( $items['responseBody']['sessions'] as $item) {
            yield $item;
        }
    }

    private function query(int $from, int $to): array
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

    public static function convertDateToMillisecond(string $date): int
    {
        $time = new \DateTime($date);
        $matches = [];
        preg_match("/\d{6,6}$/", $time->format(self::TIMEFRAME), $matches);
        return (int)($time->getTimestamp().substr($matches[0], 0, 3));
    }
}
