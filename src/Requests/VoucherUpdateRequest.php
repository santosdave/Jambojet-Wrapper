<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Voucher Update Request
 * 
 * Update voucher status, type, or expiration
 * Endpoint: PATCH /api/nsk/v1/vouchers/{voucherKey}
 * 
 * Note: Only one field can be updated per call
 * 
 * @package SantosDave\JamboJet\Requests
 */
class VoucherUpdateRequest extends BaseRequest
{
    use ValidatesRequests;

    // Voucher status constants
    public const STATUS_OPEN = 0;
    public const STATUS_CLOSED = 1;
    public const STATUS_EXPIRED = 2;
    public const STATUS_VOIDED = 3;

    // Voucher type constants (Note: 0=Credit and 2=Service are not valid for updates)
    public const TYPE_DISCOUNT = 1;
    public const TYPE_REPLACEMENT = 3;

    /**
     * Create new voucher update request
     * 
     * @param int|null $status Voucher status (0=Open, 1=Closed, 2=Expired, 3=Voided)
     * @param int|null $type Voucher type (1=Discount, 3=Replacement)
     * @param string|null $expiration Expiration date (ISO format)
     */
    public function __construct(
        public ?int $status = null,
        public ?int $type = null,
        public ?string $expiration = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'status' => $this->status,
            'type' => $this->type,
            'expiration' => $this->expiration,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        $data = $this->toArray();

        // Only one field can be updated per call
        $providedFields = count(array_filter($data, fn($value) => $value !== null));

        if ($providedFields === 0) {
            throw new JamboJetValidationException(
                'At least one field (status, type, or expiration) must be provided',
                400
            );
        }

        if ($providedFields > 1) {
            throw new JamboJetValidationException(
                'Only one field can be updated per call',
                400
            );
        }

        // Validate status value if provided
        if ($this->status !== null) {
            $validStatuses = [
                self::STATUS_OPEN,
                self::STATUS_CLOSED,
                self::STATUS_EXPIRED,
                self::STATUS_VOIDED
            ];
            if (!in_array($this->status, $validStatuses, true)) {
                throw new JamboJetValidationException(
                    'Invalid status. Must be 0 (Open), 1 (Closed), 2 (Expired), or 3 (Voided)',
                    400
                );
            }
        }

        // Validate type value if provided
        if ($this->type !== null) {
            $validTypes = [self::TYPE_DISCOUNT, self::TYPE_REPLACEMENT];
            if (!in_array($this->type, $validTypes, true)) {
                throw new JamboJetValidationException(
                    'Invalid type. Must be 1 (Discount) or 3 (Replacement). Types 0 (Credit) and 2 (Service) are not valid for updates',
                    400
                );
            }
        }

        // Validate expiration date format if provided
        if ($this->expiration && !$this->isValidDateFormat($this->expiration)) {
            throw new JamboJetValidationException(
                'Expiration date must be in valid ISO 8601 format',
                400
            );
        }
    }

    /**
     * Check if date is in valid format
     * 
     * @param string $date Date string
     * @return bool
     */
    private function isValidDateFormat(string $date): bool
    {
        $patterns = [
            '/^\d{4}-\d{2}-\d{2}$/',
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $date)) {
                return true;
            }
        }

        return false;
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
            status: $data['status'] ?? null,
            type: $data['type'] ?? null,
            expiration: $data['expiration'] ?? null
        );
    }

    /**
     * Create request to update status
     * 
     * @param int $status Status value
     * @return static
     */
    public static function updateStatus(int $status): static
    {
        return new static(status: $status);
    }

    /**
     * Create request to update type
     * 
     * @param int $type Type value
     * @return static
     */
    public static function updateType(int $type): static
    {
        return new static(type: $type);
    }

    /**
     * Create request to update expiration
     * 
     * @param string $expiration Expiration date
     * @return static
     */
    public static function updateExpiration(string $expiration): static
    {
        return new static(expiration: $expiration);
    }
}

/**
 * Voucher Owner Update Request
 * 
 * Update voucher owner
 * Endpoint: PATCH /api/nsk/v1/vouchers/{voucherKey}/owner
 * 
 * @package SantosDave\JamboJet\Requests
 */
class VoucherOwnerUpdateRequest extends BaseRequest
{
    use ValidatesRequests;

    /**
     * Create new voucher owner update request
     * 
     * @param string|null $personKey Person key
     * @param string|null $firstName First name (max 64 chars)
     * @param string|null $lastName Last name (max 64 chars)
     */
    public function __construct(
        public ?string $personKey = null,
        public ?string $firstName = null,
        public ?string $lastName = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'personKey' => $this->personKey,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        $data = $this->toArray();

        // At least one field must be provided
        if (empty($data)) {
            throw new JamboJetValidationException(
                'At least one field (personKey, firstName, or lastName) must be provided',
                400
            );
        }

        // Validate string lengths
        $this->validateStringLengths($data, [
            'firstName' => ['max' => 64],
            'lastName' => ['max' => 64],
        ]);
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
            personKey: $data['personKey'] ?? null,
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null
        );
    }

    /**
     * Set person key
     * 
     * @param string $personKey Person key
     * @return $this
     */
    public function withPersonKey(string $personKey): static
    {
        $this->personKey = $personKey;
        return $this;
    }

    /**
     * Set name
     * 
     * @param string $firstName First name
     * @param string $lastName Last name
     * @return $this
     */
    public function withName(string $firstName, string $lastName): static
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        return $this;
    }
}
