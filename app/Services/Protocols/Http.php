<?php

namespace App\Services\Protocols;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Queue\SerializesModels;

class Http implements IProtocols
{
    use SerializesModels;
    protected $response;

    protected Client $client;

    public function __construct($uri = "")
    {
        $this->client = new Client([
            'base_uri' => $uri,
            'verify' => false,
            'cookies' => true
        ]);
    }

    public function execute(string $method = "", string $uri = "", $body = null): static
    {
        $this->response = $this->client->request($method, $uri, $body);
        return $this;
    }

    public function response(): Response
    {
        return $this->response;
    }
}
