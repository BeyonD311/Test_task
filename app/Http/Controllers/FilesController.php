<?php

namespace App\Http\Controllers;

use App\Exceptions\Connection;
use App\Models\Connections;
use App\Services\Connections\Asterisk;
use App\Services\Connections\Cisco;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use \App\Services\Connection as FacadeConnection;

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
                "sort_direction" => "string"
            ]);
            app('db');
            $info = $this->connections->infoFromConnection($res['connection']);
            $files = $this->connections->getWorkingConnection($res);
            $connection = match(strtolower($info['name'])) {
                "asterisk" => new Asterisk($info['database_connection']),
                "cisco" => new Cisco($info['server_connection'])
            };
            $query = new \App\Services\Query\Cisco($connection);
            $query->getItems("2022-09-05", "2022-09-09")->current();
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
}
