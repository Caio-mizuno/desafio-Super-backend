<?php

namespace App\Repositories\Eloquent;

use App\Models\Withdrawal;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;

class WithdrawalRepository implements WithdrawalRepositoryInterface
{
    public function create(array $data): Withdrawal
    {
        return Withdrawal::create($data);
    }

    public function updateStatus(Withdrawal $withdrawal, string $status, array $attributes = []): Withdrawal
    {
        $withdrawal->fill(array_merge($attributes, ['status' => $status]));
        $withdrawal->save();
        return $withdrawal;
    }

    public function findByExternalId(string $externalId): ?Withdrawal
    {
        return Withdrawal::where('external_withdraw_id', $externalId)->first();
    }
}

