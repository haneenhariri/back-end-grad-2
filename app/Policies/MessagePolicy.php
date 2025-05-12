<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Message $message): bool
    {
        return $message->sender_id == auth()->id();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Message $message): bool
    {
        return $message->sender_id == auth()->id();
    }

}
