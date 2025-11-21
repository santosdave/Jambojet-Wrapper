<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Authentication Token Request
 * 
 * Handles authentication token creation requests for NSK API v1
 * Endpoint: POST /api/auth/v1/token/user
 * 
 * @package SantosDave\JamboJet\Requests
 */
class AuthenticationTokenRequest extends BaseRequest
{
    /**
     * Create new authentication token request
     * 
     * @param string $username Required: Username or email for authentication
     * @param string $password Required: Password for authentication
     * @param string|null $domain Optional: Domain for the token
     * @param string|null $roleCode Optional: Role code for the session
     * @param array|null $roles Optional: Array of roles to assume
     * @param bool $persistent Optional: Whether token should be persistent (default: false)
     * @param int|null $expirationMinutes Optional: Token expiration in minutes
     * @param string|null $cultureCode Optional: Culture code for localization
     */
    public function __construct(
        public string $username,
        public string $password,
        public ?string $domain = null,
        public ?string $roleCode = null,
        public ?array $roles = null,
        public bool $persistent = false,
        public ?int $expirationMinutes = null,
        public ?string $cultureCode = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'username' => $this->username,
            'password' => $this->password,
            'domain' => $this->domain,
            'roleCode' => $this->roleCode,
            'roles' => $this->roles,
            'persistent' => $this->persistent,
            'expirationMinutes' => $this->expirationMinutes,
            'cultureCode' => $this->cultureCode,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    public function validate(): void
    {
        $data = $this->toArray();

        // Validate required fields
        $this->validateRequired($data, ['username', 'password']);

        // Validate username format (typically email)
        if (filter_var($this->username, FILTER_VALIDATE_EMAIL) === false) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Username must be a valid email address',
                400
            );
        }

        // Validate password is not empty
        if (strlen(trim($this->password)) === 0) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Password cannot be empty',
                400
            );
        }

        // Validate expiration minutes if provided
        if ($this->expirationMinutes !== null) {
            if (!is_int($this->expirationMinutes) || $this->expirationMinutes <= 0) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Expiration minutes must be a positive integer',
                    400
                );
            }

            // Reasonable maximum (24 hours)
            if ($this->expirationMinutes > 1440) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Expiration minutes cannot exceed 1440 (24 hours)',
                    400
                );
            }
        }

        // Validate culture code if provided
        if ($this->cultureCode) {
            if (!preg_match('/^[a-z]{2}-[A-Z]{2}$/', $this->cultureCode)) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Invalid culture code format. Expected format: en-US',
                    400
                );
            }
        }

        // Validate roles if provided
        if ($this->roles) {
            foreach ($this->roles as $index => $role) {
                if (!is_string($role) || empty(trim($role))) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "Role at index {$index} must be a non-empty string",
                        400
                    );
                }
            }
        }

        // Validate domain if provided
        if ($this->domain && strlen($this->domain) > 50) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Domain cannot exceed 50 characters',
                400
            );
        }

        // Validate role code if provided
        if ($this->roleCode && strlen($this->roleCode) > 20) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Role code cannot exceed 20 characters',
                400
            );
        }
    }

    /**
     * Create simple authentication request
     * 
     * @param string $email Email address
     * @param string $password Password
     * @return self
     */
    public static function createSimple(string $email, string $password): self
    {
        return new self(
            username: $email,
            password: $password
        );
    }

    /**
     * Create authentication request with role
     * 
     * @param string $email Email address
     * @param string $password Password
     * @param string $roleCode Role code to assume
     * @return self
     */
    public static function createWithRole(string $email, string $password, string $roleCode): self
    {
        return new self(
            username: $email,
            password: $password,
            roleCode: $roleCode
        );
    }

    /**
     * Create persistent authentication request
     * 
     * @param string $email Email address
     * @param string $password Password
     * @param int $expirationMinutes Token expiration in minutes
     * @return self
     */
    public static function createPersistent(string $email, string $password, int $expirationMinutes = 120): self
    {
        return new self(
            username: $email,
            password: $password,
            persistent: true,
            expirationMinutes: $expirationMinutes
        );
    }

    /**
     * Create agent authentication request
     * 
     * @param string $email Email address
     * @param string $password Password
     * @param string $domain Agent domain
     * @param array $roles Agent roles
     * @return self
     */
    public static function createAgent(string $email, string $password, string $domain, array $roles = []): self
    {
        return new self(
            username: $email,
            password: $password,
            domain: $domain,
            roles: $roles,
            persistent: true,
            expirationMinutes: 480 // 8 hours for agent sessions
        );
    }
}
