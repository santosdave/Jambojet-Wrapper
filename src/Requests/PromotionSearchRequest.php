<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Promotion Search Request for JamboJet NSK API
 * 
 * Used with:
 * - GET /api/nsk/v1/promotions
 * 
 * Match Criteria Enum:
 * - 0 = StartsWith
 * - 1 = EndsWith
 * - 2 = Contains
 * - 3 = ExactMatch (default)
 * 
 * @package SantosDave\JamboJet\Requests
 */
class PromotionSearchRequest extends BaseRequest
{
    public const MATCH_STARTS_WITH = 0;
    public const MATCH_ENDS_WITH = 1;
    public const MATCH_CONTAINS = 2;
    public const MATCH_EXACT = 3;

    /**
     * Create a new promotion search request
     * 
     * @param string|null $promotionCode Optional: Promotion code (max 8 chars)
     * @param string|null $organizationCode Optional: Organization code (max 10 chars)
     * @param string|null $effectiveDate Optional: Effective date (ISO 8601 format)
     * @param string|null $cultureCode Optional: Culture code (e.g., en-US)
     * @param int|null $promotionCodeMatching Optional: Match criteria for promotion code (0-3)
     * @param int|null $organizationCodeMatching Optional: Match criteria for organization code (0-3)
     */
    public function __construct(
        public ?string $promotionCode = null,
        public ?string $organizationCode = null,
        public ?string $effectiveDate = null,
        public ?string $cultureCode = null,
        public ?int $promotionCodeMatching = null,
        public ?int $organizationCodeMatching = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->promotionCode !== null) {
            $data['PromotionCode'] = $this->promotionCode;
        }

        if ($this->organizationCode !== null) {
            $data['OrganizationCode'] = $this->organizationCode;
        }

        if ($this->effectiveDate !== null) {
            $data['EffectiveDate'] = $this->effectiveDate;
        }

        if ($this->cultureCode !== null) {
            $data['CultureCode'] = $this->cultureCode;
        }

        if ($this->promotionCodeMatching !== null) {
            $data['PromotionCodeMatching'] = $this->promotionCodeMatching;
        }

        if ($this->organizationCodeMatching !== null) {
            $data['OrganizationCodeMatching'] = $this->organizationCodeMatching;
        }

        return $data;
    }

    /**
     * Validate request data
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate PromotionCode length
        if ($this->promotionCode !== null && strlen($this->promotionCode) > 8) {
            throw new JamboJetValidationException(
                'PromotionCode must not exceed 8 characters',
                400
            );
        }

        // Validate OrganizationCode length
        if ($this->organizationCode !== null && strlen($this->organizationCode) > 10) {
            throw new JamboJetValidationException(
                'OrganizationCode must not exceed 10 characters',
                400
            );
        }

        // Validate EffectiveDate format
        if ($this->effectiveDate !== null) {
            if (!$this->isValidDateTime($this->effectiveDate)) {
                throw new JamboJetValidationException(
                    'EffectiveDate must be a valid ISO 8601 datetime format',
                    400
                );
            }
        }

        // Validate PromotionCodeMatching enum
        if ($this->promotionCodeMatching !== null) {
            if (!in_array($this->promotionCodeMatching, [0, 1, 2, 3])) {
                throw new JamboJetValidationException(
                    'PromotionCodeMatching must be 0 (StartsWith), 1 (EndsWith), 2 (Contains), or 3 (ExactMatch)',
                    400
                );
            }
        }

        // Validate OrganizationCodeMatching enum
        if ($this->organizationCodeMatching !== null) {
            if (!in_array($this->organizationCodeMatching, [0, 1, 2, 3])) {
                throw new JamboJetValidationException(
                    'OrganizationCodeMatching must be 0 (StartsWith), 1 (EndsWith), 2 (Contains), or 3 (ExactMatch)',
                    400
                );
            }
        }
    }

    /**
     * Check if value is a valid datetime
     * 
     * @param string $datetime Datetime string
     * @return bool True if valid datetime
     */
    private function isValidDateTime(string $datetime): bool
    {
        return (bool) strtotime($datetime) &&
            (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $datetime) ||
                preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $datetime));
    }

    /**
     * Create from array data
     * 
     * @param array $data Request data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            promotionCode: $data['PromotionCode'] ?? $data['promotionCode'] ?? null,
            organizationCode: $data['OrganizationCode'] ?? $data['organizationCode'] ?? null,
            effectiveDate: $data['EffectiveDate'] ?? $data['effectiveDate'] ?? null,
            cultureCode: $data['CultureCode'] ?? $data['cultureCode'] ?? null,
            promotionCodeMatching: $data['PromotionCodeMatching'] ?? $data['promotionCodeMatching'] ?? null,
            organizationCodeMatching: $data['OrganizationCodeMatching'] ?? $data['organizationCodeMatching'] ?? null
        );
    }

    /**
     * Search by exact promotion code
     * 
     * @param string $code Promotion code
     * @return static
     */
    public static function byCode(string $code): static
    {
        return new static(
            promotionCode: $code,
            promotionCodeMatching: self::MATCH_EXACT
        );
    }

    /**
     * Search by organization
     * 
     * @param string $organizationCode Organization code
     * @return static
     */
    public static function byOrganization(string $organizationCode): static
    {
        return new static(
            organizationCode: $organizationCode,
            organizationCodeMatching: self::MATCH_EXACT
        );
    }

    /**
     * Search active promotions at specific date
     * 
     * @param string $effectiveDate Effective date
     * @return static
     */
    public static function activeAt(string $effectiveDate): static
    {
        return new static(
            effectiveDate: $effectiveDate
        );
    }

    /**
     * Set promotion code matching strategy
     * 
     * @param int $matching Match criteria (0-3)
     * @return $this
     */
    public function withPromotionMatching(int $matching): static
    {
        $this->promotionCodeMatching = $matching;
        return $this;
    }

    /**
     * Set organization code matching strategy
     * 
     * @param int $matching Match criteria (0-3)
     * @return $this
     */
    public function withOrganizationMatching(int $matching): static
    {
        $this->organizationCodeMatching = $matching;
        return $this;
    }
}
