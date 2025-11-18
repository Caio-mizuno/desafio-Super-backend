<?php

namespace App\Services\Pix\Strategies;

use App\Models\Pix;

interface PixGenerationStrategyInterface
{
    public function createPix(array $data): array;
    public function createWithdraw(array $data): array;
}
