<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Multi-Factor Authentication Request
 * 
 * Handles MFA registration and verification for NSK API
 * Endpoint: POST /api/auth/v1/token/multifactor
 * 
 * @package SantosDave\JamboJet\Requests
 */
class MultiFactorRequest extends BaseRequest
{
    // MFA Types
    public const TYPE_EMAIL = 0;
    public const TYPE_SMS = 1;
    public const TYPE_TOTP = 2;

    /**
     * Create new MFA request
     * 
     * @param array $credentials Login credentials (domain, username, password)
     * @param array|null $registration Registration data for initial MFA setup (type, email/phone)
     * @param array|null $verify Verification data with challenge code and ID
     */
    public function __construct(
        public array $credentials,
        public ?array $registration = null,
        public ?array $verify = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'credentials' => $this->credentials,
            'registration' => $this->registration,
            'verify' => $this->verify,
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

        // Validate credentials
        $this->validateRequired($data, ['credentials']);
        $this->validateRequired($data['credentials'], ['domain', 'username', 'password']);

        // Must have either registration or verify
        if (!$this->registration && !$this->verify) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Either registration or verify data must be provided',
                400
            );
        }

        // Validate registration
        if ($this->registration) {
            $this->validateRegistration($this->registration);
        }

        // Validate verify
        if ($this->verify) {
            $this->validateVerify($this->verify);
        }
    }

    /**
     * Create email registration request
     * 
     * @param array $credentials Login credentials
     * @param string $email Email address for MFA
     * @return static
     */
    public static function registerEmail(array $credentials, string $email): static
    {
        return new static(
            credentials: $credentials,
            registration: [
                'type' => self::TYPE_EMAIL,
                'email' => $email
            ]
        );
    }

    /**
     * Create SMS registration request
     * 
     * @param array $credentials Login credentials
     * @param string $phone Phone number for MFA
     * @return static
     */
    public static function registerSms(array $credentials, string $phone): static
    {
        return new static(
            credentials: $credentials,
            registration: [
                'type' => self::TYPE_SMS,
                'phone' => $phone
            ]
        );
    }

    /**
     * Create TOTP (authenticator app) registration request
     * 
     * @param array $credentials Login credentials
     * @return static
     */
    public static function registerTotp(array $credentials): static
    {
        return new static(
            credentials: $credentials,
            registration: [
                'type' => self::TYPE_TOTP
            ]
        );
    }

    /**
     * Create verification request
     * 
     * @param array $credentials Login credentials
     * @param string $challengeCode Challenge code received
     * @param string $challengeId Challenge ID from registration
     * @return static
     */
    public static function verify(array $credentials, string $challengeCode, string $challengeId): static
    {
        return new static(
            credentials: $credentials,
            verify: [
                'challengeCode' => $challengeCode,
                'challengeId' => $challengeId
            ]
        );
    }

    /**
     * Validate registration data
     * 
     * @param array $registration Registration data
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateRegistration(array $registration): void
    {
        $this->validateRequired($registration, ['type']);

        $type = $registration['type'];

        if (!in_array($type, [self::TYPE_EMAIL, self::TYPE_SMS, self::TYPE_TOTP])) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid MFA type. Must be 0 (Email), 1 (SMS), or 2 (TOTP)',
                400
            );
        }

        // Validate email for type 0
        if ($type === self::TYPE_EMAIL) {
            $this->validateRequired($registration, ['email']);
            $this->validateFormats($registration, ['email' => 'email']);
        }

        // Validate phone for type 1
        if ($type === self::TYPE_SMS) {
            $this->validateRequired($registration, ['phone']);
        }
    }

    /**
     * Validate verify data
     * 
     * @param array $verify Verification data
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateVerify(array $verify): void
    {
        $this->validateRequired($verify, ['challengeCode', 'challengeId']);

        if (empty(trim($verify['challengeCode']))) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Challenge code cannot be empty',
                400
            );
        }

        if (empty(trim($verify['challengeId']))) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Challenge ID cannot be empty',
                400
            );
        }
    }
}
