<?php

namespace App\Services;

use App\Jobs\SimulateWithdrawWebhook;
use App\Models\User;
use App\Models\Withdrawal;
use App\Repositories\WithdrawalRepositoryInterface;
use App\Repositories\LogRepositoryInterface;
use Illuminate\Support\Facades\Http;

class WithdrawalService
{
    public function __construct(
        private WithdrawalRepositoryInterface $withdrawalRepository,
        private LogRepositoryInterface $logRepository
    ) {
    }

    public function create(User $user, array $data): Withdrawal
    {
        $withdrawal = $this->withdrawalRepository->create([
            'user_id' => $user->id,
            'status' => 'PENDING',
            'amount' => $data['amount'],
            'requested_at' => now(),
        ]);

        $baseUrl = $this->getBaseUrl($user->subacquirer);
        $headers = [];
        if (!empty($data['mock_header'])) {
            $headers['x-mock-response-name'] = $data['mock_header'];
        }
        $response = Http::baseUrl($baseUrl)->withHeaders($headers)->post('/withdraw', [
            'amount' => (float) $withdrawal->amount,
        ]);

        $externalId = $response->json('withdraw_id') ?? $response->json('data.id');
        $transactionId = $response->json('transaction_id') ?? $response->json('data.transaction_id');
        $this->withdrawalRepository->updateStatus($withdrawal, 'PROCESSING', [
            'external_withdraw_id' => $externalId,
            'transaction_id' => $transactionId,
            'payload' => $response->json(),
        ]);

        SimulateWithdrawWebhook::dispatch($withdrawal, $user->subacquirer);
        $this->logRepository->create(2, 'Withdraw created', ['withdrawal_id' => $withdrawal->id, 'subacquirer' => $user->subacquirer]);
        return $withdrawal;
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

