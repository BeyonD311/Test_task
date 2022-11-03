<?php

namespace App\Services\Protocols;

use App\Services\FileDTO;

interface IProtocols
{
    public function setFile(FileDTO $file): IProtocols;
    public function getFile(): FileDTO;
    public function execute();
}
