<?php

namespace App\Http\Controllers;
use App\Models\CDR;
use App\Services\Connections\Scp;
use App\Services\DataService\Asterisk;
use App\Services\Driver;
use App\Services\Hosts\Server;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test() {
        dd(123);

    }

    //
}
