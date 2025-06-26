<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\ChargeRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AccountService;

class AccountController extends Controller
{
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function chargeAccount(ChargeRequest $request)
    {
        try {
            $account = $this->accountService->chargeAccount($request->validated());
            return self::success($account);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Account charge error', [
                'message' => $e->getMessage(),
                'data' => $request->validated()
            ]);
            return self::error(null, 'Failed to charge account: ' . $e->getMessage());
        }
    }


public function getPayments()
{
    $user = auth()->user();

    if (!$user) {
        throw new \Exception("User not authenticated");
    }

    $account = $user->account;

    if (!$account) {
        throw new \Exception("User account not found");
    }

    $accountId = $account->id;

    $query = Transaction::with(['course', 'account.user', 'intendedAccount.user']);

    if ($user->hasRole('student')) {
        // الطالب: يعرض فقط معاملاته لكن لا يرى المحولة إلى حساب المنصة (id = 1)
        $query->where('account_id', $accountId)
              ->where('intended_account_id', '!=', 1);
    } elseif ($user->hasRole('instructor')) {
        // المدرس: يرى فقط ما حُوّل له
        $query->where('intended_account_id', $accountId);
    } elseif ($user->hasRole('admin')) {
        // الأدمن: يرى ما حُوّل إلى حساب المنصة (id = 1)
        $query->where('intended_account_id', 1)
              ->with('account.user');
    } else {
        // أي دور آخر (احتياطي)
        $query->where('account_id', $accountId)
              ->with('intendedAccount.user');
    }

    $payments = $query->latest()->get();

    return self::success(TransactionResource::collection($payments));
}

    public function allPayment()
    {
        $transactions = Transaction::with(['course', 'account', 'intendedAccount'])->get();
        return self::success(TransactionResource::collection($transactions));
    }

    public function getPaymentsForUser(User $user)
    {
        $payments = $this->accountService->getPaymentsForUser($user);
        return self::success(TransactionResource::collection($payments->with('course')->latest()->get()));
    }
    
    

}
