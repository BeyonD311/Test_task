<?php

namespace App\Services\Connections;

use App\Exceptions\Connection;
use Illuminate\Support\Facades\Log;

class UcScp extends Scp
{
    const PATH = "/storage/records/";

    public function download()
    {
        try {
            parent::download();
        } catch (Connection $connection) {
            $path = static::PATH.implode("/", array_slice(explode("/",$this->pathDownload), -2)).".gz";
            parent::setPathDownload($path);
            parent::download();
            $file = $this->download.$this->to."/".implode("",array_slice(explode("/", $path), -1));
            $newFile = $this->download."/audio/".str_replace(".gz", "",
                    implode("",array_slice(explode("/", $path), -1)));
            Log::info($file."\n".$newFile);
            if(file_exists($file)) {
                $code = 0;
                $output = [];
                exec("gunzip $file", $output, $code);
                if($code != 0) {
                    throw new \Exception("Не получается разархивировать файл", 500);
                }
                $file = str_replace(".gz", "", $file);
                $copy = copy($file, $newFile);
                if($copy) {
                    $copy = "try";
                } else {
                    $copy = "false";
                }
                unlink($file);
            }
        }
    }
}
