<?php

namespace App\Services\Downloading\Type;

use App\Exceptions\Connection;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\SetCookie;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\Protocols\Http as HttpProtocol;

class Http extends HttpProtocol
{
    use SerializesModels;

    public function execute()
    {
        $path = "/var/www/storage/temp/{$this->file->outputName}";
        $server = $this->file->options['server'];
        $response = $this->client->request("GET", $this->file->file, [
            'headers' => [
                'Authorization' => 'Basic '.base64_encode("$server->login:$server->pass")
            ]
        ]);
        if($response->getStatusCode() !== 200) {
            throw new Connection("Ошибка при выполнении запроса; \n Status-code: {$response->getStatusCode()()}; \n Response: {$response->getBody()->getContents()}");
        }
        $status = file_put_contents($path, $response->getBody()->getContents());
        if($status === false) {
            throw new Connection("Не удалось загрузить файл {$this->file->outputName} \n CallDate {$this->file->calldate}");
        }
        if(file_exists($path)) {
            copy($path, "/var/www/storage/audio/{$this->file->outputName}");
            $this->file->file = "/var/www/storage/audio/{$this->file->outputName}";
            unlink($path);
        }
    }
}
