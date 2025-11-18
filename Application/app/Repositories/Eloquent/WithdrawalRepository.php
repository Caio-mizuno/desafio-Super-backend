<?php

namespace App\Repositories\Eloquent;

use App\Models\Withdrawal;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;

class WithdrawalRepository implements WithdrawalRepositoryInterface
{
    public function __construct(protected Withdrawal $model) {}

    public function create(array $data): Withdrawal
    {
        return $this->model->create($data);
    }

    public function updateStatus(Withdrawal $withdrawal, string $status, array $attributes = []): Withdrawal
    {
        $withdrawal->fill(array_merge($attributes, ['status' => $status]));
        $withdrawal->save();
        return $withdrawal;
    }

    public function findByExternalId(string $externalId): ?Withdrawal
    {
        return $this->model->where('external_withdraw_id', $externalId)->first();
    }
}
