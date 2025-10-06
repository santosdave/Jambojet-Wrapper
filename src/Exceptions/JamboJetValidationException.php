<?php

namespace SantosDave\JamboJet\Exceptions;

use Exception;

class JamboJetValidationException extends JamboJetApiException
{
    protected array $validationErrors;

    public function __construct(string $message = '', int $code = 0, array $validationErrors = [], ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }
}
