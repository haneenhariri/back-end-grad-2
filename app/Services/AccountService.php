<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;

class AccountService
{
    public function chargeAccount(array $data){
        $account = Account::query()->firstOrCreate(['user_id' => $data['user_id']], ['balance' => $data['amount']]);
        if (!$account->wasRecentlyCreated) {
            $account->update(['balance' => ($account->balance + $data['amount'])]);
        }
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
