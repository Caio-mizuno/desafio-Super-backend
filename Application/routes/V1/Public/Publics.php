<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api'])->group(function () {
    Route::get('/saude-server-check', function () {
        return response('OK', 200);
    });
});



Route::middleware(['api', 'security'])->prefix('/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
