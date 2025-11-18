<?php

namespace App\Jobs;

use App\Constants\PixStatus;
use App\Models\Withdrawal;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWithdrawWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload) {}

    public function handle(WithdrawalRepositoryInterface $withdrawalRepository, LogRepositoryInterface $logRepository): void
    {
        $isSubadqA = isset($this->payload['event']) || ($this->payload['metadata']['source'] ?? null) === 'SubadqA';
        $isSubadqB = isset($this->payload['type']) || isset($this->payload['data']);

        if (!$isSubadqA && !$isSubadqB) {
            return;
        }

        $externalId = $isSubadqA ? ($this->payload['withdraw_id'] ?? null) : ($this->payload['data']['id'] ?? null);
        if (!$externalId) {
            return;
        }

        $withdrawal = $withdrawalRepository->findByExternalId($externalId);
        if (!$withdrawal) {
            return;
        }

        $status = $isSubadqA ? ($this->payload['status'] ?? 'PENDING') : ($this->payload['data']['status'] ?? 'PENDING');
        $completedAt = $isSubadqA ? ($this->payload['completed_at'] ?? null) : ($this->payload['data']['processed_at'] ?? null);

        $attributes = [];
        if ($completedAt) {
            $attributes['completed_at'] = $completedAt;
        }

        $updated = $withdrawalRepository->updateStatus($withdrawal, PixStatus::fromString($status), $attributes);
        $logRepository->create(2, 'Withdraw webhook processed', [
            'withdrawal_id' => $updated->id,
            'external_withdraw_id' => $externalId,
            'status' => $status,
            'provider' => $isSubadqA ? 'SubadqA' : 'SubadqB',
        ], $updated->toArray(), Withdrawal::class, $updated->id);
    }
}
