<?php

namespace App\Http\Controllers;


use App\Models\Connections;
use App\Services\Query\Asterisk;
use \App\Services\Query\Cisco;
use App\Services\Factory\ConnectionFactory;
use App\Services\Query\ContextQuery;

class TestController extends Controller
{
    public function test() {
        /*app('db');
        $dto = Connections::infoFromConnection(2);
        $queryAsterisk = new Asterisk();
        $queryCisco = new Cisco();
        $queryContext = new ContextQuery();
        $connection = ConnectionFactory::getInstance($dto);
        $queryContext->setContext($queryCisco, $connection);
        $queryContext->setOptions();
        foreach ($queryContext->getItems("2022-01-01", "2022-11-09") as $item) {

        }*/
    }
}
