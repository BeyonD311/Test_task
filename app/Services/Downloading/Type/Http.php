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
        $this->file->file = str_replace("https", "http", $this->file->file);
        $response = file_get_contents($this->file->file);
        dd($response);
       /* if($this->response->getStatusCode() !== 200) {
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
        }*/
    }
}
