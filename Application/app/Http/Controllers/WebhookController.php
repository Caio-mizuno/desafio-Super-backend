<?php

namespace App\Http\Controllers;

use App\Models\Pix;
use App\Models\Withdrawal;
use App\Repositories\Interfaces\PixRepositoryInterface;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private PixRepositoryInterface $pixRepository,
        private WithdrawalRepositoryInterface $withdrawalRepository,
        private LogRepositoryInterface $logRepository
    ) {
    }

    public function pix(Request $request)
    {
        $payload = $request->all();

        $isSubadqA = isset($payload['event']) || ($payload['metadata']['source'] ?? null) === 'SubadqA';
        $isSubadqB = isset($payload['type']) || isset($payload['data']);

        if (!$isSubadqA && !$isSubadqB) {
            return $this->error('Formato de payload inválido', 422);
        }

        $externalId = $isSubadqA ? ($payload['pix_id'] ?? null) : ($payload['data']['id'] ?? null);
        if (!$externalId) {
            return $this->error('Identificador externo do PIX não encontrado', 422);
        }

        $pix = $this->pixRepository->findByExternalId($externalId);
        if (!$pix) {
            return $this->error('PIX não encontrado', 404);
        }

        $status = $isSubadqA ? ($payload['status'] ?? 'PENDING') : ($payload['data']['status'] ?? 'PENDING');
        $paymentDate = $isSubadqA ? ($payload['payment_date'] ?? null) : ($payload['data']['confirmed_at'] ?? null);
        $transactionId = $isSubadqA ? ($payload['transaction_id'] ?? null) : ($payload['data']['transaction_id'] ?? null);

        $attributes = ['payload' => $payload];
        if ($paymentDate) {
            $attributes['payment_date'] = $paymentDate;
        }
        if ($transactionId) {
            $attributes['transaction_id'] = $transactionId;
        }

        $updated = $this->pixRepository->updateStatus($pix, $status, $attributes);
        $this->logRepository->create(1, 'PIX webhook processed', [
            'pix_id' => $updated->id,
            'external_pix_id' => $externalId,
            'status' => $status,
            'provider' => $isSubadqA ? 'SubadqA' : 'SubadqB',
        ], Pix::class, $updated->id);

        return $this->success($updated, 'Webhook de PIX processado');
    }

    public function withdraw(Request $request)
    {
        $payload = $request->all();

        $isSubadqA = isset($payload['event']) || ($payload['metadata']['source'] ?? null) === 'SubadqA';
        $isSubadqB = isset($payload['type']) || isset($payload['data']);

        if (!$isSubadqA && !$isSubadqB) {
            return $this->error('Formato de payload inválido', 422);
        }

        $externalId = $isSubadqA ? ($payload['withdraw_id'] ?? null) : ($payload['data']['id'] ?? null);
        if (!$externalId) {
            return $this->error('Identificador externo do Saque não encontrado', 422);
        }

        $withdrawal = $this->withdrawalRepository->findByExternalId($externalId);
        if (!$withdrawal) {
            return $this->error('Saque não encontrado', 404);
        }

        $status = $isSubadqA ? ($payload['status'] ?? 'PENDING') : ($payload['data']['status'] ?? 'PENDING');
        $completedAt = $isSubadqA ? ($payload['completed_at'] ?? null) : ($payload['data']['processed_at'] ?? null);
        $transactionId = $isSubadqA ? ($payload['transaction_id'] ?? null) : ($payload['data']['transaction_id'] ?? null);

        $attributes = ['payload' => $payload];
        if ($completedAt) {
            $attributes['completed_at'] = $completedAt;
        }
        if ($transactionId) {
            $attributes['transaction_id'] = $transactionId;
        }

        $updated = $this->withdrawalRepository->updateStatus($withdrawal, $status, $attributes);
        $this->logRepository->create(2, 'Withdraw webhook processed', [
            'withdrawal_id' => $updated->id,
            'external_withdraw_id' => $externalId,
            'status' => $status,
            'provider' => $isSubadqA ? 'SubadqA' : 'SubadqB',
        ], Withdrawal::class, $updated->id);

        return $this->success($updated, 'Webhook de Saque processado');
    }
}