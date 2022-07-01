<?php

namespace App\Interfaces;

interface Host
{
    public function setHost(string $host);
    public function setPort(int $port);
    public function setLogin(string $login);
    public function setPass(string $pass);
    public function getPort(): int;
    public function getHost(): string;
    public function getLogin(): string;
    public function getPass(): string;
    public function setId(int $id);
    public function getId(): int;
    public function setConnectionId(int $connectionId);
    public function getConnectionId(): int;
}
