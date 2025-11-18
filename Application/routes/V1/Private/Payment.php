<?php

use App\Http\Controllers\PixController;
use App\Http\Controllers\WithdrawController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'security'])->group(function () {
    Route::post('/pix', [PixController::class, 'store']);
    Route::post('/withdraw', [WithdrawController::class, 'store']);
});

