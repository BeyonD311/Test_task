<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    protected $table = "files";
    protected $fillable = [
        "name",
        "connections_id",
        "exception",
        "call_at",
        "load_at"
    ];
    protected $hidden = [
        "created_at",
        "updated_at"
    ];


    public function connection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Connections::class, 'id', 'connection_id');
    }

    public function callInfo(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(CallInfo::class, 'file_id', 'id');
    }

    /**
     * @param array $array
     * @return array
     */
    public static function getFiles(array $array): array
    {
        $where = [["exception", "=", "empty"]];
        $where = array_merge($where,
            static::generateDateFilter($array['date_from'], $array['date_to']),
            static::generateSrdDstDuration($array['src'], $array['dst'], $array['duration']));

        $instance = static::with(['callInfo'])
            ->select(["src", "dst", "duration", "name", "id", "call_at"])
            ->join('call_info', 'files.id', '=', 'call_info.file_id');
        if(!empty($array['connection'])) {
            $instance = $instance->whereIn('connections_id', $array['connection']);
        }
        if(!empty($where)) {
            $instance = $instance->where($where);
        }
        if($array['sort_field'] != "") {
            $direction =  "asc";
            if($array["sort_direction"] != "") {
                if(strtolower($array["sort_direction"]) !== "asc" && strtolower($array["sort_direction"]) !== "desc") {
                    $direction = 'asc';
                } else {
                    $direction = strtolower($array["sort_direction"]);
                }
            }
            $instance = $instance->orderBy($array['sort_field'], $direction);
        }
        $instance = $instance->paginate($array['size'], page: $array['page']);
        foreach ($instance->items() as &$item) {
            $info = $item->callInfo;
            unset($item->callInfo);
            $item->src = $info->src;
            $item->dst = $info->dst;
            $item->duration = $info->duration;
        }
        return [
            "paginate" => [
                "page" => $instance->currentPage(),
                "last_page" => $instance->lastPage(),
                "total" => $instance->total()
            ],
            "items" => $instance->items()
        ];
    }

    private static function generateDateFilter($date_from = "", $date_to = ""): array
    {
        $result = [];
        if($date_from !== "" && $date_to !== "") {
            if(strtotime($date_from) > strtotime($date_to)) {
                $temp = $date_from;
                $date_from = $date_to;
                $date_to = $temp;
            }
        }
        if($date_from !== "") {
            $result[] = ['call_at', '>=', $date_from];
        }
        if($date_to !== "") {
            $result[] = ['call_at', '<=', $date_to];
        }
        return $result;
    }

    private static function generateSrdDstDuration($src = "", $dst = "", $duration = "")
    {
        $result = [];
        if($src !== "") {
            $result[] = ['call_info.src', 'like', $src.'%'];
        }
        if($dst !== "") {
            $result[] = ["dst", "like", $dst.'%'];
        }
        if($duration !== "") {
            $result[] = ["duration", "=", $duration];
        }
        return $result;
    }
}
