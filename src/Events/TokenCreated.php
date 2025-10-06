<?php

namespace SantosDave\JamboJet\Events;

use Carbon\Carbon;

class TokenCreated
{
    public function __construct(
        public string $token,
        public Carbon $expiresAt,
        public array $tokenData
    ) {}
}



