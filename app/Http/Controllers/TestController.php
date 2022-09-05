<?php

namespace App\Http\Controllers;

use App\Services\Query\Build\Cisco;

class TestController extends Controller
{
    public function test() {
        $query = new Cisco();
        $query->addFiled("calldate", date("Y-m-d H:i:s"), ">=");
    }

    //
}
