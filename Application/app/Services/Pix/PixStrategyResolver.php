<?php

namespace App\Services\Pix;

use App\Services\Pix\Strategies\PixGenerationStrategyInterface;
use App\Services\Pix\Strategies\SubadqA\SubadqAPixStrategy;
use App\Services\Pix\Strategies\SubadqB\SubadqBPixStrategy;
use App\Helpers\SubadqAHelper;
use App\Helpers\SubadqBHelper;

class PixStrategyResolver
{
    public function resolve(string $subacquirer): PixGenerationStrategyInterface
    {
        return match ($subacquirer) {
            'SubadqB' => new SubadqBPixStrategy(new SubadqBHelper()),
            default => new SubadqAPixStrategy(new SubadqAHelper()),
        };
    }
}
