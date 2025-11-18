<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use OpenApi\Attributes as OA;

class WebhookController extends Controller
{
    public function __construct() {}

    #[OA\Post(
        path: '/api/webhooks/pix',
        tags: ['Webhooks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object')
        ),
        responses: [
            new OA\Response(response: 202, description: 'Webhook de PIX enfileirado')
        ]
    )]
    public function pix(Request $request)
    {
        $payload = $request->all();
        $job = Bus::dispatch(
            new \App\Jobs\ProcessPixWebhook($payload)
        );
        unset($job);
        return $this->success(['queued' => true], 'Webhook de PIX enfileirado', 202);
    }

    #[OA\Post(
        path: '/api/webhooks/withdraw',
        tags: ['Webhooks'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(type: 'object')
        ),
        responses: [
            new OA\Response(response: 202, description: 'Webhook de Saque enfileirado')
        ]
    )]
    public function withdraw(Request $request)
    {
        $payload = $request->all();
        $job = Bus::dispatch(
            new \App\Jobs\ProcessWithdrawWebhook($payload)
        );
        unset($job);
        return $this->success(['queued' => true], 'Webhook de Saque enfileirado', 202);
    }
}
