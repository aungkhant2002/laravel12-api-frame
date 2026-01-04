<?php

namespace Modules\OTP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Modules\OTP\Database\Factories\OtpFactory;

class Otp extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'target',
        'purpose',
        'otp_hash',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function scopeValid($query)
    {
        return $query->whereNull('verified_at')->where('expired_at', '>', now());
    }

    public function markAsVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    // protected static function newFactory(): OtpFactory
    // {
    //     // return OtpFactory::new();
    // }
}
