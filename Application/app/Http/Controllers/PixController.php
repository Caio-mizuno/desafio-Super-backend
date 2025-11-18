<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePixRequest;
use App\Services\PixService;
use OpenApi\Attributes as OA;

class PixController extends Controller
{
    public function __construct(private PixService $pixService) {}

    #[OA\Post(
        path: '/api/pix',
        tags: ['Pix'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'x-mock-response-name', in: 'header', required: true, schema: new OA\Schema(type: 'string', example: 'SUCESSO_PIX ou ERRO_PIX')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['amount', 'idempotency'],
                properties: [
                    new OA\Property(property: 'amount', type: 'integer', minimum: 1),
                    new OA\Property(property: 'idempotency', type: 'string'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Pix criado')
        ]
    )]
    public function store(CreatePixRequest $request)
    {
        $user = auth()->user();
        $pix = $this->pixService->create($user, $request->validated());
        return $this->success($pix, 'Pix criado');
    }
}
