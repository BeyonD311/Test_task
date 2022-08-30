<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class File
{
    public static function rename(string $from, string $to): void
    {
        if(!file_exists($from) && !rename($from, $to)) {
            Log::error("Файл не переименован ".$from);
        }
    }
}
