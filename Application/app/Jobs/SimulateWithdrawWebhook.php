<?php

namespace App\Jobs;

use App\Constants\PixStatus;
use App\Models\Withdrawal;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SimulateWithdrawWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Withdrawal $withdrawal, public string $subacquirer) {}

    public function handle(WithdrawalRepositoryInterface $withdrawalRepository, LogRepositoryInterface $logRepository): void
    {
        $payload = $this->subacquirer === 'SubadqA'
            ? [
                'event' => 'withdraw_completed',
                'withdraw_id' => $this->withdrawal->external_withdraw_id ?? ('WD' . $this->withdrawal->id),
                'transaction_id' => $this->withdrawal->transaction_id ?? uniqid('T'),
                'status' => 'SUCCESS',
                'amount' => (float) $this->withdrawal->amount,
                'requested_at' => Carbon::now()->subMinutes(2)->format('Y-m-d H:i:s'),
                'completed_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'metadata' => ['source' => 'SubadqA'],
            ]
            : [
                'type' => 'withdraw.status_update',
                'data' => [
                    'id' => $this->withdrawal->external_withdraw_id ?? ('WDX' . $this->withdrawal->id),
                    'status' => 'DONE',
                    'amount' => (float) $this->withdrawal->amount,
                    'processed_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ],
                'signature' => substr(sha1((string) $this->withdrawal->id), 0, 16),
            ];

        $status = $this->subacquirer === 'SubadqA' ? 'SUCCESS' : 'DONE';
        $withdrawalRepository->updateStatus($this->withdrawal, PixStatus::fromString($status), [
            'completed_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        $logRepository->create(
            2,
            'Withdraw webhook processed',
            ['withdrawal_id' => $this->withdrawal->id, 'status' => $status],
            $payload,
            Withdrawal::class,
            $this->withdrawal->id
        );
    }
}
