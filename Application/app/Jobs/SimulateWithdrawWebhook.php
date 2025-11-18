<?php

namespace App\Jobs;

use App\Models\Withdrawal;
use App\Repositories\WithdrawalRepositoryInterface;
use App\Repositories\LogRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SimulateWithdrawWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Withdrawal $withdrawal, public string $subacquirer)
    {
    }

    public function handle(WithdrawalRepositoryInterface $withdrawalRepository, LogRepositoryInterface $logRepository): void
    {
        $payload = $this->subacquirer === 'SubadqA'
            ? [
                'event' => 'withdraw_completed',
                'withdraw_id' => $this->withdrawal->external_withdraw_id ?? ('WD' . $this->withdrawal->id),
                'transaction_id' => $this->withdrawal->transaction_id ?? uniqid('T'),
                'status' => 'SUCCESS',
                'amount' => (float) $this->withdrawal->amount,
                'requested_at' => now()->subMinutes(2)->toIso8601String(),
                'completed_at' => now()->toIso8601String(),
                'metadata' => ['source' => 'SubadqA'],
            ]
            : [
                'type' => 'withdraw.status_update',
                'data' => [
                    'id' => $this->withdrawal->external_withdraw_id ?? ('WDX' . $this->withdrawal->id),
                    'status' => 'DONE',
                    'amount' => (float) $this->withdrawal->amount,
                    'processed_at' => now()->toIso8601String(),
                ],
                'signature' => substr(sha1((string) $this->withdrawal->id), 0, 16),
            ];

        $status = $this->subacquirer === 'SubadqA' ? 'SUCCESS' : 'DONE';
        $withdrawalRepository->updateStatus($this->withdrawal, $status, [
            'completed_at' => now(),
            'payload' => $payload,
        ]);
        $logRepository->create(2, 'Withdraw webhook processed', ['withdrawal_id' => $this->withdrawal->id, 'status' => $status], Withdrawal::class, $this->withdrawal->id);
    }
}

