<?php

namespace App\Services\Downloading;

use App\Exceptions\Connection;
use App\Interfaces\DataServices;
use App\Interfaces\LastUpdate;
use Illuminate\Queue\SerializesModels;

abstract class DataService implements DataServices
{
    use SerializesModels;

    /**
     * @var string $lastUpdateConnection
     * * служит для определения сервиса соединения
     * * * database_connection_id || server_connection_id
     * Поле обязательное для установки
     */
    protected string $lastUpdateConnection;

    protected \DateTimeInterface $finalDate;

    protected \DateTimeZone $timeZone;

    public function __construct()
    {
        $this->timeZone = new \DateTimeZone('Europe/Moscow');
    }

    public function setDate(\DateTimeInterface $date)
    {
        $this->finalDate = $date;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->finalDate;
    }

    public function getConnectionType(): string
    {
        return $this->lastUpdateConnection;
    }

    abstract public function download(): \DateTimeInterface;
}
