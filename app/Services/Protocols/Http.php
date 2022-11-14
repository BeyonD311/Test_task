<?php

namespace App\Services\Protocols;
use App\Exceptions\Connection;
use App\Services\DTO\File;
use GuzzleHttp\Client;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

abstract class Http extends Protocol
{
    protected string $method;
    protected string $uri;
    protected Client $client;

    public function __construct(protected File $file)
    {
        parent::__construct($file);
        $this->client = new Client([
            'verify' => false,
            'timeout' => 0,
            'cookies' => true
        ]);
    }
}
