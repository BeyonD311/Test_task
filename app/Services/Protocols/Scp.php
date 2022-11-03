<?php
declare(strict_types=1);

namespace App\Services\Protocols;

use App\Exceptions\Connection;
use Illuminate\Support\Facades\Log;

class Scp extends Protocol
{

    const DOWNLOAD_PATH = "/var/www/storage/";
    protected $connect;

    /**
     * @throws Connection
     */
    public function connect(): void
    {
        $this->connect = ssh2_connect($this->server->getHost(), $this->server->getPort(), null, callbacks: [
            "debug" => function($reason, $message, $always_display) {
                Log::error(sprintf("reason: %s; $message: %s; language: %s", json_encode($reason, JSON_PRETTY_PRINT), $message, json_encode($always_display, JSON_PRETTY_PRINT)));
            },
            "disconnect" => function($reason, $message, $language) {
                Log::error(sprintf("reason: %s; $message: %s; language: %s", json_encode($reason, JSON_PRETTY_PRINT), $message, $language));
            }
        ]);
        if($this->connect === false) {
            throw new Connection("Нет подключения к серверу");
        }
        if(!ssh2_auth_password($this->connect, $this->server->getLogin(), $this->server->getPass())) {
            throw new Connection("Аутентификация не пройдена");
        }
    }

    /**
     * @throws Connection
     */
    public function disconnect(): void
    {
        if(!ssh2_disconnect($this->connect)) {
            throw new Connection("Ошибка при закрытии соединения");
        }
    }
}
