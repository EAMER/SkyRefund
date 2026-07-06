<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundAttachment extends Model
{
    protected $fillable = [
        'refund_id',
        'type',
        'original_name',
        'stored_name',
        'path',
        'mime_type',
        'size'
    ];

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }
}