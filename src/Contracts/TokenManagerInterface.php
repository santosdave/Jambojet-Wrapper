<?php

namespace SantosDave\JamboJet\Contracts;

use Carbon\Carbon;

interface TokenManagerInterface
{
    /**
     * Set global token for all services
     */
    public function setToken(string $token, Carbon $expiresAt): void;

    /**
     * Get current global token
     */
    public function getToken(): ?string;

    /**
     * Get token expiration time
     */
    public function getTokenExpiresAt(): ?Carbon;

    /**
     * Check if token is valid
     */
    public function hasValidToken(): bool;

    /**
     * Clear global token
     */
    public function clearToken(): void;

    /**
     * Get remaining seconds until expiration
     */
    public function getRemainingSeconds(): int;
}
