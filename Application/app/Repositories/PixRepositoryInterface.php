<?php

namespace App\Repositories;

use App\Models\Pix;

interface PixRepositoryInterface
{
    public function create(array $data): Pix;
    public function updateStatus(Pix $pix, string $status, array $attributes = []): Pix;
    public function findByExternalId(string $externalId): ?Pix;
}

