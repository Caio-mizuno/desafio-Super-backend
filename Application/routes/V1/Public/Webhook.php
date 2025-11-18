<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    Route::post('/webhooks/pix', [WebhookController::class, 'pix']);
    Route::post('/webhooks/withdraw', [WebhookController::class, 'withdraw']);
});
