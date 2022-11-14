<?php

namespace App\Services\Downloading;

use App\Services\DTO\File;
use Illuminate\Support\Facades\Artisan;

class DownloadFile
{
    protected File $file;

    public function setFile(File $file)
    {
        $this->file = $file;
    }

    public function download()
    {
        try {
            Artisan::call("file", [
                "item" => serialize($this->file)
            ]);
        } catch (\Throwable $throwable) {
            throw $throwable;
        }
    }
}
