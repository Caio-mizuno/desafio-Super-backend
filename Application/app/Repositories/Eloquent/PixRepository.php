<?php

namespace App\Repositories\Eloquent;

use App\Models\Pix;
use App\Repositories\Interfaces\PixRepositoryInterface;

class PixRepository implements PixRepositoryInterface
{
    public function __construct(protected Pix $model)
    {
    }

    public function create(array $data): Pix
    {
        return $this->model->create($data);
    }

    public function updateStatus(Pix $pix, string $status, array $attributes = []): Pix
    {
        $pix->fill(array_merge($attributes, ['status' => $status]));
        $pix->save();
        return $pix;
    }

    public function findByExternalId(string $transactionId): ?Pix
    {
        return $this->model->where('transaction_id', $transactionId)->first();
    }
}

