<?php

namespace Modules\OTP\Exceptions;

use RuntimeException;

class OtpCooldownException extends RuntimeException
{
    public function __construct(public int $remainingSeconds, string $message = "")
    {
        parent::__construct($message ?: "Please wait {$remainingSeconds} seconds before requesting another OTP.");
    }
}
