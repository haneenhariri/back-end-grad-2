<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message; // جعلها public لتكون متاحة في الحدث
    public $received; // جعلها public لتكون متاحة في الحدث

    /**
     * Create a new event instance.
     */
    public function __construct($message, $received)
    {
        $this->message = $message;
        $this->received = $received;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.user.' . $this->received['id']),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'received' => $this->received,
            'message' => $this->message
        ];
    }

    // تأكد من أن اسم الحدث صحيح
    public function broadcastAs()
    {
        return 'message.sent';
    }
}

