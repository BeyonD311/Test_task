<?php

namespace App\Http\Controllers;

use App\Models\Connections;
use App\Services\Connections\Asterisk;
use App\Services\Connections\Cisco;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FilesController extends Controller
{
    public function __construct(protected Connections $connections)
    {}


    public function store(Request $request)
    {
        try {
            $res = $this->validate($request, [
                "date_from" => "required|date",
                "date_to" => "required|date:after:date_from",
                "connection" => "required",
                "sort_field" => "string",
                "sort_direction" => "string"
            ]);
            app('db');
            // Информация о соединении и о файлах
            $info = $this->connections->infoFromConnection($res['connection']);
            $files = $this->connections->getWorkingConnection($res);
            $connection = match(strtolower($info['name'])) {
                "asterisk" => new Asterisk($info['database_connection']),
                "cisco" => new Cisco($info['server_connection'])
            };

        } catch (ValidationException $validationException) {
            dump($validationException);
        }
    }
}
