<?php

namespace App\Services\DataService;

use App\Exceptions\Connection;
use App\Interfaces\DataServices;
use App\Interfaces\LastUpdate;
use Illuminate\Queue\SerializesModels;

class DataService implements DataServices
{
    use SerializesModels;

    /**
     * @var string $lastUpdateConnection
     * * служит для определения сервиса соединения
     * * * database_connection_id || server_connection_id
     * Поле обязательное для установки
     */
    protected string $lastUpdateConnection;

    protected LastUpdate $instanceLastUpdate;

    /**
     * @throws Connection
     */
    public function __construct()
    {
        $this->setInstanceLastUpdate();
    }

    /**
     * Создает экземпляр LastUpdate
     * @throws Connection
     */
    protected function setInstanceLastUpdate()
    {
        if(is_null($this->lastUpdateConnection)) {
            throw new Connection("Не установлена колонка соединения database_connection_id || server_connection_id", 400);
        }
        $this->instanceLastUpdate = new \App\Services\LastUpdate($this->lastUpdateConnection);
    }

    /**
     * @return LastUpdate
     */
    protected function getInstanceLastUpdate(): LastUpdate
    {
        return $this->instanceLastUpdate;
    }

    public function download()
    {
        // Реализация
    }
}
