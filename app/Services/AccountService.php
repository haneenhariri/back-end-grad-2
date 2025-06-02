<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;

class AccountService
{
        public function chargeAccount(array $data)
    {
        // تحقق من وجود المستخدم أولاً
        $user = User::find($data['user_id']);
        if (!$user) {
            throw new \Exception("User not found");
        }

        // الحصول على حساب المستخدم
        $account = $user->account;
        if (!$account) {
            throw new \Exception("User account not found");
        }

        // تحديث الرصيد
        $account->balance += $data['amount'];
        $account->save();

        // لا نقوم بإنشاء سجل في جدول transactions
        // نكتفي بتحديث رصيد الحساب فقط

        return $account;
    }

    public function getPayments(){
        $user = auth()->user();
        $accountId = $user->account->id;

        $payments = match (true) {
            $user->hasRole('instructor') => Transaction::where('intended_account_id', $accountId)->with('account.user'),
            $user->hasRole('admin') => Transaction::where('intended_account_id', Account::find(1)->id)->with('account.user'),
            default => Transaction::where('account_id', $accountId)->with('intendedAccount.user')
        };
        return $payments->with('course')->latest()->get();
    }

    public function getPaymentsForUser(User $user){
        $accountId = $user->account->id;
        $payments = match (true) {
            $user->hasRole('student') => Transaction::where('account_id', $accountId)->with('intendedAccount.user'),
            $user->hasRole('instructor') => Transaction::where('intended_account_id', $accountId)->with('account.user'),
            $user->hasRole('admin') => Transaction::where('intended_account_id', Account::find(1)->id)->with('account.user'),
            default => Transaction::query()
        };
        return $payments;
    }
}
