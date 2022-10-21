<?php

namespace App\Services\Protocols;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Http
{
    protected $response;

    protected Client $client;

    public function __construct($uri)
    {
        $this->client = new Client([
            'base_uri' => $uri,
            'verify' => false,
            'cookies' => true
        ]);
    }

    public function send(string $method, string $uri, $body): static
    {
        $this->response = $this->client->request($method, $uri, $body);
        return $this;
    }

    public function response(): Response
    {
        return $this->response;
    }
}
