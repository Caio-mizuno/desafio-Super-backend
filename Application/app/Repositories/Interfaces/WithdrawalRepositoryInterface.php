<?php

namespace App\Repositories\Interfaces;

use App\Models\Withdrawal;

interface WithdrawalRepositoryInterface
{
    public function create(array $data): Withdrawal;
    public function updateStatus(Withdrawal $withdrawal, string $status, array $attributes = []): Withdrawal;
    public function findByExternalId(string $externalId): ?Withdrawal;
}

