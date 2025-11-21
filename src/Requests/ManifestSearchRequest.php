<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Manifest Search Request
 * 
 * Search for manifest trip information
 * Endpoint: GET /api/nsk/v2/manifest
 * 
 * @package SantosDave\JamboJet\Requests
 */
class ManifestSearchRequest extends BaseRequest
{
    use ValidatesRequests;

    /**
     * Create new manifest search request
     * 
     * @param string|null $origin Departure station code (3 chars)
     * @param string|null $destination Arrival station code (3 chars)
     * @param string|null $carrierCode Carrier code (2 chars)
     * @param string|null $beginDate Begin date for search (ISO format)
     * @param string|null $identifier Flight identifier (number or departure date)
     * @param string|null $flightType Flight type filter
     */
    public function __construct(
        public ?string $origin = null,
        public ?string $destination = null,
        public ?string $carrierCode = null,
        public ?string $beginDate = null,
        public ?string $identifier = null,
        public ?string $flightType = null
    ) {}

    /**
     * Convert to query parameters array
     * 
     * @return array Query parameters
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'origin' => $this->origin,
            'destination' => $this->destination,
            'carrierCode' => $this->carrierCode,
            'beginDate' => $this->beginDate,
            'identifier' => $this->identifier,
            'flightType' => $this->flightType,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate station codes length (3 characters)
        if ($this->origin && strlen($this->origin) !== 3) {
            throw new JamboJetValidationException(
                'Origin station code must be exactly 3 characters',
                400
            );
        }

        if ($this->destination && strlen($this->destination) !== 3) {
            throw new JamboJetValidationException(
                'Destination station code must be exactly 3 characters',
                400
            );
        }

        // Validate carrier code length (2 characters)
        if ($this->carrierCode && strlen($this->carrierCode) !== 2) {
            throw new JamboJetValidationException(
                'Carrier code must be exactly 2 characters',
                400
            );
        }

        // Validate date format if provided
        if ($this->beginDate && !$this->isValidDateFormat($this->beginDate)) {
            throw new JamboJetValidationException(
                'Begin date must be in valid ISO 8601 format (YYYY-MM-DD or YYYY-MM-DDTHH:MM:SS)',
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
        // Accept YYYY-MM-DD or ISO 8601 datetime format
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
            origin: $data['origin'] ?? null,
            destination: $data['destination'] ?? null,
            carrierCode: $data['carrierCode'] ?? null,
            beginDate: $data['beginDate'] ?? null,
            identifier: $data['identifier'] ?? null,
            flightType: $data['flightType'] ?? null
        );
    }

    /**
     * Set origin station
     * 
     * @param string $origin Origin station code
     * @return $this
     */
    public function withOrigin(string $origin): static
    {
        $this->origin = strtoupper($origin);
        return $this;
    }

    /**
     * Set destination station
     * 
     * @param string $destination Destination station code
     * @return $this
     */
    public function withDestination(string $destination): static
    {
        $this->destination = strtoupper($destination);
        return $this;
    }

    /**
     * Set carrier code
     * 
     * @param string $carrierCode Carrier code
     * @return $this
     */
    public function withCarrierCode(string $carrierCode): static
    {
        $this->carrierCode = strtoupper($carrierCode);
        return $this;
    }

    /**
     * Set begin date
     * 
     * @param string $beginDate Begin date in ISO format
     * @return $this
     */
    public function withBeginDate(string $beginDate): static
    {
        $this->beginDate = $beginDate;
        return $this;
    }

    /**
     * Set identifier (flight number or departure date)
     * 
     * @param string $identifier Flight identifier
     * @return $this
     */
    public function withIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Set flight type
     * 
     * @param string $flightType Flight type
     * @return $this
     */
    public function withFlightType(string $flightType): static
    {
        $this->flightType = $flightType;
        return $this;
    }
}
