<?php

namespace App\Models;

class LastUpdate extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'last_update';

    protected $fillable = [
        'connection_id',
        'update'
    ];

    public $timestamps = false;

    public static function getLastUpdate(int $connection_id): \DateTime
    {
        $lastUpdate = static::where("connection_id", "=", $connection_id)->first();
        $timeZone = new \DateTimeZone('Europe/Moscow');
        if(is_null($lastUpdate)) {
            $date = new \DateTime(env('START_APP_DATE'), $timeZone);
        } else {
            $date = new \DateTime($lastUpdate->update, $timeZone);
        }
        return $date;
    }

    public static function setLastUpdate(int $connection_id, string $lastDate)
    {
        $lastUpdate = static::where("connection_id", "=", $connection_id)->first();
        if(is_null($lastUpdate)) {
            $update = static::create([
                'connection_id' => $connection_id,
                'update' => $lastDate
            ]);
            dd($update);
        } else {
            $lastUpdate->update = $lastDate;
            $lastUpdate->save();
        }
    }
}
