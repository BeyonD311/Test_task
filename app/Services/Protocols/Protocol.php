<?php

namespace App\Services\Protocols;

use App\Interfaces\Host;
use App\Services\FileDTO;
use Illuminate\Queue\SerializesModels;

abstract class Protocol implements IProtocols
{
    use SerializesModels;
    protected FileDTO $file;

    public function __construct(protected Host $server)
    {
    }

    /**
     * @param FileDTO $file
     * @return $this
     */
    public function setFile(FileDTO $file): Protocol
    {
        $this->file = $file;
        return $this;
    }

    public function getFile(): FileDTO
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
