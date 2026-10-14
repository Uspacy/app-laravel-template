<?php

namespace App\Exceptions;

class JwtDecodeException extends \Exception
{
    public function __construct(string $message = 'Invalid token', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
