<?php

namespace App\Services\Protocols;

use App\Exceptions\Connection;
use App\Interfaces\Host;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Scp
{
    use SerializesModels;

    protected string $download = "/var/www/storage/";

    protected string $pathDownload;


    public function __construct
    (
        protected Host $server,
        protected string $to
    )
    {}

    public function setPathDownload(string $path)
    {
        $this->pathDownload = $path;
        return $this;
    }

    protected function makeShhPass(): string
    {
        return "sshpass -p '".$this->server->getPass()."'";
    }

    protected function makeScp(): string
    {
        return "scp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -rq ".
            $this->server->getLogin().
            "@".$this->server->getHost().
            ":".$this->pathDownload.
            " ".$this->download.$this->to;
    }

    public function getServer(): Host
    {
        return $this->server;
    }

    public function download()
    {
        $this->checkOutPath();
        $exec = $this->makeShhPass() ." ".$this->makeScp();
        $output = [];
        $code = 0;
        exec($exec, $output, $code);
        if ($code != 0) {
            Log::info(json_encode($output, JSON_PRETTY_PRINT));
            Log::info($code);
            if ($code == 1) {
                $output = "File not found";
            }
            throw new Connection(json_encode($output, JSON_PRETTY_PRINT), 404);
        }
    }

    protected function checkOutPath()
    {
        if(strpos($this->pathDownload, ".gz") === false) {
            return;
        }
        $this->to = "temp";
    }
}
