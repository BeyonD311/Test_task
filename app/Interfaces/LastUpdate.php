<?php

namespace App\Interfaces;

interface LastUpdate
{
    public function getTimestamp(int $connection_id): int;
    public function updateOrCreate(int $id, string $time): void;
    public function getStringDate(int $connection_id): string;
}
