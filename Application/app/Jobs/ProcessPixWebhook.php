<?php

namespace App\Jobs;

use App\Constants\PixStatus;
use App\Models\Pix;
use App\Repositories\Interfaces\PixRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProcessPixWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public array $payload) {}

    public function handle(PixRepositoryInterface $pixRepository, LogRepositoryInterface $logRepository): void
    {

        try {
            $isSubadqA = isset($this->payload['event']) || ($this->payload['metadata']['source'] ?? null) === 'SubadqA';
            $isSubadqB = isset($this->payload['type']) || isset($this->payload['data']);

            if (!$isSubadqA && !$isSubadqB) {
                return;
            }

            $externalId = $isSubadqA ? ($this->payload['transaction_id'] ?? null) : ($this->payload['data']['id'] ?? null);
            if (!$externalId) {
                return;
            }

            $pix = $pixRepository->findByExternalId($externalId);
            if (!$pix) {
                return;
            }

            $status = $isSubadqA ? ($this->payload['status'] ?? 'PENDING') : ($this->payload['data']['status'] ?? 'PENDING');
            $paymentDate = $isSubadqA ? ($this->payload['payment_date'] ?? null) : ($this->payload['data']['confirmed_at'] ?? null);
            $transactionId = $isSubadqA ? ($this->payload['transaction_id'] ?? null) : ($this->payload['data']['transaction_id'] ?? null);

            $attributes = ['payload' => $this->payload];
            if ($paymentDate) {
                $attributes['payment_date'] = Carbon::parse($paymentDate)->format('Y-m-d H:i:s');
            }
            if ($transactionId) {
                $attributes['transaction_id'] = $transactionId;
            }


            $updated = $pixRepository->updateStatus(
                $pix,
                PixStatus::fromString($status),
                $attributes
            );

            $logRepository->create(
                1,
                'PIX webhook processed',
                [
                    'pix_id' => $updated->id,
                    'transaction_id' => $externalId,
                    'status' => $status,
                    'provider' => $isSubadqA ? 'SubadqA' : 'SubadqB',
                ],
                $updated->toArray(),
                Pix::class,
                $updated->id
            );
        } catch (Throwable $e) {
            $logRepository->create(
                1,
                'PIX webhook error',
                [
                    'pix_id' => $pix->id,
                    'transaction_id' => $externalId,
                    'status' => $status,
                    'provider' => $isSubadqA ? 'SubadqA' : 'SubadqB',
                ],
                ['error' => $e->getMessage()],
                Pix::class,
                $pix->id
            );
        }
    }
}
