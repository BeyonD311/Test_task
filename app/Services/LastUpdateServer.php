<?php

namespace App\Services;

use App\Models\LastUpdate as ModelLastUpdate;

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

    public static function updateOrCreate(int $server_id, string $time): void
    {
        $last = ModelLastUpdate::where('server_connection_id', '=', $server_id)->first();
        $time = date('Y-m-d H:i:s', strtotime($time));
        if(isset($last)) {
            $last->update = $time;
            $last->save();
        } else {
            ModelLastUpdate::create([
                'server_connection_id' => $server_id,
                'database_connection_id' => 0,
                'update' => $time
            ]);
        }
    }
}
