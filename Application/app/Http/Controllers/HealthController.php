<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/saude-server-check',
        tags: ['Health'],
        responses: [
            new OA\Response(response: 200, description: 'OK')
        ]
    )]
    public function index()
    {
        return response('OK', 200);
    }
}