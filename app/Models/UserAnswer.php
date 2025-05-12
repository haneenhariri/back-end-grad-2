<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserAnswer extends Pivot
{
    protected $table='user_answers';
//    public $incrementing=true;

    protected $fillable=[
        'user_id',
        'question_id',
        'answer',
        'mark',
    ];

    public function question(){
        return $this->belongsTo(Question::class);
    }
}
