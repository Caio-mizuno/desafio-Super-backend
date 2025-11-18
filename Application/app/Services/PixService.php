<?php

namespace App\Services;

use App\Exceptions\BasicException;
use App\Jobs\SimulatePixWebhook;
use App\Models\Pix;
use App\Models\User;
use App\Repositories\Interfaces\PixRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Constants\PixStatus;
use App\Services\Pix\PixStrategyResolver;

class PixService
{
    public function __construct(
        private PixRepositoryInterface $pixRepository,
        private LogRepositoryInterface $logRepository,
        private PixStrategyResolver $resolver
    ) {}

    public function create(User $user, array $data): Pix
    {
        // Convert to cents, remove decimal point, secure against float precision errors
        $data['amount'] = (int) number_format($data['amount'], 2, '', '');

        $strategy = $this->resolver->resolve($user->subacquirer->name);
        $result = $strategy->createPix($data);

        $pix = $this->pixRepository->create([
            'mock_header' => $result['mock_header'] ?? 'SUCESSO_PIX',
            'user_id' => $user->id,
            'subacquirer_id' => $user->subacquirer_id,
            'status' => PixStatus::fromString($result['status']),
            'amount' => $data['amount'],
            'payer_name' => $data['payer_name'] ?? null,
            'payer_document' => $data['payer_document'] ?? null,
            'external_pix_id' => $result['external_pix_id'] ?? null,
            'transaction_id' => $result['transaction_id'] ?? null,
            'location' => $result['location'] ?? null,
            'qrcode' => $result['qrcode'] ?? null,
            'expires_at' => $result['expires_at'] ?? null,
        ]);

        $this->logRepository->create(1, 'PIX created', ['pix_id' => $pix->id, 'subacquirer' => $user->subacquirer->name]);
        return $pix;
    }
}
