<?php

namespace App\Services\Protocols;
use App\Exceptions\Connection;
use App\Interfaces\Host;
use App\Services\FileDTO;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;

class Http extends Protocol
{
    protected string $method;
    protected string $uri;
    protected $body;
    protected FileDTO $file;
    protected Response $response;

    protected Client $client;

    public function __construct(Host $server, $uri = "")
    {
        parent::__construct($server);
        $this->client = new Client([
            'base_uri' => $uri,
            'verify' => false,
            'cookies' => true
        ]);
    }

    public function execute()
    {
        $this->response = $this->client->request($this->method, $this->uri, $this->body);
        if($this->response->getStatusCode() !== 200) {
            Log::error($this->response->getBody()->getContents());
            throw new Connection("Ошибка при выполнении запроса; Status-code: {$this->response->getStatusCode()}");
        }
        return json_decode($this->response->getBody()->getContents(), true);
    }

    /**
     * @param string $header
     * @return array
     * @throws Connection
     */
    public function getHeader(string $header): array
    {
        if(!isset($this->response)) {
            Log::error("Заголовки отсутствуют");
            throw new Connection("Заголовки отсутствуют");
        }
        return $this->response->getHeader($header);
    }

    public function getHeaders(): array
    {
        if(!isset($this->response)) {
            Log::error("Заголовки отсуцствуют");
            throw new Connection("Заголовки отсуцствуют");
        }
        return $this->response->getHeaders();
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setUri(string $uri):static
    {
        $this->uri = $uri;
        return $this;
    }

    public function setBody($body):static
    {
        $this->body = $body;
        return $this;
    }
}
