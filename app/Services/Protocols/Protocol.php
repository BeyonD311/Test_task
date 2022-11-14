<?php

namespace App\Services\Protocols;

use App\Services\Dto\File;
use Illuminate\Queue\SerializesModels;

abstract class Protocol implements IProtocols
{
    use SerializesModels;

    public function __construct(protected File $file)
    {
    }
}
