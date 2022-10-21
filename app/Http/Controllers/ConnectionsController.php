<?php

namespace App\Http\Controllers;
use App\Models\Connections;
use Illuminate\Http\JsonResponse;

class ConnectionsController extends Controller
{
    public function __construct(
        protected Connections $connections
    )
    {}

    public function index(): JsonResponse
    {
        app('db');
        $response = [
            "status" => "success",
            "data" => [],
        ];
        $connections = $this->connections
            ->getWorkingConnections()
            ->map(function ($item) {
            return [
                "id" => $item->id,
                "name" => $item->name,
                "power" => $item->power
            ];
        })->toArray();
        $code = 200;
        if(empty($connections)) {
            $response['status'] = "fail";
            $code = 400;
        }else {
            $response['data'] = $connections;
        }
        return new JsonResponse($response, $code);
    }
}
