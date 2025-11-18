<?php

namespace App\Repositories\Eloquent;

use App\Models\Log;
use App\Repositories\Interfaces\LogRepositoryInterface;

class LogRepository implements LogRepositoryInterface
{
    public function __construct(private Log $logModel) {}
    public function create(int $id_tipo, string $message, array $context = [], array $response = [], ?string $relatedType = null, ?int $relatedId = null): Log
    {
        return $this->logModel->create([
            'id_tipo' => $id_tipo,
            'message' => $message,
            'context' => $context,
            'response' => $response,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
        ]);
    }
}
