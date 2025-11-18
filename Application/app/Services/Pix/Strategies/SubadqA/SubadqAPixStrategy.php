<?php

namespace App\Services\Pix\Strategies\SubadqA;

use App\Helpers\LogHelper;
use App\Helpers\SubadqAHelper;
use App\Services\Pix\Strategies\PixGenerationStrategyInterface;

class SubadqAPixStrategy implements PixGenerationStrategyInterface
{
    public const CURRENCY_BRL = 'BRL';

    public function __construct(private SubadqAHelper $helper) {}

    public function createPix(array $data): array
    {
        $response = $this->helper->client($data)->post('/pix/create', [
            'merchant_id' => env('SUBADQA_MERCHANT_ID', 'm123'),
            'order_id' => $data['idempotency'],
            'amount' => (int) $data['amount'],
            'currency' => self::CURRENCY_BRL,
            'payer' => [
                'name' => $data['payer_name'],
                'cpf_cnpj' => $data['payer_document'],
            ],
            "expires_in" => env('SUBADQA_PIX_EXPIRES_IN', 3600),
        ]);

        LogHelper::save(6, 'PIX created', $data, $response->json());

        return [
            'transaction_id' => $response->json('transaction_id'),
            'location' => $response->json('location'),
            'qrcode' => $response->json('qrcode'),
            'expires_at' => $response->json('expires_at'),
            'status' => $response->json('status'),
        ];
    }

    public function createWithdraw(array $data): array
    {
        $response = $this->helper->client($data)->post('/withdraw', [
            'merchant_id' => env('SUBADQA_MERCHANT_ID', 'm123'),
            'account' => [
                'bank_code' => $data['bank_code'],
                'agencia' => $data['branch'],
                'conta' => $data['account_number'],
                'type' => $data['account_type'],
            ],
            'amount' => (float) $data['amount'],
            'transaction_id' => $data['transaction_id'],
        ]);

        LogHelper::save(7, 'Withdrawal created', $data, $response->json());

        return [
            'withdraw_id' => $response->json('withdraw_id'),
            'status' => $response->json('status'),
        ];
    }
}
