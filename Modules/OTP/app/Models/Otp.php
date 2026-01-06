<?php

namespace Modules\OTP\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Queue\Connectors\BeanstalkdConnector;

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
        'attempts',
        'last_sent_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'last_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeValid($query)
    {
        return $query->whereNull('verified_at')->where('expires_at', '>', now());
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
