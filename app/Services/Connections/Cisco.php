<?php

namespace App\Services\Connections;

use App\Exceptions\Connection;
use App\Services\Dto\Connection as ConnectionDTO;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class Cisco implements \App\Services\Interfaces\Connection
{
    use SerializesModels;
    protected string $uri;
    protected PendingRequest $connect;
    private bool $statusConnection = false;
    private bool $sigIn = false;
    public $cookie;

    public function __construct(protected ConnectionDTO $connection)
    {
        $server = $this->connection->server;
        $this->uri = 'https://'.$server->host.':'.$server->port.'/ora/';
        $this->connect = Http::withOptions([
            "base_uri" => $this->uri,
            "verify" => false
        ]);
    }

    /**
     * @return PendingRequest
     * @throws \Exception
     */
    public function connection(): PendingRequest
    {
        $this->sigIn();
        return $this->connect;
    }

    /**
     * Свойство sigIn указывает на была ли авторизация у данного instance
     * @throws \Exception
     */
    private function sigIn(): void
    {
        $response = $this->connect->send("POST", 'authenticationService/authentication/signIn', [
            'json' => [
                "requestParameters" => [
                    'username' => $this->connection->server->login,
                    'password' => $this->connection->server->pass
                ]
            ]
        ]);
        $this->sigIn = true;
        $this->cookie = $response->cookies();
        $response = $response->object();
        if($response->responseCode === 2000) {
            $this->statusConnection = true;
        }
    }

    public function getParams(): ConnectionDTO
    {
        return $this->connection;
    }

    public function getParam(string $param): mixed
    {
        if(isset($this->connection->{$param})) {
            return $this->connection->{$param};
        }
        return null;
    }

    public function disconnect(): void
    {
        $this->sigIn = false;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function checkConnection(): bool
    {
        if($this->sigIn === false) {
            $this->sigIn();
        }
        return $this->statusConnection;
    }
}
