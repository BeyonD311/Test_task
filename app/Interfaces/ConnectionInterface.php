<?php

namespace App\Interfaces;
interface ConnectionInterface
{
    public function connect();
    public function getStatus();
    public function disconnect();
    public function checkConnection();

}
