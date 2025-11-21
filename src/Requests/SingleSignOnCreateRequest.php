<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Traits\ValidatesRequests;

/**
 * Single Sign-On Create Request
 * 
 * Creates a new user and links it with an SSO provider (currently Facebook only)
 * Endpoint: POST /api/nsk/v1/token/user/person/singleSignOn
 * 
 * @package SantosDave\JamboJet\Requests
 */
class SingleSignOnCreateRequest extends BaseRequest
{

    use ValidatesRequests;
    /**
     * Create new SSO create request
     * 
     * @param array $person Person data for the new user
     * @param string $singleSignOnToken SSO token from provider
     * @param string|null $username Optional: Username for the new account
     * @param string|null $password Optional: Password for the new account
     * @param string|null $expirationDate Optional: Token expiration date
     */
    public function __construct(
        public array $person,
        public string $singleSignOnToken,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $expirationDate = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'person' => $this->person,
            'singleSignOn' => $this->singleSignOnToken,
            'username' => $this->username,
            'password' => $this->password,
            'expirationDate' => $this->expirationDate,
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
        $this->validateRequired($data, ['person', 'singleSignOn']);

        // Validate SSO token
        if (empty(trim($this->singleSignOnToken))) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Single sign-on token cannot be empty',
                400
            );
        }

        $this->validateStringLengths($data, [
            'singleSignOn' => ['max' => 256],
            'username' => ['max' => 64],
            'password' => ['max' => 128]
        ]);

        // Validate person data
        $this->validatePersonData($this->person);

        // Validate expiration date if provided
        if ($this->expirationDate) {
            $this->validateFormats($data, ['expirationDate' => 'datetime']);
        }
    }

    /**
     * Create from array
     * 
     * @param array $data Request data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            person: $data['person'],
            singleSignOnToken: $data['singleSignOn'] ?? $data['singleSignOnToken'],
            username: $data['username'] ?? null,
            password: $data['password'] ?? null,
            expirationDate: $data['expirationDate'] ?? null
        );
    }

    /**
     * Create with minimum required data
     * 
     * @param array $person Person data
     * @param string $ssoToken SSO token
     * @return static
     */
    public static function create(array $person, string $ssoToken): static
    {
        return new static(
            person: $person,
            singleSignOnToken: $ssoToken
        );
    }

    /**
     * Set username
     * 
     * @param string $username Username
     * @return $this
     */
    public function withUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set password
     * 
     * @param string $password Password
     * @return $this
     */
    public function withPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Set expiration date
     * 
     * @param string $expirationDate Expiration date (ISO format)
     * @return $this
     */
    public function withExpirationDate(string $expirationDate): static
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    /**
     * Validate person data structure
     * 
     * @param array $person Person data
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePersonData(array $person): void
    {
        // Person must have name
        if (!isset($person['name'])) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Person data must include name',
                400
            );
        }

        // Validate name structure
        $name = $person['name'];
        $this->validateRequired($name, ['first', 'last']);

        // Validate name lengths
        $this->validateStringLengths($name, [
            'first' => ['max' => 32],
            'middle' => ['max' => 32],
            'last' => ['max' => 32],
            'title' => ['max' => 6],
            'suffix' => ['max' => 6]
        ]);

        // Validate contact info if provided
        if (isset($person['emailAddress'])) {
            $this->validateFormats(['emailAddress' => $person['emailAddress']], [
                'emailAddress' => 'email'
            ]);
        }

        // Validate date of birth if provided
        if (isset($person['dateOfBirth'])) {
            $this->validateFormats(['dateOfBirth' => $person['dateOfBirth']], [
                'dateOfBirth' => 'datetime'
            ]);
        }
    }
}
