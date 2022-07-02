<?php

namespace App\Services;

use App\Models\LastUpdate as ModelLastUpdate;
use Illuminate\Support\Facades\Log;

class LastUpdateServer
{
    /**
     * return timestamp
     * @param int $connection_id
     * @return int
     */
    public static function getTime(int $server_id): int
    {
        $last = ModelLastUpdate::where('server_connection_id', '=', $server_id)->first();
        if(is_null($last)) {
            return strtotime("2022-01-01");
        }
        return strtotime($last->update);
    }

    public static function updateOrCreate(int $server_id, int $time): void
    {
        $last = ModelLastUpdate::where('server_connection_id', '=', $server_id)->first();
        date_default_timezone_set('UTC');
        $date = date('d-m-Y H:i:s', $time);
        Log::info($date);
        Log::info(strtotime($date));
        if(isset($last)) {
            $last->update = '2022-01-01';
            $last->save();
        } else {
            ModelLastUpdate::create([
                'server_connection_id' => $server_id,
                'database_connection_id' => 0,
                'update' => '2022-01-01'
            ]);
        }
    }
}
