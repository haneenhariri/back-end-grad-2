<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'balance'
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class,'account_id','id');
    }

    public function intendedTransaction()
    {
        return $this->hasMany(Transaction::class, 'intended_account_id', 'id');
    }

}
