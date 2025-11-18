<?php

namespace App\Jobs;

use App\Constants\PixStatus;
use App\Models\Pix;
use App\Repositories\Interfaces\PixRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SimulatePixWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Pix $pix, public string $subacquirer) {}

    public function handle(PixRepositoryInterface $pixRepository, LogRepositoryInterface $logRepository): void
    {
        $payload = $this->subacquirer === 'SubadqA'
            ? [
                'event' => 'pix_payment_confirmed',
                'transaction_id' => $this->pix->transaction_id ?? uniqid(),
                'pix_id' => $this->pix->external_pix_id ?? ('PIX' . $this->pix->id),
                'status' => 'CONFIRMED',
                'amount' => (float) $this->pix->amount,
                'payer_name' => $this->pix->payer_name,
                'payer_cpf' => $this->pix->payer_document,
                'payment_date' => now()->toIso8601String(),
                'metadata' => ['source' => 'SubadqA', 'environment' => 'sandbox'],
            ]
            : [
                'type' => 'pix.status_update',
                'data' => [
                    'id' => $this->pix->external_pix_id ?? ('PX' . $this->pix->id),
                    'status' => 'PAID',
                    'value' => (float) $this->pix->amount,
                    'payer' => [
                        'name' => $this->pix->payer_name,
                        'document' => $this->pix->payer_document,
                    ],
                    'confirmed_at' => now()->toIso8601String(),
                ],
                'signature' => substr(sha1((string) $this->pix->id), 0, 12),
            ];

        $status = $this->subacquirer === 'SubadqA' ? 'CONFIRMED' : 'PAID';
        $pixRepository->updateStatus($this->pix, PixStatus::fromString($status), [
            'payment_date' => now(),
            'payload' => $payload,
        ]);
        $logRepository->create(1, 'PIX webhook processed', ['pix_id' => $this->pix->id, 'status' => $status], $payload, Pix::class, $this->pix->id);
    }
}
