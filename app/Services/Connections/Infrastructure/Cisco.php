<?php

namespace App\Services\Connections\Infrastructure;

use App\Exceptions\Connection;
use App\Services\Connections\DTO\Server;
use App\Services\Protocols\Http;
use Illuminate\Queue\SerializesModels;

class Cisco implements \App\Interfaces\ConnectionInterface
{
    use SerializesModels;
    protected Http $httpClient;

    private array $cookie = [
        'JSESSIONID' => NULL
    ];

    public function __construct(protected Server $server)
    {
        $this->httpClient = new Http($server, 'https://'.$server->getHost().':'.$server->getPort().'/ora/');
        $this->httpClient
            ->setMethod("POST")
            ->setUri('authenticationService/authentication/signIn')
            ->setBody([
                'json' => [
                    "requestParameters" => [
                        'username' => $this->server->getLogin(),
                        'password' => $this->server->getPass()
                    ]
                ]
            ]);
    }

    /**
     * @throws Connection
     */
    public function connection(): Http
	{
		$this->sigIn();
		return $this->httpClient;
	}

	public function getOptions(): array
    {
        return $this->cookie;
    }

    private function sigIn(): void
    {
        $response = $this->httpClient->execute();
        #Cisco code success 2000
        if($response['responseCode'] !== 2000) {
            throw new Connection($response["responseMessage"], $response['responseCode']);
        }
        foreach ($this->httpClient->getHeader('Set-Cookie') as $cookie) {
            if(str_contains($cookie, 'JSESSIONID')) {
                $params = explode(';', $cookie);
                foreach ($params as $param) {
                    if(str_contains($cookie, 'JSESSIONID')) {
                        $param = explode('=', $param);
                        $this->cookie['JSESSIONID'] = $param[1];
                        break 2;
                    }
                }
            }
        }
    }
}
