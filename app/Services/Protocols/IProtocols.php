<?php

namespace App\Services\Protocols;

use App\Services\Dto\File;

interface IProtocols
{
    public function setFile(File $file): IProtocols;
    public function getFile(): File;
    public function execute();
}
