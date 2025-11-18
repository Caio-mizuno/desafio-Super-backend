<?php

namespace App\Constants;

class PixStatus
{
    public const PENDING = 0;
    public const PROCESSING = 1;
    public const SUCCESS = 2;
    public const DONE = 3;
    public const FAILED = 4;
    public const CANCELLED = 5;

    public static function fromPixWebhook(array $payload): int
    {
        $isSubadqA = isset($payload['event']) || (($payload['metadata']['source'] ?? null) === 'SubadqA');
        $status = $isSubadqA ? ($payload['status'] ?? 'PENDING') : ($payload['data']['status'] ?? 'PENDING');
        return match (strtoupper($status)) {
            'PENDING' => self::PENDING,
            'PROCESSING' => self::PROCESSING,
            'CONFIRMED' => self::SUCCESS,
            'PAID' => self::DONE,
            'CANCELLED' => self::CANCELLED,
            'FAILED' => self::FAILED,
            default => self::PENDING,
        };
    }

    public static function fromWithdrawWebhook(array $payload): int
    {
        $isSubadqA = isset($payload['event']) || (($payload['metadata']['source'] ?? null) === 'SubadqA');
        $status = $isSubadqA ? ($payload['status'] ?? 'PENDING') : ($payload['data']['status'] ?? 'PENDING');
        return match (strtoupper($status)) {
            'PENDING' => self::PENDING,
            'PROCESSING' => self::PROCESSING,
            'SUCCESS' => self::SUCCESS,
            'DONE' => self::DONE,
            'CANCELLED' => self::CANCELLED,
            'FAILED' => self::FAILED,
            default => self::PENDING,
        };
    }

    public static function fromString(string $status): int
    {
        return match (strtoupper($status)) {
            'PENDING' => self::PENDING,
            'PROCESSING' => self::PROCESSING,
            'CONFIRMED' => self::SUCCESS,
            'SUCCESS' => self::SUCCESS,
            'PAID' => self::DONE,
            'DONE' => self::DONE,
            'FAILED' => self::FAILED,
            'CANCELLED' => self::CANCELLED,
            default => self::PENDING,
        };
    }
}
