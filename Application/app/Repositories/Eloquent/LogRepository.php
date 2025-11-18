<?php

namespace App\Repositories\Eloquent;

use App\Models\Log;
use App\Repositories\LogRepositoryInterface;

class LogRepository implements LogRepositoryInterface
{
    public function create(int $id_tipo, string $message, array $context = [], ?string $relatedType = null, ?int $relatedId = null): Log
    {
        return Log::create([
            'id_tipo' => $id_tipo,
            'message' => $message,
            'context' => $context,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);
    }
}

