<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class File
{
    public static function rename(string $from, string $to): void
    {
        $renameStatus = rename($from, $to);
        if(!file_exists($from) && !$renameStatus) {
            Log::error("Файл не переименован ".$from);
        }
    }
}
