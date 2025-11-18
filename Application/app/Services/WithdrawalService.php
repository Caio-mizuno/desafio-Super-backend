<?php

namespace App\Services;

use App\Constants\PixStatus;
use App\Exceptions\BasicException;
use App\Helpers\LogHelper;
use App\Models\User;
use App\Models\Withdrawal;
use App\Repositories\Interfaces\WithdrawalRepositoryInterface;
use App\Repositories\Interfaces\LogRepositoryInterface;
use App\Services\Pix\PixStrategyResolver;
use Carbon\Carbon;

class WithdrawalService
{
    public function __construct(
        private WithdrawalRepositoryInterface $withdrawalRepository,
        private PixStrategyResolver $resolver

    ) {}

    public function create(User $user, array $data): Withdrawal
    {
        $bankAccount = $user->bankAccounts()->find($data['bank_account']);
        if (!$bankAccount) {
            throw new BasicException('Bank account not found', 404);
        }

        $pix = $user->pixes()->find($data['pix_id']);
        if (!$pix) {
            throw new BasicException('Pix not found', 404);
        }

        $strategy = $this->resolver->resolve($user->subacquirer->name);
        $result = $strategy->createWithdraw([
            'mock_header' => $result['mock_header'] ?? 'SUCESSO_WD',
            'account_number' => $bankAccount->account_number,
            'account_type' => $bankAccount->account_type,
            'bank_code' => $bankAccount->bank_code,
            'branch' => $bankAccount->branch,
            'transaction_id' => $pix->transaction_id,
            // Convert to cents, remove decimal point, secure against float precision errors
            'amount' =>  (int) number_format($data['amount'], 2, '', ''),
        ]);

        $withdrawal = $this->withdrawalRepository->create([
            'user_id' => $user->id,
            'subacquirer_id' => $user->subacquirer_id,
            'status' => PixStatus::fromString($result['status']),
            'amount' => $data['amount'],
            'payer_name' => $data['payer_name'] ?? null,
            'payer_document' => $data['payer_document'] ?? null,
            'external_pix_id' => $result['external_pix_id'] ?? null,
            'transaction_id' => $result['transaction_id'] ?? null,
            'requested_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        LogHelper::save(
            7,
            'Withdrawal created',
            $data,
            $withdrawal->toArray()
        );
        return $withdrawal;
    }
}
