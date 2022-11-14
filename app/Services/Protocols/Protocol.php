<?php

namespace App\Services\Protocols;

use App\Interfaces\Host;
use App\Services\Dto\File;
use Illuminate\Queue\SerializesModels;

abstract class Protocol implements IProtocols
{
    use SerializesModels;
    protected File $file;

    public function __construct(protected Host $server)
    {
    }

    /**
     * @param File $file
     * @return $this
     */
    public function setFile(File $file): Protocol
    {
        $this->file = $file;
        return $this;
    }

    public function getFile(): File
    {
        return $this->file;
    }

    /**
     * @return Host
     */
    public function getServer(): Host
    {
        return $this->server;
    }

    /**
     * @param Host $server
     * @return Protocol
     */
    public function setServer(Host $server): Protocol
    {
        $this->server = $server;
        return $this;
    }

    public function execute(){}
}
