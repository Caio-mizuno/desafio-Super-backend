<?php

namespace App\Helpers;

use App\Models\Log;

class LogHelper
{
    public static function save(int $id_tipo, string $message, array $context = [], array $response = []): Log
    {
        return Log::create([
            'id_tipo' => $id_tipo,
            'message' => $message,
            'context' => $context,
            'response' => $response,
        ]);
    }
}
