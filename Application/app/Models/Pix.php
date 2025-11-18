<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pix extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subacquirer_id',
        'transaction_id',
        'status',
        'amount',
        'payer_name',
        'payer_document',
        'payment_date',
        'payload',
        'expires_at',
        'idempotency'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subacquirer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Subacquirer::class);
    }
}

