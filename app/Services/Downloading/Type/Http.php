<?php

namespace App\Services\Downloading\Type;

use App\Exceptions\Connection;
use Illuminate\Support\Facades\Log;
use App\Services\Protocols\Http as HttpProtocol;

class Http extends HttpProtocol
{
    public function execute()
    {
        $file = $this->getFile();
        $path = "/var/www/storage/temp/{$file->outputName}";
        $this->response = $this->client->request($this->method, $this->uri, $this->body);
        if($this->response->getStatusCode() !== 200) {
            throw new Connection("Ошибка при выполнении запроса; \n Status-code: {$this->response->getStatusCode()}; \n Response: {$this->response->getBody()->getContents()}");
        }
        $status = file_put_contents($path, $this->response->getBody()->getContents());
        if($status === false) {
            throw new Connection("Не удалось загрузить файл $file->outputName \n CallDate {$this->getFile()->calldate}");
        }
        if(file_exists($path)) {
            copy($path, "/var/www/storage/audio/$file->outputName");
            $this->getFile()->file = "/var/www/storage/audio/$file->outputName";
            unlink($path);
        }
    }
}
