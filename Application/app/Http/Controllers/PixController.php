<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePixRequest;
use App\Models\User;
use App\Services\PixService;

class PixController extends Controller
{
    public function __construct(private PixService $pixService)
    {
    }

    public function store(CreatePixRequest $request)
    {
        $user = User::findOrFail($request->integer('user_id'));
        $pix = $this->pixService->create($user, $request->validated());
        return $this->success($pix, 'Pix criado');
    }
}

