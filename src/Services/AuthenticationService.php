<?php

namespace SantosDave\JamboJet\Services;

use Carbon\Carbon;
use SantosDave\JamboJet\Contracts\AuthenticationInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetAuthenticationException;
use Illuminate\Support\Facades\Cache;
use SantosDave\JamboJet\Events\TokenCreated;
use SantosDave\JamboJet\Events\TokenRefreshed;

/**
 * Authentication Service for JamboJet NSK API
 * 
 * Handles all authentication and token management operations
 * Base endpoints: /api/nsk/v1/token and /api/v1/token
 * 
 * Supported endpoints:
 * - GET /api/nsk/v1/token - Get current token information
 * - POST /api/nsk/v1/token - Create access token
 * - PUT /api/nsk/v1/token - Refresh/upgrade token or keep alive
 * - DELETE /api/nsk/v1/token - Abandon/logout token
 * - POST /api/nsk/v1/token/serverTransfer - Server transfer
 * - PUT /api/nsk/v1/token/singleSignOn - Single sign on upgrade
 * - POST /api/nsk/v1/token/singleSignOn - Create SSO token
 * 
 * @package SantosDave\JamboJet\Services
 */
class AuthenticationService implements AuthenticationInterface
{
    use HandlesApiRequests, ValidatesRequests;

    protected string $cachePrefix = 'jambojet_token_';

    /**
     * Create access token
     * 
     * POST /api/nsk/v1/token
     * Creates the general access token that will grant access to the API
     * 
     * @param array $credentials Optional NSK token request data
     * @return array Token response with access token
     * @throws JamboJetAuthenticationException
     */
    public function createToken(array $credentials = []): array
    {
        try {
            $response = $this->post('api/nsk/v1/token', $credentials);

            // Store token in cache and set for current session
            if (isset($response['data']['token'])) {
                $token = $response['data']['token'];
                $this->cacheToken($token, $response['data']);
                $this->setAccessToken($token);
            }

            // ADD: Fire event
            event(new TokenCreated($token, Carbon::parse($response['data']['expires']), $response['data']));

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to create access token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create access token (legacy endpoint)
     * 
     * POST /api/v1/token  
     * Legacy version for backwards compatibility
     * 
     * @param array $credentials Optional token request data
     * @return array Token response
     * @throws JamboJetAuthenticationException
     */
    public function createTokenLegacy(array $credentials = []): array
    {
        try {
            $response = $this->post('api/v1/token', $credentials);

            if (isset($response['data']['token'])) {
                $token = $response['data']['token'];
                $this->cacheToken($token, $response['data']);
                $this->setAccessToken($token);
            }

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to create access token (legacy): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get current token information
     * 
     * GET /api/nsk/v1/token
     * Get the information about the current token
     * 
     * @return array Current token session context
     * @throws JamboJetAuthenticationException
     */
    public function getTokenInfo(): array
    {
        try {
            return $this->get('api/nsk/v1/token');
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to get token information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Refresh/upgrade current token or keep alive
     * 
     * PUT /api/nsk/v1/token
     * Given a non-null request, upgrades the current session's logged in user.
     * Otherwise, keeps the active token alive.
     * 
     * @param array $credentials Optional credentials for upgrade
     * @return array Response from token refresh
     * @throws JamboJetAuthenticationException
     */
    public function refreshToken(array $credentials = []): array
    {
        try {
            $response = $this->put('api/nsk/v1/token', $credentials);

            // Update cache if new token provided
            if (isset($response['data']['token'])) {
                $token = $response['data']['token'];
                $this->cacheToken($token, $response['data']);
                $this->setAccessToken($token);

                // ADD: Fire event
                event(new TokenRefreshed($token, Carbon::parse($response['data']['expires'])));
            }

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to refresh token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Keep token alive (legacy endpoint)
     * 
     * PUT /api/v1/token
     * Legacy version that keeps the active token alive
     * 
     * @return array Response from keep alive
     * @throws JamboJetAuthenticationException
     */
    public function keepAlive(): array
    {
        try {
            return $this->put('api/v1/token');
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to keep token alive: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Abandon/logout current token
     * 
     * DELETE /api/nsk/v1/token
     * Abandons the active token and logs out the user
     * 
     * @return array Logout response
     * @throws JamboJetAuthenticationException
     */
    public function logout(): array
    {
        try {
            $response = $this->delete('api/nsk/v1/token');

            // Clear cached token and current session token
            $this->clearTokenCache();
            $this->clearAccessToken();

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to logout: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Abandon token (legacy endpoint)
     * 
     * DELETE /api/v1/token
     * Legacy version for abandoning the active token
     * 
     * @return array Logout response
     * @throws JamboJetAuthenticationException
     */
    public function logoutLegacy(): array
    {
        try {
            $response = $this->delete('api/v1/token');

            $this->clearTokenCache();
            $this->clearAccessToken();

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to logout (legacy): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Server transfer
     * 
     * POST /api/nsk/v1/token/serverTransfer
     * Transfers session context to another server
     * 
     * @param array $transferRequest Server transfer request data
     * @return array Transfer response with new token
     * @throws JamboJetAuthenticationException
     */
    public function serverTransfer(array $transferRequest): array
    {
        $this->validateRequired($transferRequest, ['targetServer']);

        try {
            $response = $this->post('api/nsk/v1/token/serverTransfer', $transferRequest);

            // Update with new token from transfer
            if (isset($response['data']['token'])) {
                $token = $response['data']['token'];
                $this->cacheToken($token, $response['data']);
                $this->setAccessToken($token);
            }

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to transfer server: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Single Sign On - Upgrade current session
     * 
     * PUT /api/nsk/v1/token/singleSignOn
     * Upgrades the current session's logged in user using SSO
     * 
     * @param array $ssoCredentials Single sign on credentials
     * @return array SSO upgrade response
     * @throws JamboJetAuthenticationException
     */
    public function singleSignOnUpgrade(array $ssoCredentials): array
    {
        $this->validateRequired($ssoCredentials, ['providerKey', 'token']);

        try {
            return $this->put('api/nsk/v1/token/singleSignOn', $ssoCredentials);
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to upgrade with SSO: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Single Sign On - Create token
     * 
     * POST /api/nsk/v1/token/singleSignOn
     * Creates access token using single sign on credentials
     * 
     * @param array $ssoCredentials Single sign on credentials
     * @return array SSO token response
     * @throws JamboJetAuthenticationException
     */
    public function singleSignOnCreate(array $ssoCredentials): array
    {
        $this->validateRequired($ssoCredentials, ['providerKey', 'token']);

        try {
            $response = $this->post('api/nsk/v1/token/singleSignOn', $ssoCredentials);

            if (isset($response['data']['token'])) {
                $token = $response['data']['token'];
                $this->cacheToken($token, $response['data']);
                $this->setAccessToken($token);
            }

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetAuthenticationException(
                'Failed to create SSO token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Authenticate with username and password
     * 
     * Convenience method that creates token with standard credentials
     * 
     * @param string $username User login name
     * @param string $password User password
     * @param array $additionalData Optional additional authentication data
     * @return array Authentication response
     * @throws JamboJetAuthenticationException
     */
    public function authenticate(string $username, string $password, array $additionalData = []): array
    {
        $credentials = array_merge([
            'username' => $username,
            'password' => $password,
        ], $additionalData);

        return $this->createToken($credentials);
    }

    /**
     * Check if user is currently authenticated
     * 
     * @return bool True if authenticated, false otherwise
     */
    public function isAuthenticated(): bool
    {
        try {
            $tokenInfo = $this->getTokenInfo();
            return $tokenInfo['success'] && !empty($tokenInfo['data']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get current user from token
     * 
     * @return array|null Current user data or null if not authenticated
     */
    public function getCurrentUser(): ?array
    {
        try {
            $tokenInfo = $this->getTokenInfo();
            return $tokenInfo['data']['user'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cache token for future use
     * 
     * @param string $token Access token
     * @param array $tokenData Full token response data
     * @return void
     */
    protected function cacheToken(string $token, array $tokenData): void
    {
        if (!$this->config['cache']['enabled']) {
            return;
        }

        // ADD: Parse expiration timestamp
        $expiresAt = isset($tokenData['expires'])
            ? Carbon::parse($tokenData['expires'])
            : now()->addSeconds($tokenData['expiresIn'] ?? 1200);

        $cacheKey = $this->cachePrefix . md5($token);

        Cache::put($cacheKey, [
            'token' => $token,
            'data' => $tokenData,
            'created_at' => now(),
            'expires_at' => $expiresAt  // ADD THIS
        ], $expiresAt->diffInSeconds(now()));

        $tokenManager = app(TokenManager::class);
        $tokenManager->setToken($token, $expiresAt);

        // ADD: Store current token globally
        Cache::put('jambojet_current_token', $token, $expiresAt->diffInSeconds(now()));
        Cache::put('jambojet_current_token_expires', $expiresAt, $expiresAt->diffInSeconds(now()));
    }

    /**
     * Get seconds until token expires
     * 
     * @return int Seconds until token expiration, 0 if expired or unknown
     */
    public function tokenExpiresIn(): int
    {
        $cached = $this->getCachedToken($this->accessToken);
        if (!$cached || !isset($cached['expires_at'])) {
            return 0;
        }
        return max(0, $cached['expires_at']->diffInSeconds(now()));
    }

    /**
     * Check if token is expiring soon
     * 
     * @param int $thresholdSeconds Threshold in seconds to consider "soon"
     * @return bool True if token is expiring within threshold, false otherwise
     */
    public function isTokenExpiringSoon(int $thresholdSeconds = 120): bool
    {
        return $this->tokenExpiresIn() < $thresholdSeconds;
    }

    /**
     * Check if token is expired
     * 
     * @return bool True if token is expired, false otherwise
     */
    public function isTokenExpired(): bool
    {
        return $this->tokenExpiresIn() <= 0;
    }

    /**
     * Get cached token data
     * 
     * @param string $token Access token
     * @return array|null Cached token data or null
     */
    protected function getCachedToken(string $token): ?array
    {
        if (!$this->config['cache']['enabled']) {
            return null;
        }

        $cacheKey = $this->cachePrefix . md5($token);
        return Cache::get($cacheKey);
    }

    /**
     * Clear token from cache
     * 
     * @return void
     */
    protected function clearTokenCache(): void
    {
        if (!$this->config['cache']['enabled'] || !$this->accessToken) {
            return;
        }

        $cacheKey = $this->cachePrefix . md5($this->accessToken);
        Cache::forget($cacheKey);
    }

    /**
     * Restore token from cache if available
     * 
     * @param string $token Token to restore
     * @return bool True if token was restored, false otherwise
     */
    public function restoreToken(string $token): bool
    {
        $cachedData = $this->getCachedToken($token);

        if ($cachedData) {
            $this->setAccessToken($cachedData['token']);
            return true;
        }

        return false;
    }

    /**
     * Get all available authentication methods
     * 
     * @return array List of available authentication methods
     */
    public function getAvailableMethods(): array
    {
        return [
            'standard' => [
                'method' => 'authenticate',
                'description' => 'Username and password authentication',
                'required' => ['username', 'password']
            ],
            'token_create' => [
                'method' => 'createToken',
                'description' => 'Create token with custom credentials',
                'required' => []
            ],
            'sso' => [
                'method' => 'singleSignOnCreate',
                'description' => 'Single Sign On authentication',
                'required' => ['providerKey', 'token']
            ],
            'legacy' => [
                'method' => 'createTokenLegacy',
                'description' => 'Legacy token creation',
                'required' => []
            ]
        ];
    }
}
