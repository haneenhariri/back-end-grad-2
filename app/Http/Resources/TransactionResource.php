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
        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'intended_account_id' => $this->intended_account_id,
            'amount' => $this->amount,
            'created_at' => $this->created_at,
            'course' => $this->whenLoaded('course', function () use ($request) {
                    $locale = app()->getLocale(); // أو: $request->getPreferredLanguage()
                    return optional($this->course)?->getTranslation('title', $locale);
                }),
            'course_cover' => $this->whenLoaded('course', function () {
                return optional($this->course)->cover;
            }),
            'student' => $this->whenLoaded('account', function () {
                return optional($this->account->user)->name;
            }),
            'instructor' => $this->whenLoaded('intendedAccount', function () {
                return optional($this->intendedAccount->user)->name;
            }),
        ];
    }
}

