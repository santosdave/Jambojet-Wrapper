<?php

namespace SantosDave\JamboJet\Contracts;

interface AuthenticationInterface
{
    /**
     * Create access token
     * POST /api/nsk/v1/token
     */
    public function createToken(array $credentials): array;

    /**
     * Get current token information
     * GET /api/nsk/v1/token
     */
    public function getTokenInfo(): array;

    /**
     * Refresh/update current token
     * PUT /api/nsk/v1/token
     */
    public function refreshToken(array $credentials = []): array;

    /**
     * Abandon/logout current token
     * DELETE /api/nsk/v1/token
     */
    public function logout(): array;
}
