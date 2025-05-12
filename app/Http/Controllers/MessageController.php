<?php

namespace App\Http\Controllers;

use App\Http\Requests\Message\StoreRequest;
use App\Http\Requests\Message\UpdateRequest;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validationData();
        $data['sender_id'] = auth()->id();
        Message::create($data);
        return self::success();
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Message $message)
    {
        $this->authorize('update', $message);
        $data = $request->validationData();
        $message->update($data);
        return self::success(new MessageResource($message), 'updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        $this->authorize('delete', $message);
        $message->delete();
        return self::success(null,'deleted successfully');
    }

    public function viewChat(User $user){
        $authUserId = auth()->id();
        $otherUserId = $user->id;

        $messages = Message::where(function ($query) use ($authUserId, $otherUserId) {
            // الرسائل المرسلة من المستخدم المسجل دخول إلى المستخدم الآخر
            $query->where('sender_id', $authUserId)
                ->where('received_id', $otherUserId);
        })->orWhere(function ($query) use ($authUserId, $otherUserId) {
            // الرسائل المستلمة من المستخدم الآخر إلى المستخدم المسجل دخول
            $query->where('sender_id', $otherUserId)
                ->where('received_id', $authUserId);
        })->orderBy('created_at', 'asc') // ترتيب الرسائل حسب وقت الإرسال
        ->get();
        return self::success(MessageResource::collection($messages));
    }


}
