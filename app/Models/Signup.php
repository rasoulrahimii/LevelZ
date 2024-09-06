<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $country_code
 * @property string $mobile
 * @property string $verification_code
 * @property string $registration_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $mobile_verified_at
 *
 * @mixin \Eloquent
 */
class Signup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mobile',
        'country_code',
        'verification_code',
        'registration_token',
    ];

    protected $casts = [
        'verification_code' => 'integer',
    ];
}
