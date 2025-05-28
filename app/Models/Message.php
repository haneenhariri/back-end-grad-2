<?php

namespace App\Models;

use App\Events\MessageSentEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'received_id',
        'content'
    ];

    protected static function boot()
    {
        parent::boot();

        self::created(function ($message) {
            $received = $message->recevied;
            // تأكد من أن البيانات المرسلة صحيحة
            \Log::info('Broadcasting message', [
                'message' => $message->content,
                'received' => [
                    'id' => $received->id,
                    'name' => $received->name,
                    'profile_picture' => $received->profile_picture
                ]
            ]);

            broadcast(new MessageSentEvent(
                $message->content,
                [
                    'id' => $received->id,
                    'name' => $received->name,
                    'profile_picture' => $received->profile_picture
                ]
            ))->toOthers(); // استخدم toOthers() لتجنب إرسال الحدث للمرسل
        });
    }


    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function recevied()
    {
        return $this->belongsTo(User::class, 'received_id', 'id');
    }

}

