<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateWithdrawRequest;
use App\Models\User;
use App\Services\WithdrawalService;
use OpenApi\Attributes as OA;

class WithdrawController extends Controller
{
    public function __construct(private WithdrawalService $withdrawalService) {}
    #[OA\Post(
        path: '/api/withdraw',
        tags: ['Withdraw'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'x-mock-response-name', in: 'header', required: true, schema: new OA\Schema(type: 'string', example: 'SUCESSO_WD ou ERROW_WD')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['bank_account', 'pix_id', 'amount'],
                properties: [
                    new OA\Property(property: 'bank_account', type: 'integer'),
                    new OA\Property(property: 'pix_id', type: 'integer'),
                    new OA\Property(property: 'amount', type: 'integer', minimum: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Saque criado')
        ]
    )]
    public function store(CreateWithdrawRequest $request)
    {
        $user = auth()->user();
        $withdrawal = $this->withdrawalService->create($user, $request->validated());
        return $this->success($withdrawal, 'Saque criado');
    }
}
