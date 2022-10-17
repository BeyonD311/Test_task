<?php

namespace App\Interfaces;
interface ConnectionInterface
{
    public function connection();
    public function getOptions(): array;
}
