<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendMessage;
use Illuminate\Support\Facades\Redis;

class EventsController extends Controller
{
    
    public function send(Request $request) {

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
