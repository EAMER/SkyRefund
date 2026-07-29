<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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


    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'flagged' => 'boolean',
            'details' => 'array',
        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    /**
     * Refund associated with this AI analysis.
     */
    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }



    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */


    /**
     * High risk AI results.
     */
    public function scopeFlagged(
        Builder $query
    ): Builder {

        return $query->where(
            'flagged',
            true
        );
    }



    /**
     * Latest AI analysis first.
     */
    public function scopeLatestAnalysis(
        Builder $query
    ): Builder {

        return $query->latest();
    }



    /*
    |--------------------------------------------------------------------------
    | Model Protection
    |--------------------------------------------------------------------------
    */


    /**
     * AI analysis history should not be edited.
     */
    protected static function booted(): void
    {
        static::updating(function () {

            throw new \RuntimeException(
                'AI analysis records cannot be modified.'
            );

        });


        static::deleting(function () {

            throw new \RuntimeException(
                'AI analysis records cannot be deleted.'
            );

        });
    }
}
