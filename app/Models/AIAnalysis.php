<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIAnalysis extends Model
{
    protected $fillable = [
        'refund_id',
        'summary',
        'score',
        'flagged',
        'details',
    ];

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }
}
