<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
public function toArray(Request $request): array
{
    $locale = $request->query('lang') ?? $request->header('Accept-Language') ?? app()->getLocale();
    $user = $request->user();
    $isStudent = $user && $user->hasRole('student');

    if ($isStudent) {
        // ما يُعرض للطالب فقط
        return [
            'course' => optional($this->course)?->getTranslation('title', $locale),
            'course_cover' => optional($this->course)?->cover,
            'instructor' => optional($this->intendedAccount->user)?->name,
            'purchased_at' => $this->created_at,
            'total_price' => optional($this->course)?->price,
        ];
    }

    // ما يُعرض للمسؤولين أو المدرسين
    return [
        'id' => $this->id,
        'account_id' => $this->account_id,
        'intended_account_id' => $this->intended_account_id,
        'amount' => $this->amount,
        'created_at' => $this->created_at,
        'course' => optional($this->course)?->getTranslation('title', $locale),
        'course_cover' => optional($this->course)?->cover,
        'student' => optional($this->account->user)?->name,
        'instructor' => optional($this->intendedAccount->user)?->name,
    ];
}

}

