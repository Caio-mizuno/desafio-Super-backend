<?php

namespace App\Services;

use App\Exceptions\BasicException;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'subacquirer_id' => $data['subacquirer_id'],
        ]);
        $token = $user->createToken('api')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    public function login(array $data): ?array
    {
        $user = $this->userRepository->findByEmail($data['email']);
        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw new BasicException('Credenciais inválidas', 400);
        }
        $token = $user->createToken('api')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    public function logout(Request $request): void
    {
        $request->user()->currentAccessToken()->delete();
    }

    public function show(Request $request)
    {
        return $request->user();
    }
}
