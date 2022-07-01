<?php

namespace App\Services;

use App\Models\LastUpdate as ModelLastUpdate;

final class LastUpdateDatabase
{
    /**
     * return timestamp
     * @param int $connection_id
     * @return int
     */
    public static function getTime(int $connection_id): int
    {
        $last = ModelLastUpdate::where('database_connection_id', '=', $connection_id)->first();
        if(is_null($last)) {
            return strtotime("2022-01-01");
        }
        return strtotime($last->update);
    }

    public static function updateOrCreate(int $connection_id, string $time): void
    {
        $last = ModelLastUpdate::where('database_connection_id', '=', $connection_id)->first();
        $time = date('Y-m-d H:i:s', strtotime($time));
        if(isset($last)) {
            $last->update = $time;
            $last->save();
        }else {
            ModelLastUpdate::create([
                'server_connection_id' => 0,
                'database_connection_id' => $connection_id,
                'update' => $time
            ]);
        }
    }
}
