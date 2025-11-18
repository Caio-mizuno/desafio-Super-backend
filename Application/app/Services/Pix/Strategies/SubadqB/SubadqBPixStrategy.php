<?php

namespace App\Services\Pix\Strategies\SubadqB;

use App\Exceptions\BasicException;
use App\Helpers\SubadqBHelper;
use App\Services\Pix\Strategies\PixGenerationStrategyInterface;

class SubadqBPixStrategy implements PixGenerationStrategyInterface
{
    public function __construct(private SubadqBHelper $helper) {}

    public function createPix(array $data): array
    {
        $response = $this->helper->client($data)->post('/pix/create', [
            'seller_id' => env('SUBADQB_MERCHANT_ID', 'm123'),
            'order' => $data['idempotency'],
            'amount' => (int) $data['amount'],
            'payer' => [
                'name' => $data['payer_name'],
                'cpf_cnpj' => $data['payer_document'],
            ],
            "expires_in" => env('SUBADQB_PIX_EXPIRES_IN', 3600),
        ]);
        if (!$response->successful()) {
            throw new BasicException($response->json('message'), $response->status());
        }
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
            'seller_id' => env('SUBADQB_MERCHANT_ID', 'm123'),
            'account' => [
                'bank_code' => $data['bank_code'],
                'agencia' => $data['branch'],
                'conta' => $data['account_number'],
                'type' => $data['account_type'],
            ],
            'amount' => (float) $data['amount'],
            'transaction_id' => $data['transaction_id'],
        ]);
        if (!$response->successful()) {
            throw new BasicException($response->json('error'), $response->status());
        }
        return [
            'withdraw_id' => $response->json('withdraw_id'),
            'status' => $response->json('status'),
        ];
    }
}
