<?php

namespace SantosDave\JamboJet\Events;

use Carbon\Carbon;

class TokenRefreshed
{
    public function __construct(
        public string $token,
        public Carbon $expiresAt
    ) {}
}
