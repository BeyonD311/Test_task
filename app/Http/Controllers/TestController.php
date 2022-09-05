<?php

namespace App\Http\Controllers;

use App\Services\Query\Build\Asterisk;

class TestController extends Controller
{
    public function test() {
        $query = new Asterisk;
        $query->addFiled("calldate", date("Y-m-d H:i:s"), ">=");
    }

    //
}
