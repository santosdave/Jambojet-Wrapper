<?php

namespace SantosDave\JamboJet\Contracts;

interface AuthenticationInterface
{
    /**
     * Create access token
     * POST /api/auth/v1/token/user
     */
    public function createToken(array $credentials): array;

    /**
     * Get current token information
     * GET /api/auth/v1/token/user
     */
    public function getTokenInfo(): array;

    /**
     * Refresh/update current token
     * PUT /api/auth/v1/token/user
     */
    public function refreshToken(array $credentials = []): array;

    /**
     * Check if the current token is expiring soon
     *
     * @param int $thresholdSeconds Number of seconds before expiry to consider as "expiring soon"
     * @return bool
     */
    public function isTokenExpiringSoon(int $thresholdSeconds = 120): bool;

    /**
     * Automatically authenticate and obtain a valid token if needed.
     *
     * @return array
     */
    public function autoAuthenticate(): array;

    /**
     * Check if there is a valid authenticated session
     *
     * @return bool
     */
    public function isAuthenticated(): bool;


    /**
     * Ensure there is a valid authenticated session, otherwise throw an exception
     *
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetAuthenticationException
     */
    public function ensureAuthenticated(): void;


    /**
     * Abandon/logout current token
     * DELETE /api/auth/v1/token/user
     */
    public function logout(): array;
}
