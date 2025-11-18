<?php

namespace App\Repositories\Eloquent;

use App\Models\Pix;
use App\Repositories\PixRepositoryInterface;

class PixRepository implements PixRepositoryInterface
{
    public function create(array $data): Pix
    {
        return Pix::create($data);
    }

    public function updateStatus(Pix $pix, string $status, array $attributes = []): Pix
    {
        $pix->fill(array_merge($attributes, ['status' => $status]));
        $pix->save();
        return $pix;
    }

    public function findByExternalId(string $externalId): ?Pix
    {
        return Pix::where('external_pix_id', $externalId)->first();
    }
}

