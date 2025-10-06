<?php

namespace SantosDave\JamboJet\Events;

use Carbon\Carbon;

class TokenExpired
{
    public function __construct(
        public string $token
    ) {}
}
