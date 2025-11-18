<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;

class WebhookController extends Controller
{
    public function __construct() {}

    public function pix(Request $request)
    {
        $payload = $request->all();
        $job = Bus::dispatchSync(
            new \App\Jobs\ProcessPixWebhook($payload)
        );
        unset($job);
        return $this->success(['queued' => true], 'Webhook de PIX enfileirado', 202);
    }

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
