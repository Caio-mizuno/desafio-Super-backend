<?php

namespace App\Repositories;

use App\Models\Log;

interface LogRepositoryInterface
{
    public function create(int $id_tipo, string $message, array $context = [], ?string $relatedType = null, ?int $relatedId = null): Log;
}

