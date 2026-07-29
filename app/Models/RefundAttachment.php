<?php

namespace App\Models;

use App\Enums\AttachmentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RefundAttachment extends Model
{
    protected $fillable = [

        'refund_id',

        'type',

        'original_name',

        'stored_name',

        'path',

        'mime_type',

        'size',

    ];



    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [

            'type' => AttachmentType::class,

            'size' => 'integer',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    public function refund(): BelongsTo
    {
        return $this->belongsTo(
            Refund::class
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */


    /**
     * Get downloadable URL.
     *
     * Files remain private.
     */
    public function url(): string
    {
        return Storage::disk('local')
            ->path($this->path);
    }



    /**
     * Human readable file size.
     */
    public function getSizeLabelAttribute(): string
    {
        $size = $this->size;


        if ($size >= 1048576) {

            return round(
                $size / 1048576,
                2
            ) . ' MB';
        }


        if ($size >= 1024) {

            return round(
                $size / 1024,
                2
            ) . ' KB';
        }


        return $size . ' Bytes';
    }



    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */


    public function scopeType(
        Builder $query,
        AttachmentType $type
    ): Builder {

        return $query->where(
            'type',
            $type
        );
    }



    public function scopeForRefund(
        Builder $query,
        int $refundId
    ): Builder {

        return $query->where(
            'refund_id',
            $refundId
        );
    }
}