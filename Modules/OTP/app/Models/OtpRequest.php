<?php

namespace Modules\OTP\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\OTP\Database\Factories\OtpRequestFactory;

class OtpRequest extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'target',
        'purpose',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // protected static function newFactory(): OtpRequestFactory
    // {
    //     // return OtpRequestFactory::new();
    // }
}
