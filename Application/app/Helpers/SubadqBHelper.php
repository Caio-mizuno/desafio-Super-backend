<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class SubadqBHelper
{
    public function client(array $data = []): PendingRequest
    {
        $headers = [
            'Accept' => 'application/json',
            'x-mock-response-name' => request()->header('x-mock-response-name') ?? 'SUCESSO_WD',
        ];
        return Http::baseUrl(env('SUBADQB_BASE_URL', 'https://ef8513c8-fd99-4081-8963-573cd135e133.mock.pstmn.io'))
            ->withHeaders($headers);
    }
}
