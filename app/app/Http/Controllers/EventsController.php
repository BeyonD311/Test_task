<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\SendMessage;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Redis;

class EventsController extends Controller
{
    /**
     * Принимает ассоциативный массив
     * Описанный в validate
     * @return JsonResponse
     */
    public function send(Request $request): JsonResponse 
    {
        
        $params = $this->validate($request, [
            "events" => "required|array|min:1",
            "events.*.account_id" => "required|int|max:1000"
        ]);
        
        foreach ($params['events'] as $event) {
            dispatch(new SendMessage($event['account_id']));
        }

        return response()->json([
            'status'=> 'success',
            'message'=> 'added events to send'
        ]);
    }
}
