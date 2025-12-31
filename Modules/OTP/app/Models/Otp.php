<?php

namespace Modules\OTP\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'target_type',
        'purpose',
        'otp_hash',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeValid($query)
    {
        return $query->whereNull('verified_at')
                     ->where('expires_at', '>', now());
    }

    public function scopeForValid($query, string $target, string $purpose)
    {
        return $query->valid()
                     ->where('target', $target)
                     ->where('purpose', $purpose);
    }

    public function isExpired(): bool
    {
        return $this->expired_at->isPast();
    }

    public function markAsVerified(): bool
    {
        return $this->update(['verified_at' => now()]);
    }


    // protected static function newFactory(): OtpFactory
    // {
    //     // return OtpFactory::new();
    // }
}
