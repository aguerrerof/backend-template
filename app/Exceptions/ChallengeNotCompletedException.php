<?php

namespace App\Exceptions;

class ChallengeNotCompletedException extends \Exception
{
    private readonly ?string $description;

    public function __construct(
        ?string $description,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->description = $description;
        parent::__construct($description, $code, $previous);
    }
    public function getDescription(): ?string
    {
        return $this->description;
    }
}
