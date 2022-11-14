<?php
declare(strict_types=1);

namespace App\Services\Protocols;

use App\Exceptions\Connection;
use App\Services\Dto\File;
use App\Services\Dto\Server;
use Illuminate\Support\Facades\Log;

abstract class Scp extends Protocol
{
    protected $connect;
    protected Server $server;
    /**
     * @param File $file
     */
    public function __construct(File $file)
    {
        parent::__construct($file);
        $this->server = $this->file->options['server'];
    }

    /**
     * @throws Connection
     */
    public function connect(): void
    {
        $this->connect = ssh2_connect($this->server->host, $this->server->port, null, callbacks: [
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
        if(!ssh2_auth_password($this->connect, $this->server->login, $this->server->pass)) {
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
