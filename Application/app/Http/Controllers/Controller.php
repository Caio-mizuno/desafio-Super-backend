<?php

namespace App\Http\Controllers;

use App\Http\Traits\ResponseTrait;
use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Super Backend API')]
#[OA\Server(url: L5_SWAGGER_CONST_HOST, description: 'API Server')]
#[OA\SecurityScheme(securityScheme: 'bearerAuth', type: 'http', scheme: 'bearer', bearerFormat: 'JWT')]
#[OA\SecurityScheme(securityScheme: 'apiKeySecurity', type: 'apiKey', name: 'security', in: 'header')]
abstract class Controller
{
    use ResponseTrait;
}
