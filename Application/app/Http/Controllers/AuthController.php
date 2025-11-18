<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;


class AuthController extends Controller
{
    public function __construct(private AuthService $authService) {}

    #[OA\Post(
        path: '/api/auth/register',
        tags: ['Auth'],
        security: [['apiKeySecurity' => []]],
        parameters: [
            new OA\Parameter(name: 'security', in: 'header', required: true, schema: new OA\Schema(type: 'string', example: 'SECURITY_KEY_SAMPLE')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'subacquirer_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                    new OA\Property(property: 'subacquirer_id', type: 'integer'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Usuário registrado')
        ]
    )]

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());
        return $this->success($result, 'Usuário registrado');
    }

    #[OA\Post(
        path: '/api/auth/login',
        tags: ['Auth'],
        security: [['apiKeySecurity' => []]],
        parameters: [
            new OA\Parameter(name: 'security', in: 'header', required: true, schema: new OA\Schema(type: 'string', example: 'SECURITY_KEY_SAMPLE')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'SubadqA@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login realizado')
        ]
    )]
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());
        return $this->success($result, 'Login realizado');
    }

    #[OA\Post(
        path: '/api/logout',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logout realizado')
        ]
    )]

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        return $this->success(true, 'Logout realizado');
    }


    #[OA\Get(
        path: '/api/me',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Usuário autenticado')
        ]
    )]
    public function show(Request $request)
    {
        $user = $this->authService->show($request);
        return $this->success($user, 'Usuário autenticado');
    }
}
