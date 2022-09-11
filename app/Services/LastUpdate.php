<?php

namespace App\Services;

use App\Models\LastUpdate as ModelLastUpdate;

class LastUpdate implements \App\Interfaces\LastUpdate
{
    protected string $typeConnection;

    /**
     * @param string $typeConnection
     * тип соединения используется в базе как столбец database_connection_id или server_connection_id
     * * должна прихоть строка с указанием столбца куда записать id
     */
    public function __construct(string $typeConnection)
    {
        $this->typeConnection = $typeConnection;
    }

    public function getTimestamp(int $connection_id): int
	{
        $last = ModelLastUpdate::where($this->typeConnection, '=', $connection_id)->first();
        if(is_null($last)) {
            return strtotime("2022-01-01");
        }
        return strtotime($last->update);
	}

    /**
     * @param int $id database_connections || server_connections
     * @param string $time
     */
	public function updateOrCreate(int $id, string $time): void
	{
        $last = ModelLastUpdate::where($this->typeConnection, '=', $id)->first();
        $time = date('Y-m-d H:i:s.u', strtotime($time));
        if(isset($last)) {
            $last->update = $time;
            $last->save();
        } else {
            ModelLastUpdate::create([
                $this->typeConnection => $id,
                'update' => $time
            ]);
        }
	}
}
