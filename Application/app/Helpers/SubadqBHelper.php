<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class SubadqBHelper
{
    public function client(array $data = []): PendingRequest
    {
        $headers = ['Accept' => 'application/json'];
        if (!empty($data['mock_header'])) {
            $headers['x-mock-response-name'] = $data['mock_header'];
        }
        return Http::baseUrl(env('SUBADQB_BASE_URL', 'https://ef8513c8-fd99-4081-8963-573cd135e133.mock.pstmn.io'))
            ->withHeaders($headers);
    }
}
