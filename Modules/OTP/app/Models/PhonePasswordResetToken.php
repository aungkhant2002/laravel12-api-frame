<?php

namespace Modules\OTP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\OTP\Database\Factories\PhonePasswordResetTokenFactory;

class PhonePasswordResetToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function scopeValid($query)
    {
        return $query->whereNull('used_at')->where('expires_at', '>', now());
    }

    public function markUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    // protected static function newFactory(): PhonePasswordResetTokenFactory
    // {
    //     // return PhonePasswordResetTokenFactory::new();
    // }
}
