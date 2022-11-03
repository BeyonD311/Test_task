<?php

namespace App\Services\Downloading\Type;

use App\Exceptions\Connection;
use App\Services\Protocols\Scp as ScpProtocol;

class Scp extends ScpProtocol
{
    public function execute()
    {
        $path = static::DOWNLOAD_PATH."temp/{$this->file->outputName}";
        $this->connect();
        $status = ssh2_scp_recv($this->connect, $this->file->file, $path);
        if(file_exists($path) && $status === true) {
            copy($path, static::DOWNLOAD_PATH."audio/{$this->file->outputName}");
            unlink($path);
            $this->file->file = static::DOWNLOAD_PATH."audio/{$this->file->outputName}";
        } else {
            throw new Connection("Не удалось загрузить файл");
        }
        $this->disconnect();
    }
}
