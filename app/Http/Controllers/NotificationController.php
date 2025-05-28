<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );

        $pusher->trigger('my-channel', 'my-event', [
            'message' => $request->message,
            'time' => now()->toDateTimeString()
        ]);
        
        return response()->json(['message' => 'تم إرسال الإشعار بنجاح']);
    }
}