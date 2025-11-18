<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_tipo',
        'message',
        'context',
        'response',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'context' => 'array',
        'response' => 'array',
    ];

    public function logType(): BelongsTo
    {
        return $this->belongsTo(LogType::class, 'id_tipo');
    }
}

