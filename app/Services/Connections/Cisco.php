<?php

namespace App\Services\Connections;

use App\Exceptions\Connection;
use App\Services\Connections\Options\Server;
use App\Services\Protocols\Http;

class Cisco implements \App\Interfaces\ConnectionInterface
{
    protected Http $httpClient;

    private array $cookie = [
        'JSESSIONID' => NULL
    ];

    public function __construct(protected Server $server)
    {
        $this->httpClient = new Http('https://'.$server->getHost().':'.$server->getPort().'/ora/');
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
        $sigIn = $this->httpClient->send('post', 'authenticationService/authentication/signIn', [
            'json' => [
                "requestParameters" => [
                    'username' => $this->server->getLogin(),
                    'password' => $this->server->getPass()
                ]
            ]
        ]);
        $response = json_decode($sigIn->response()->getBody()->getContents(), true);
        #Cisco code success 2000
        if($response['responseCode'] !== 2000) {
            throw new Connection($response["responseMessage"], $response['responseCode']);
        }

        foreach ($sigIn->response()->getHeader('Set-Cookie') as $cookie) {
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
