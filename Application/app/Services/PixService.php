<?php

namespace App\Services;

use App\Jobs\SimulatePixWebhook;
use App\Models\Pix;
use App\Models\User;
use App\Repositories\Interfaces\PixRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use Illuminate\Support\Facades\Http;

class PixService
{
    public function __construct(
        private PixRepositoryInterface $pixRepository,
        private LogRepositoryInterface $logRepository
    ) {
    }

    public function create(User $user, array $data): Pix
    {
        $pix = $this->pixRepository->create([
            'user_id' => $user->id,
            'status' => 'PENDING',
            'amount' => $data['amount'],
            'payer_name' => $data['payer_name'] ?? null,
            'payer_document' => $data['payer_document'] ?? null,
        ]);

        $baseUrl = $this->getBaseUrl($user->subacquirer);
        $headers = [];
        if (!empty($data['mock_header'])) {
            $headers['x-mock-response-name'] = $data['mock_header'];
        }
        $response = Http::baseUrl($baseUrl)->withHeaders($headers)->post('/pix/create', [
            'amount' => (float) $pix->amount,
            'payer_name' => $pix->payer_name,
            'payer_document' => $pix->payer_document,
        ]);

        $externalId = $response->json('pix_id') ?? $response->json('data.id');
        $transactionId = $response->json('transaction_id') ?? $response->json('data.transaction_id');
        $this->pixRepository->updateStatus($pix, 'PROCESSING', [
            'external_pix_id' => $externalId,
            'transaction_id' => $transactionId,
            'payload' => $response->json(),
        ]);

        SimulatePixWebhook::dispatch($pix, $user->subacquirer);
        $this->logRepository->create(1, 'PIX created', ['pix_id' => $pix->id, 'subacquirer' => $user->subacquirer]);
        return $pix;
    }

    private function getBaseUrl(string $subacquirer): string
    {
        $map = [
            'SubadqA' => env('SUBADQA_BASE_URL', 'https://0acdeaee-1729-4d55-80eb-d54a125e5e18.mock.pstmn.io'),
            'SubadqB' => env('SUBADQB_BASE_URL', 'https://ef8513c8-fd99-4081-8963-573cd135e133.mock.pstmn.io'),
        ];
        return $map[$subacquirer] ?? $map['SubadqA'];
    }
}

