<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class MissingCustomerAddressException extends Exception
{
    private string $userMessage;

    public function __construct(
        string $message,
        int $code = 0,
        Throwable $previous = null,
        string $userMessage = null,
    ) {
        $this->userMessage = $userMessage;
        parent::__construct($message, $code, $previous);
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }
}
