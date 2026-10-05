<?php

namespace SantosDave\JamboJet\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Token Manager
 *
 * Shares the API token across all service instances of one JamboJet account: in memory for
 * this process, in the cache for the next. Tokens are kept per account (base URL, domain and
 * user name), so several accounts in one application never share or overwrite a token.
 */
class TokenManager
{
    /** @var array<string, array{token: string, expires_at: Carbon}> account scope => token */
    protected static array $tokens = [];

    protected string $scope;

    protected string $cachePrefix;

    /**
     * @param  string|null  $scope  the account's scope (see scopeFor); the configured account when null
     */
    public function __construct(?string $scope = null)
    {
        $this->scope = $scope ?? static::scopeFor((array) config('jambojet'));
        $this->cachePrefix = 'jambojet_global_' . $this->scope . '_';
    }

    /**
     * A short, stable name for the account a configuration logs in as. Holds no secret.
     */
    public static function scopeFor(array $config): string
    {
        $auth = (array) ($config['auth'] ?? []);

        return substr(hash('sha256', implode('|', [
            rtrim((string) ($config['base_url'] ?? ''), '/'),
            (string) ($auth['domain'] ?? ''),
            (string) ($auth['username'] ?? ''),
        ])), 0, 16);
    }

    public function scope(): string
    {
        return $this->scope;
    }

    /**
     * Set the token for every service instance of this account
     */
    public function setToken(string $token, Carbon $expiresAt): void
    {
        static::$tokens[$this->scope] = ['token' => $token, 'expires_at' => $expiresAt];

        // Store in cache for persistence across requests, until the token expires
        Cache::put($this->cachePrefix . 'token', $token, $expiresAt);
        Cache::put($this->cachePrefix . 'expires_at', $expiresAt, $expiresAt);

        Log::info('JamboJet: Global token updated', [
            'expires_at' => $expiresAt->toISOString(),
            'expires_in_seconds' => static::secondsUntil($expiresAt)
        ]);
    }

    /**
     * Whole seconds from now until the given time; negative once it has passed.
     *
     * Written so it means the same on Carbon 2 (Laravel 10) and Carbon 3 (Laravel 11+):
     * Carbon 3 made diffInSeconds() signed, so `$expiresAt->diffInSeconds(now())` turned
     * negative for every future expiry and tokens were never cached.
     */
    public static function secondsUntil(\DateTimeInterface $at): int
    {
        return (int) Carbon::now()->diffInSeconds(Carbon::instance($at), false);
    }

    protected function loadCachedTokenIfAvailable(): void
    {
        $token = Cache::get($this->cachePrefix . 'token');
        $expiresAt = Cache::get($this->cachePrefix . 'expires_at');

        if ($token && $expiresAt && $expiresAt->isFuture()) {
            static::$tokens[$this->scope] = ['token' => $token, 'expires_at' => $expiresAt];
            Log::debug('JamboJet: Auto-loaded cached token', [
                'expires_at' => $expiresAt->toDateTimeString()
            ]);
        }
    }

    /**
     * Get the current token
     */
    public function getToken(): ?string
    {
        // Check memory first, then the cache
        return static::$tokens[$this->scope]['token'] ?? Cache::get($this->cachePrefix . 'token');
    }

    /**
     * Get token expiration time
     */
    public function getTokenExpiresAt(): ?Carbon
    {
        return static::$tokens[$this->scope]['expires_at'] ?? Cache::get($this->cachePrefix . 'expires_at');
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
     * Clear the token
     */
    public function clearToken(): void
    {
        unset(static::$tokens[$this->scope]);

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

        return max(0, static::secondsUntil($expiresAt));
    }
}
