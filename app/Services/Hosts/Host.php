<?php

namespace App\Services\Hosts;


use Illuminate\Queue\SerializesModels;

abstract class Host implements \App\Interfaces\Host
{
    use SerializesModels;

    protected string $_host;
    protected int $_port;
    protected string $_login;
    protected string $_pass;
    protected int $connectionId;
    protected int $id;

	public function setHost(string $host): static
	{
        $this->_host = $host;
        return $this;
	}

	public function setPort(int $port): static
	{
        $this->_port = $port;
        return $this;
	}

    /**
     * @return int
     */
	public function getPort(): int
	{
		return $this->_port;
	}

    /**
     * @return string
     */
	public function getHost(): string
	{
		return $this->_host;
	}

    public function setLogin(string $login): static
    {
        $this->_login = $login;
        return $this;
    }

    public function setPass(string $pass): static
    {
        $this->_pass = $pass;
        return $this;
    }

    public function getLogin(): string
    {
        return $this->_login;
    }

    public function getPass(): string
    {
        return $this->_pass;
    }

    public function setConnectionId(int $connectionId)
    {
        $this->connectionId = $connectionId;
    }

    public function getConnectionId(): int
    {
        return $this->connectionId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }
}
