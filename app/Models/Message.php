<?php

namespace App\Models;

use App\Events\MessageSentEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

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
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = Crypt::encryptString($value);
    }
    // 🔓 فك التشفير تلقائي عند القراءة
    public function getContentAttribute($value)
    {
        try {
            // تحقق إن كانت الرسالة مشفرة فعلاً
            // نفترض أن الرسالة المشفرة تحتوي على النمط الخاص بالتشفير من Laravel مثل: "eyJpdiI6..."
            if (!str_starts_with($value, 'eyJ') && !str_contains($value, '::')) {
                // غير مشفرة، أرجع القيمة كما هي
                return $value;
            }

            // محاولة فك التشفير
            return \Crypt::decryptString($value);
        } catch (\Exception $e) {
            \Log::error('Failed to decrypt message: ' . $e->getMessage(), [
                'message_id' => $this->id,
                'value' => $value,
            ]);
            return '[رسالة غير قابلة للقراءة]';
        }
    }
}

