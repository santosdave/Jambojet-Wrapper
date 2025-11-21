<?php

namespace SantosDave\JamboJet\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Global Token Manager
 * 
 * Centralized token management across all service instances
 */
class TokenManager
{
    protected static ?string $globalToken = null;
    protected static ?Carbon $tokenExpiresAt = null;
    protected string $cachePrefix = 'jambojet_global_';

    /**
     * Set global token for all services
     */
    public function setToken(string $token, Carbon $expiresAt): void
    {
        static::$globalToken = $token;
        static::$tokenExpiresAt = $expiresAt;

        // Store in cache for persistence across requests
        Cache::put($this->cachePrefix . 'token', $token, $expiresAt->diffInSeconds(now()));
        Cache::put($this->cachePrefix . 'expires_at', $expiresAt, $expiresAt->diffInSeconds(now()));

        Log::info('JamboJet: Global token updated', [
            'expires_at' => $expiresAt->toISOString(),
            'expires_in_seconds' => $expiresAt->diffInSeconds(now())
        ]);
    }

    /**
     * Get current global token
     */
    public function getToken(): ?string
    {
        // Check memory first
        if (static::$globalToken) {
            return static::$globalToken;
        }

        // Load from cache
        return Cache::get($this->cachePrefix . 'token');
    }

    /**
     * Get token expiration time
     */
    public function getTokenExpiresAt(): ?Carbon
    {
        // Check memory first
        if (static::$tokenExpiresAt) {
            return static::$tokenExpiresAt;
        }

        // Load from cache
        return Cache::get($this->cachePrefix . 'expires_at');
    }

    /**
     * Check if token is valid
     */
    public function hasValidToken(): bool
    {
        $token = $this->getToken();
        $expiresAt = $this->getTokenExpiresAt();

        if (!$token || !$expiresAt) {
            return false;
        }

        return $expiresAt->isFuture();
    }

    /**
     * Clear global token
     */
    public function clearToken(): void
    {
        static::$globalToken = null;
        static::$tokenExpiresAt = null;

        Cache::forget($this->cachePrefix . 'token');
        Cache::forget($this->cachePrefix . 'expires_at');

        Log::info('JamboJet: Global token cleared');
    }

    /**
     * Get remaining seconds until expiration
     */
    public function getRemainingSeconds(): int
    {
        $expiresAt = $this->getTokenExpiresAt();

        if (!$expiresAt) {
            return 0;
        }

        return max(0, $expiresAt->diffInSeconds(now()));
    }
}