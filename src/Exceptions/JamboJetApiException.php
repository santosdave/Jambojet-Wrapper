<?php

namespace SantosDave\JamboJet\Exceptions;

use Exception;

class JamboJetApiException extends Exception
{
    protected array $context;

    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
