<?php

namespace App\Services;

use Illuminate\Queue\SerializesModels;

class FileDTO
{
    use SerializesModels;
    public float $duration;
    public string $src;
    public string $dst;
    public string $file;
    public string $calldate;
    public string $uniqueid;
    public string $connection_id;
    public string $outputName;
}
