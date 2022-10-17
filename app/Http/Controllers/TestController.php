<?php

namespace App\Http\Controllers;


use App\Models\Connections;
use App\Services\Downloading\Cisco;
use GuzzleHttp\Client;

class TestController extends Controller
{
    public function __construct(protected Connections $connections)
    {}

    public function test() {
        app('db');
        $client = new Client([
            'cookies' => true,
        ]);
        $file = \GuzzleHttp\Psr7\Utils::tryFopen('/var/www/test.wav', 'a+');
        $response = $client->request('GET', 'https://10.3.0.42:8446/recordedMedia/oramedia/wav/68183acf2e5461.wav', [
            'verify' => false,
            'headers' => [
                'Cookie' => 'JSESSIONID=42B63F8D2B76E71CDCEE2AB77BEC7BD0',
                'Authorization' => 'Basic bXMwMTpQQHNzdzByZDE='
            ],
            'save_to' => $file
        ]);
    }

    //
}
