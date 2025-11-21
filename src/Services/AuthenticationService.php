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
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Requests\MultiFactorRequest;
use SantosDave\JamboJet\Requests\RoleUpdateRequest;
use SantosDave\JamboJet\Requests\SingleSignOnCreateRequest;

/**
 * Authentication Service for JamboJet NSK API
 * 
 * Handles all authentication and token management operations
 * Base endpoints: /api/auth/v1/token/user and /api/v1/token
 * 
 * Supported endpoints:
 * - GET /api/auth/v1/token/user - Get current token information
 * - POST /api/auth/v1/token/user - Create access token
 * - PUT /api/auth/v1/token/user - Refresh/upgrade token or keep alive
 * - DELETE /api/auth/v1/token/user - Abandon/logout token
 * - POST /api/auth/v1/token/user/serverTransfer - Server transfer
 * - PUT /api/auth/v1/token/user/singleSignOn - Single sign on upgrade
 * - POST /api/auth/v1/token/user/singleSignOn - Create SSO token
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
     * POST /api/auth/v1/token/user
     * Creates the general access token that will grant access to the API
     * 
     * @param array $credentials Optional NSK token request data
     * @return array Token response with access token
     * @throws JamboJetAuthenticationException
     */
    public function createToken(array $credentials = []): array
    {

        try {
            $response = $this->post('api/auth/v1/token/user', $credentials);
            // Store token in cache and set for current session
            if (isset($response['data']['data']['token'])) {
                $tokenData = $response['data']['data'];
                $token = $tokenData['token'];
                $this->cacheToken($token, $tokenData);
                $this->setAccessToken($token);

                // Fire event
                event(new TokenCreated($token, Carbon::parse(time: $tokenData['expires']), $tokenData));
            }
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
     * GET /api/auth/v1/token/user
     * Get the information about the current token
     * 
     * @return array Current token session context
     * @throws JamboJetAuthenticationException
     */
    public function getTokenInfo(): array
    {
        try {
            return $this->get('api/auth/v1/token/user');
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
     * PUT /api/auth/v1/token/user
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
            $response = $this->put('api/auth/v1/token/user', $credentials);

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
     * DELETE /api/auth/v1/token/user
     * Abandons the active token and logs out the user
     * 
     * @return array Logout response
     * @throws JamboJetAuthenticationException
     */
    public function logout(): array
    {
        try {
            $response = $this->delete('api/auth/v1/token/user');

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
     * POST /api/auth/v1/token/user/serverTransfer
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
            $response = $this->post('api/auth/v1/token/user/serverTransfer', $transferRequest);

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
     * PUT /api/auth/v1/token/user/singleSignOn
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
            return $this->put('api/auth/v1/token/user/singleSignOn', $ssoCredentials);
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
     * POST /api/auth/v1/token/user/singleSignOn
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
            $response = $this->post('api/auth/v1/token/user/singleSignOn', $ssoCredentials);

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
     * Create anonymous token
     * 
     * POST /api/auth/v1/token/anonymous
     * Creates a short-lived anonymous JWT token for unauthenticated access
     * Tokens have a set expiration time
     * 
     * @param array $refreshData Optional refresh token data
     * @return array Anonymous token response
     * @throws JamboJetApiException
     */
    public function createAnonymousToken(array $refreshData = []): array
    {
        try {
            $response = $this->post('api/auth/v1/token/anonymous', $refreshData);

            if (isset($response['data']['token'])) {
                $this->setAccessToken($response['data']['token']);
                $this->cacheToken($response['data']['token'], $response['data']);
            }

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create anonymous token: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Register multi-factor authentication
     * 
     * POST /api/auth/v1/token/multifactor
     * Register multi-factor authentication options for first-time users
     * Allows selection of MFA method and sends challenge code
     * 
     * @param MultiFactorRequest|array $request MFA request object or data array
     * @return array MFA registration response
     * @throws JamboJetApiException
     */
    public function registerMultiFactor(MultiFactorRequest|array $request): array
    {
        // Convert array to Request object if needed
        if (is_array($request)) {
            $request = new MultiFactorRequest(
                credentials: $request['credentials'],
                registration: $request['registration'] ?? null,
                verify: $request['verify'] ?? null
            );
        }

        $request->validate();

        try {
            return $this->post('api/auth/v1/token/multifactor', $request->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to register multi-factor authentication: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update token with new role permissions
     * 
     * PUT /api/auth/v1/token/role
     * Updates the short-lived JWT with new role permissions (impersonation)
     * 
     * @param RoleUpdateRequest|array $request Role update request object or data array
     * @return array Updated token response
     * @throws JamboJetApiException
     */
    public function updateTokenRole(RoleUpdateRequest|array $request): array
    {
        // Convert array to Request object if needed
        if (is_array($request)) {
            $request = RoleUpdateRequest::fromArray($request);
        }

        $request->validate();

        try {
            $response = $this->put('api/auth/v1/token/role', $request->toArray());

            if (isset($response['data']['token'])) {
                $this->setAccessToken($response['data']['token']);
                $this->cacheToken($response['data']['token'], $response['data']);
            }

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update token role: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create single sign-on user with person data
     * 
     * POST /api/nsk/v1/token/user/person/singleSignOn
     * Creates a new user, links it with an SSO provider, then logs in as the new user
     * Warning: Currently only works with Facebook
     * 
     * @param SingleSignOnCreateRequest|array $request SSO create request object or data array
     * @return array Token response with new user
     * @throws JamboJetApiException
     */
    public function createSingleSignOnUser(SingleSignOnCreateRequest|array $request): array
    {
        // Convert array to Request object if needed
        if (is_array($request)) {
            $request = SingleSignOnCreateRequest::fromArray($request);
        }

        $request->validate();

        try {
            $response = $this->post('api/nsk/v1/token/user/person/singleSignOn', $request->toArray());

            if (isset($response['data']['token'])) {
                $this->setAccessToken($response['data']['token']);
                $this->cacheToken($response['data']['token'], $response['data']);
            }

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create SSO user: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create single sign-on token for person
     * 
     * POST /api/nsk/v1/token/user/person/singleSignOn
     * Creates SSO token associated with a specific person
     * Note: Different from standard SSO - this endpoint creates the SSO token itself
     * 
     * @param string $personKey Person identifier
     * @param array $ssoData SSO token creation data
     * @return array SSO token response
     * @throws JamboJetApiException
     */
    public function createPersonSingleSignOnToken(string $personKey, array $ssoData): array
    {
        $this->validatePersonKey($personKey);
        $this->validateSsoTokenCreationRequest($ssoData);

        try {
            return $this->post("api/nsk/v1/token/user/person/{$personKey}/singleSignOn", $ssoData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create person SSO token: ' . $e->getMessage(),
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
        $cached = $this->getCachedToken($this->accessToken);
        if (!$cached || !isset($cached['expires_at'])) {
            return true; // No token, needs refresh
        }

        $remaining = $cached['expires_at']->diffInSeconds(now());
        return $remaining < $thresholdSeconds;
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
     * Auto-authenticate with platform credentials
     */
    public function autoAuthenticate(): array
    {
        return $this->createToken([
            'credentials' => [
                'userName' => config('jambojet.auth.username'),
                'password' => config('jambojet.auth.password'),
                'domain' => config('jambojet.auth.domain'),
                'channelType' => 'Api'
            ]
        ]);
    }

    /**
     * Ensure valid token exists
     */
    public function ensureAuthenticated(): void
    {
        if (!$this->accessToken || $this->isTokenExpiringSoon()) {
            $this->autoAuthenticate();
        }
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
     * Validate multi-factor authentication request
     * 
     * @param array $data MFA data
     * @throws JamboJetValidationException
     */
    private function validateMultiFactorRequest(array $data): void
    {
        // Validate required credentials
        $this->validateRequired($data, ['credentials']);

        $credentials = $data['credentials'];
        $this->validateRequired($credentials, ['domain', 'username', 'password']);

        // Must have either registration or verify
        if (!isset($data['registration']) && !isset($data['verify'])) {
            throw new JamboJetValidationException(
                'Either registration or verify data must be provided for MFA',
                400
            );
        }

        // Validate registration data if provided
        if (isset($data['registration'])) {
            $this->validateRequired($data['registration'], ['type']);

            $type = $data['registration']['type'];

            // Type 0 = Email, Type 1 = SMS, Type 2 = TOTP
            if (!in_array($type, [0, 1, 2])) {
                throw new JamboJetValidationException(
                    'Invalid MFA type. Must be 0 (Email), 1 (SMS), or 2 (TOTP)',
                    400
                );
            }

            // Validate email for type 0
            if ($type === 0) {
                $this->validateRequired($data['registration'], ['email']);
                $this->validateFormats($data['registration'], ['email' => 'email']);
            }

            // Validate phone for type 1
            if ($type === 1) {
                $this->validateRequired($data['registration'], ['phone']);
            }
        }

        // Validate verify data if provided
        if (isset($data['verify'])) {
            $this->validateRequired($data['verify'], ['challengeCode', 'challengeId']);
        }
    }

    /**
     * Validate role update request
     * 
     * @param array $data Role data
     * @throws JamboJetValidationException
     */
    private function validateRoleUpdateRequest(array $data): void
    {
        // Based on JwtImpersonateRequest schema
        $this->validateRequired($data, ['roleCode']);

        $this->validateStringLengths($data, [
            'roleCode' => ['max' => 10],
            'cultureCode' => ['max' => 10],
            'currencyCode' => ['max' => 3]
        ]);
    }

    /**
     * Validate person key format
     * 
     * @param string $personKey Person key
     * @throws JamboJetValidationException
     */
    private function validatePersonKey(string $personKey): void
    {
        if (empty(trim($personKey))) {
            throw new JamboJetValidationException(
                'Person key cannot be empty',
                400
            );
        }
    }

    /**
     * Validate SSO token creation request
     * 
     * @param array $data SSO creation data
     * @throws JamboJetValidationException
     */
    private function validateSsoTokenCreationRequest(array $data): void
    {
        // Add validation based on the specific schema requirements
        // This endpoint creates the SSO token itself, not using it for auth
        if (isset($data['expirationDate'])) {
            $this->validateFormats($data, ['expirationDate' => 'datetime']);
        }
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