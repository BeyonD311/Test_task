<?php

namespace App\Http\Controllers;

use App\Exceptions\Connection;
use App\Models\Files;
use App\Services\Query\Asterisk as QueryAsterisk;
use App\Services\Query\Cisco as QueryCisco;
use App\Models\Connections;
use App\Services\Connections\Asterisk;
use App\Services\Connections\Cisco;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FilesController extends Controller
{
    public function __construct(protected Connections $connections)
    {}


    public function store(Request $request)
    {
        $result = [
            "status" => "success",
            "message" => "",
            "data" => []
        ];
        $code = 200;
        try {
            $res = $this->validate($request, [
                "date_from" => "required|date",
                "date_to" => "required|date:after:date_from",
                "connection" => "required|integer",
                "sort_field" => "string",
                "sort_direction" => "string",
                "page" => "required|integer",
                "size" => "required|integer"
            ]);
            app('db');
            $info = $this->connections->infoFromConnection($res['connection']);
            $connectionResult = $this->connections->getWorkingConnection($res);
            $connection = match(strtolower($info['name'])) {
                "asterisk" => new QueryAsterisk(new Asterisk($info['database_connection'])),
                "cisco" => new QueryCisco(new Cisco($info['server_connection']))
            };
            $connection->setPaginate(0, 100);
            $connectionResult['files_from_server'] = $connection->getNumbersOfRecords($res['date_from'], $res['date_to']);
            $result['data'] = $connectionResult;
        } catch (ValidationException $validationException) {
            $result["status"] = "error";
            $result["message"] = $validationException->getMessage();
            $code = 405;
        } catch (Connection $exception) {
            $result["status"] = "error";
            $result["message"] = $exception->getMessage();
            $code = $exception->getCode();
        } catch (\Exception $exception) {
            dump($exception);
        }

        return new JsonResponse($result, $code);
    }

    public function files(Request $request)
    {
        $fields = [
            "date_from" => "date",
            "date_to" => "date:after:date_from",
            "connection" => "array",
            "sort_field" => "string",
            "sort_direction" => "string",
            "src" => "string",
            "dst" => "string",
            "duration" => "integer",
            "page" => "required|integer",
            "size" => "required|integer"
        ];
        $res = $this->validate($request, $fields);
        foreach ($fields as $field => $rules) {
            if(empty($res[$field])) {
                $res[$field] = "";
            }
        }
        app("db");
        $items = Files::getFiles($res);
        return new JsonResponse($items);
    }
}
