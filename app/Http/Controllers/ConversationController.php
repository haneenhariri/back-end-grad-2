<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Obtener los IDs de usuarios con los que el usuario actual ha intercambiado mensajes
        $userIds = Message::where('sender_id', $userId)
            ->orWhere('received_id', $userId)
            ->select('sender_id', 'received_id')
            ->get()
            ->flatMap(function ($message) use ($userId) {
                return [
                    $message->sender_id === $userId ? $message->received_id : $message->sender_id
                ];
            })
            ->unique()
            ->values();

        // Obtener información de cada conversación
        $conversations = [];

        foreach ($userIds as $otherUserId) {
            // Obtener el último mensaje entre los dos usuarios
            $lastMessage = Message::where(function ($query) use ($userId, $otherUserId) {
                $query->where('sender_id', $userId)
                    ->where('received_id', $otherUserId);
            })->orWhere(function ($query) use ($userId, $otherUserId) {
                $query->where('sender_id', $otherUserId)
                    ->where('received_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->first();

            if ($lastMessage) {
                // Obtener información del otro usuario
                $otherUser = User::find($otherUserId);

                // Contar mensajes no leídos
                $unreadCount = Message::where('sender_id', $otherUserId)
                    ->where('received_id', $userId)
                    ->count();

                $conversations[] = [
                    'id' => $otherUserId, // ID de la conversación (usamos el ID del otro usuario)
                    'user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'profile_picture' => $otherUser->profile_picture,
                    ],
                    'last_message' => $lastMessage->content,
                    'last_message_at' => $lastMessage->created_at,
                ];
            }
        }

        // Ordenar conversaciones por la fecha del último mensaje (más reciente primero)
        usort($conversations, function ($a, $b) {
            return strtotime($b['last_message_at']) - strtotime($a['last_message_at']);
        });

        return self::success($conversations);
    }
}

