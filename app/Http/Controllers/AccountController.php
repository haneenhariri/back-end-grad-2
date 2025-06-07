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
    $accountId = $user->account->id;
    if (!$user) {
        throw new \Exception("User not authenticated");
    }

    $account = $user->account;
    if (!$account) {
        throw new \Exception("User account not found");
    }
    // إذا كان الطالب، لا نرجع معاملات فيها intended_account_id = 1
    $query = Transaction::with(['course', 'account', 'intendedAccount']);

    if ($user && $user->hasRole('student')) {
        $query->where('intended_account_id', '!=', 1);
    }
        $payments = match (true) {
            $user->hasRole('instructor') => Transaction::where('intended_account_id', $accountId),
            $user->hasRole('admin') => Transaction::where('intended_account_id', Account::find(1)->id)->with('account.user'),
            default => Transaction::where('account_id', $accountId)->with('intendedAccount.user')
        };

        $payments = $payments
        ->with(['course', 'account.user', 'intendedAccount.user'])
        ->latest()
        ->get();
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
