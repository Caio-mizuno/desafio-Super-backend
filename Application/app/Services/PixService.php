<?php

namespace App\Services;

use App\Models\Pix;
use App\Models\User;
use App\Repositories\Interfaces\PixRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Constants\PixStatus;
use App\Helpers\LogHelper;
use App\Services\Pix\PixStrategyResolver;
use Illuminate\Support\Facades\Bus;

class PixService
{
    public function __construct(
        private PixRepositoryInterface $pixRepository,
        private PixStrategyResolver $resolver
    ) {}

    public function create(User $user, array $data): Pix
    {
        // Convert to cents, remove decimal point, secure against float precision errors
        $data['amount'] = (int) number_format($data['amount'], 2, '', '');

        $pixExist = $user->pixes()->where('expires_at', '>', now())->first();

        if ($pixExist) {
            throw new \Exception('You have an active PIX payment. Please, wait for it to expire.');
        }

        $strategy = $this->resolver->resolve($user->subacquirer->name);
        $result = $strategy->createPix([
            'amount' => $data['amount'],
            'idempotency' => $data['idempotency'],
            'payer_name' => $user->name,
            'payer_document' => $user->cpf_cnpj,
        ]);

        $pix = $this->pixRepository->create([
            'mock_header' => $result['mock_header'] ?? 'SUCESSO_PIX',
            'user_id' => $user->id,
            'idempotency' => $data['idempotency'],
            'subacquirer_id' => $user->subacquirer_id,
            'status' => PixStatus::fromString($result['status']),
            'amount' => $data['amount'],
            'payer_name' => $user->name,
            'payer_document' => $user->cpf_cnpj,
            'transaction_id' => $result['transaction_id'] ?? null,
            'location' => $result['location'] ?? null,
            'qrcode' => $result['qrcode'] ?? null,
            'expires_at' => $result['expires_at'] ?? null,
        ]);

        LogHelper::save(
            6,
            'PIX created',
            $data,
            ['pix_id' => $pix->id, 'subacquirer' => $user->subacquirer->name]
        );

        $job = Bus::dispatchSync(
            new \App\Jobs\SimulatePixWebhook($pix, $user->subacquirer->name)
        );
        unset($job);
        return $pix;
    }
}
