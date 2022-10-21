<?php

namespace App\Services\Protocols;

use App\Exceptions\Connection;
use App\Interfaces\Host;
use App\Services\File;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class Scp
{
    use SerializesModels;

    protected string $download = "/var/www/storage/";

    protected string $pathDownload;

    protected $connect;


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
            $logMessage = sprintf("Code: %d; Message: %s; \n Exec: %v",
            $code, json_encode($output, JSON_PRETTY_PRINT), $exec);
            Log::error($logMessage);
            if ($code == 1) {
                $output = "File not found";
            }
            throw new Connection(json_encode($output, JSON_PRETTY_PRINT), 404);
        }
        $names = $this->buildOutputName();
        File::rename($names['orig'], $names['name']);
        return $names['name'];
    }

    protected function checkOutPath()
    {
        if(strpos($this->pathDownload, ".gz") === false) {
            return;
        }
        $this->to = "temp";
    }

    protected function buildOutputName(): array
    {
        $name = explode("/", $this->pathDownload);
        $origName = array_pop($name);
        $name = explode(".", $origName);
        $expansion = array_pop($name);
        $name[] = array_pop($name)."-".$this->server->getConnectionId().$expansion;
        unset($expansion, $connectionId);
        return  [
            "orig" => $this->download.$this->to.'/'.$origName,
            "name" => $this->download.$this->to.'/'.implode('.', $name)
        ];
    }
}
