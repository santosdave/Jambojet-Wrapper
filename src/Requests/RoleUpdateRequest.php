<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Traits\ValidatesRequests;

/**
 * Role Update Request (Token Impersonation)
 * 
 * Updates JWT token with new role permissions
 * Endpoint: PUT /api/auth/v1/token/role
 * 
 * @package SantosDave\JamboJet\Requests
 */
class RoleUpdateRequest extends BaseRequest
{
    use  ValidatesRequests;
    /**
     * Create new role update request
     * 
     * @param string $roleCode Required: New role code to impersonate
     * @param string|null $cultureCode Optional: Culture code
     * @param string|null $currencyCode Optional: Currency code
     */
    public function __construct(
        public string $roleCode,
        public ?string $cultureCode = null,
        public ?string $currencyCode = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'roleCode' => $this->roleCode,
            'cultureCode' => $this->cultureCode,
            'currencyCode' => $this->currencyCode,
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

        // Validate required field
        $this->validateRequired($data, ['roleCode']);

        // Validate string lengths
        $this->validateStringLengths($data, [
            'roleCode' => ['max' => 10],
            'cultureCode' => ['max' => 10],
            'currencyCode' => ['max' => 3]
        ]);

        // Validate role code is not empty
        if (empty(trim($this->roleCode))) {
            throw new JamboJetValidationException(
                'Role code cannot be empty',
                400
            );
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
            roleCode: $data['roleCode'],
            cultureCode: $data['cultureCode'] ?? null,
            currencyCode: $data['currencyCode'] ?? null
        );
    }

    /**
     * Create with role code only
     * 
     * @param string $roleCode Role code
     * @return static
     */
    public static function withRole(string $roleCode): static
    {
        return new static(roleCode: $roleCode);
    }

    /**
     * Set culture code
     * 
     * @param string $cultureCode Culture code (e.g., 'en-US')
     * @return $this
     */
    public function withCulture(string $cultureCode): static
    {
        $this->cultureCode = $cultureCode;
        return $this;
    }

    /**
     * Set currency code
     * 
     * @param string $currencyCode Currency code (e.g., 'USD')
     * @return $this
     */
    public function withCurrency(string $currencyCode): static
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }
}
