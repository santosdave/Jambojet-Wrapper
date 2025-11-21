<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Category 50 Fare Rules Request for JamboJet NSK API
 * 
 * Used with:
 * - POST /api/nsk/v2/fareRules/category50/journeys/{journeyKey}/segments/{segmentKey}
 * 
 * Category 50 fare rules provide detailed fare rule information
 * for specific journey segments
 * 
 * @package SantosDave\JamboJet\Requests
 */
class Category50FareRulesRequest extends BaseRequest
{
    /**
     * Create a new Category 50 fare rules request
     * 
     * @param string $journeyKey Required: Journey key
     * @param string $segmentKey Required: Segment key
     * @param string|null $fareAvailabilityKey Optional: Fare availability key
     * @param string|null $cultureCode Optional: Culture code for localized content (e.g., en-US)
     * @param string|null $currencyCode Optional: Currency code (3-letter ISO code)
     * @param array|null $passengerTypes Optional: Array of passenger type codes
     * @param bool $includeMarketing Optional: Include marketing information
     * @param bool $includeRestrictions Optional: Include restriction details
     * @param array|null $additionalData Optional: Additional request data
     */
    public function __construct(
        public string $journeyKey,
        public string $segmentKey,
        public ?string $fareAvailabilityKey = null,
        public ?string $cultureCode = null,
        public ?string $currencyCode = null,
        public ?array $passengerTypes = null,
        public bool $includeMarketing = true,
        public bool $includeRestrictions = true,
        public ?array $additionalData = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->fareAvailabilityKey !== null) {
            $data['fareAvailabilityKey'] = $this->fareAvailabilityKey;
        }

        if ($this->cultureCode !== null) {
            $data['cultureCode'] = $this->cultureCode;
        }

        if ($this->currencyCode !== null) {
            $data['currencyCode'] = $this->currencyCode;
        }

        if ($this->passengerTypes !== null) {
            $data['passengerTypes'] = $this->passengerTypes;
        }

        if ($this->includeMarketing !== true) {
            $data['includeMarketing'] = $this->includeMarketing;
        }

        if ($this->includeRestrictions !== true) {
            $data['includeRestrictions'] = $this->includeRestrictions;
        }

        if ($this->additionalData !== null) {
            $data = array_merge($data, $this->additionalData);
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
        // Validate required journey key
        if (empty($this->journeyKey)) {
            throw new JamboJetValidationException(
                'Journey key is required',
                400
            );
        }

        // Validate required segment key
        if (empty($this->segmentKey)) {
            throw new JamboJetValidationException(
                'Segment key is required',
                400
            );
        }

        // Validate currency code format if provided
        if ($this->currencyCode !== null) {
            if (!$this->isValidCurrencyCode($this->currencyCode)) {
                throw new JamboJetValidationException(
                    'Currency code must be a 3-letter ISO code (e.g., USD, EUR, KES)',
                    400
                );
            }
        }

        // Validate passenger types array if provided
        if ($this->passengerTypes !== null) {
            if (!is_array($this->passengerTypes)) {
                throw new JamboJetValidationException(
                    'Passenger types must be an array',
                    400
                );
            }

            if (empty($this->passengerTypes)) {
                throw new JamboJetValidationException(
                    'Passenger types array cannot be empty if provided',
                    400
                );
            }
        }

        // Validate boolean flags
        if (!is_bool($this->includeMarketing)) {
            throw new JamboJetValidationException(
                'includeMarketing must be a boolean value',
                400
            );
        }

        if (!is_bool($this->includeRestrictions)) {
            throw new JamboJetValidationException(
                'includeRestrictions must be a boolean value',
                400
            );
        }
    }

    /**
     * Check if value is a valid currency code
     * 
     * @param string $code Currency code
     * @return bool True if valid currency code
     */
    private function isValidCurrencyCode(string $code): bool
    {
        return (bool) preg_match('/^[A-Z]{3}$/', $code);
    }

    /**
     * Get journey key
     * 
     * @return string
     */
    public function getJourneyKey(): string
    {
        return $this->journeyKey;
    }

    /**
     * Get segment key
     * 
     * @return string
     */
    public function getSegmentKey(): string
    {
        return $this->segmentKey;
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
            journeyKey: $data['journeyKey'] ?? '',
            segmentKey: $data['segmentKey'] ?? '',
            fareAvailabilityKey: $data['fareAvailabilityKey'] ?? null,
            cultureCode: $data['cultureCode'] ?? null,
            currencyCode: $data['currencyCode'] ?? null,
            passengerTypes: $data['passengerTypes'] ?? null,
            includeMarketing: $data['includeMarketing'] ?? true,
            includeRestrictions: $data['includeRestrictions'] ?? true,
            additionalData: $data['additionalData'] ?? null
        );
    }

    /**
     * Create simple request with journey and segment keys only
     * 
     * @param string $journeyKey Journey key
     * @param string $segmentKey Segment key
     * @return static
     */
    public static function simple(string $journeyKey, string $segmentKey): static
    {
        return new static(
            journeyKey: $journeyKey,
            segmentKey: $segmentKey
        );
    }

    /**
     * Create request with fare key
     * 
     * @param string $journeyKey Journey key
     * @param string $segmentKey Segment key
     * @param string $fareAvailabilityKey Fare availability key
     * @return static
     */
    public static function withFareKey(string $journeyKey, string $segmentKey, string $fareAvailabilityKey): static
    {
        return new static(
            journeyKey: $journeyKey,
            segmentKey: $segmentKey,
            fareAvailabilityKey: $fareAvailabilityKey
        );
    }

    /**
     * Create localized request
     * 
     * @param string $journeyKey Journey key
     * @param string $segmentKey Segment key
     * @param string $cultureCode Culture code
     * @return static
     */
    public static function localized(string $journeyKey, string $segmentKey, string $cultureCode): static
    {
        return new static(
            journeyKey: $journeyKey,
            segmentKey: $segmentKey,
            cultureCode: $cultureCode
        );
    }

    /**
     * Set culture code
     * 
     * @param string $cultureCode Culture code
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
     * @param string $currencyCode Currency code
     * @return $this
     */
    public function withCurrency(string $currencyCode): static
    {
        $this->currencyCode = $currencyCode;
        return $this;
    }

    /**
     * Set passenger types
     * 
     * @param array $passengerTypes Passenger type codes
     * @return $this
     */
    public function forPassengerTypes(array $passengerTypes): static
    {
        $this->passengerTypes = $passengerTypes;
        return $this;
    }

    /**
     * Exclude marketing information
     * 
     * @return $this
     */
    public function withoutMarketing(): static
    {
        $this->includeMarketing = false;
        return $this;
    }

    /**
     * Exclude restriction details
     * 
     * @return $this
     */
    public function withoutRestrictions(): static
    {
        $this->includeRestrictions = false;
        return $this;
    }
}
