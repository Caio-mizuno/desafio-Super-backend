<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWithdrawRequest;
use App\Models\User;
use App\Services\WithdrawalService;

class WithdrawController extends Controller
{
    public function __construct(private WithdrawalService $withdrawalService)
    {
    }

    public function store(CreateWithdrawRequest $request)
    {
        $user = auth()->user();
        $withdrawal = $this->withdrawalService->create($user, $request->validated());
        return $this->success($withdrawal, 'Saque criado');
    }
}

