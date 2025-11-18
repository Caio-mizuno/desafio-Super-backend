<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;


class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());
        return $this->success($result, 'Usuário registrado');
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        return $this->success($result, 'Login realizado');
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        return $this->success(true, 'Logout realizado');
    }

    public function show(Request $request)
    {
        $user = $this->authService->show($request);
        return $this->success($user, 'Usuário autenticado');
    }
}
