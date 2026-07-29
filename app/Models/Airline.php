<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends Model
{
    use HasFactory, SoftDeletes;



    protected $fillable = [

        'name',

        'code',

        'slug',

        'subdomain',

        'logo',

        'support_email',

        'active',

        'settings',

    ];



    protected function casts(): array
    {
        return [

            'settings' =>
                'array',

            'active' =>
                'boolean',

        ];
    }



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    public function users(): HasMany
    {
        return $this->hasMany(
            User::class
        );
    }



    public function refunds(): HasMany
    {
        return $this->hasMany(
            Refund::class
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */


    public function scopeActive(
        Builder $query
    ): Builder {

        return $query->where(
            'active',
            true
        );
    }




    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */


    public function isActive(): bool
    {
        return $this->active;
    }



    public function setting(
        string $key,
        mixed $default = null
    ): mixed {

        return data_get(
            $this->settings,
            $key,
            $default
        );
    }



    public function updateSetting(
        string $key,
        mixed $value
    ): void {

        $settings = $this->settings ?? [];


        data_set(
            $settings,
            $key,
            $value
        );


        $this->update([
            'settings' => $settings
        ]);
    }
}