<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class SubadqAHelper
{
    public function client(array $data = []): PendingRequest
    {
        $headers = ['Accept' => 'application/json'];
        if (!empty($data['mock_header'])) {
            $headers['x-mock-response-name'] = $data['mock_header'];
        }
        return Http::baseUrl(env('SUBADQA_BASE_URL', 'https://0acdeaee-1729-4d55-80eb-d54a125e5e18.mock.pstmn.io'))
            ->withHeaders($headers);
    }
}
