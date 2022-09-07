<?php

namespace App\Interfaces;
interface ConnectionInterface
{
    public function connection(Host $host);
    public function getOptions(): array;
}
