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
        $payments = $this->accountService->getPayments();
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
