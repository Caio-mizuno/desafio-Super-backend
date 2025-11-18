<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'subacquirer' => $data['subacquirer'],
        ]);
        $token = $user->createToken('api')->plainTextToken;
        return $this->success(['user' => $user, 'token' => $token], 'Usuário registrado');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return $this->error('Credenciais inválidas', 401);
        }
        $token = $user->createToken('api')->plainTextToken;
        return $this->success(['user' => $user, 'token' => $token], 'Login realizado');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logout realizado');
    }

    public function show(Request $request)
    {
        return $this->success($request->user(), 'Usuário autenticado');
    }
}