<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'intended_account_id',
        'amount',
        'course_id'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function intendedAccount()
    {
        return $this->belongsTo(Account::class, 'intended_account_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
