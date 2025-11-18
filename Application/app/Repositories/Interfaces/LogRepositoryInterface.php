<?php

namespace App\Repositories\Interfaces;

use App\Models\Log;

interface LogRepositoryInterface
{
    public function create(int $id_tipo, string $message, array $context = [], array $response = [], ?string $relatedType = null, ?int $relatedId = null): Log;
}

