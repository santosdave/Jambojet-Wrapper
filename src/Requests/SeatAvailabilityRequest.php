<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Requests\BaseRequest;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Seat Availability Request for JamboJet NSK API
 * 
 * Used with: GET /api/nsk/v1/booking/seat/availability
 * 
 * @package SantosDave\JamboJet\Requests
 */
class SeatAvailabilityRequest extends BaseRequest
{
    /**
     * Create a new seat availability request
     * 
     * @param array|null $segmentKeys Optional: Specific segment keys to check seat availability for
     * @param array|null $passengerKeys Optional: Specific passenger keys to check seat availability for
     * @param string|null $cabinClass Optional: Cabin class filter (Economy, Business, First)
     * @param array|null $seatTypes Optional: Seat type preferences (Window, Aisle, Middle)
     * @param array|null $seatFeatures Optional: Seat feature filters (ExtraLegroom, Preferred, etc.)
     * @param bool $includePricing Optional: Include seat pricing information
     * @param string|null $currencyCode Optional: Currency for pricing display
     * @param bool $availableOnly Optional: Show only available seats (default: true)
     * @param array|null $preferences Optional: Additional seat preferences
     */
    public function __construct(
        public ?array $segmentKeys = null,
        public ?array $passengerKeys = null,
        public ?string $cabinClass = null,
        public ?array $seatTypes = null,
        public ?array $seatFeatures = null,
        public bool $includePricing = false,
        public ?string $currencyCode = null,
        public bool $availableOnly = true,
        public ?array $preferences = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'segmentKeys' => $this->segmentKeys,
            'passengerKeys' => $this->passengerKeys,
            'cabinClass' => $this->cabinClass,
            'seatTypes' => $this->seatTypes,
            'seatFeatures' => $this->seatFeatures,
            'includePricing' => $this->includePricing,
            'currencyCode' => $this->currencyCode,
            'availableOnly' => $this->availableOnly,
            'preferences' => $this->preferences
        ]);
    }

    /**
     * Validate the request
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate segment keys if provided
        if ($this->segmentKeys !== null) {
            if (!is_array($this->segmentKeys)) {
                throw new JamboJetValidationException('segmentKeys must be an array');
            }

            foreach ($this->segmentKeys as $index => $segmentKey) {
                if (!is_string($segmentKey) || empty(trim($segmentKey))) {
                    throw new JamboJetValidationException("segmentKeys[{$index}] must be a non-empty string");
                }

                if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $segmentKey)) {
                    throw new JamboJetValidationException("segmentKeys[{$index}] format is invalid");
                }
            }
        }

        // Validate passenger keys if provided
        if ($this->passengerKeys !== null) {
            if (!is_array($this->passengerKeys)) {
                throw new JamboJetValidationException('passengerKeys must be an array');
            }

            foreach ($this->passengerKeys as $index => $passengerKey) {
                if (!is_string($passengerKey) || empty(trim($passengerKey))) {
                    throw new JamboJetValidationException("passengerKeys[{$index}] must be a non-empty string");
                }

                if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $passengerKey)) {
                    throw new JamboJetValidationException("passengerKeys[{$index}] format is invalid");
                }
            }
        }

        // Validate cabin class if provided
        if ($this->cabinClass !== null) {
            $validCabinClasses = ['Economy', 'Business', 'First', 'Premium'];
            if (!in_array($this->cabinClass, $validCabinClasses)) {
                throw new JamboJetValidationException('cabinClass must be one of: ' . implode(', ', $validCabinClasses));
            }
        }

        // Validate seat types if provided
        if ($this->seatTypes !== null) {
            if (!is_array($this->seatTypes)) {
                throw new JamboJetValidationException('seatTypes must be an array');
            }

            $validSeatTypes = ['Window', 'Aisle', 'Middle', 'Any'];
            foreach ($this->seatTypes as $index => $seatType) {
                if (!in_array($seatType, $validSeatTypes)) {
                    throw new JamboJetValidationException("seatTypes[{$index}] must be one of: " . implode(', ', $validSeatTypes));
                }
            }
        }

        // Validate seat features if provided
        if ($this->seatFeatures !== null) {
            if (!is_array($this->seatFeatures)) {
                throw new JamboJetValidationException('seatFeatures must be an array');
            }

            $validFeatures = ['ExtraLegroom', 'Preferred', 'Exit', 'Bulkhead', 'Quiet', 'Standard'];
            foreach ($this->seatFeatures as $index => $feature) {
                if (!in_array($feature, $validFeatures)) {
                    throw new JamboJetValidationException("seatFeatures[{$index}] must be one of: " . implode(', ', $validFeatures));
                }
            }
        }

        // Validate boolean flags
        if (!is_bool($this->includePricing)) {
            throw new JamboJetValidationException('includePricing must be a boolean');
        }

        if (!is_bool($this->availableOnly)) {
            throw new JamboJetValidationException('availableOnly must be a boolean');
        }

        // Validate currency code if provided
        if ($this->currencyCode !== null) {
            $this->validateFormats(['currencyCode' => $this->currencyCode], ['currencyCode' => 'currency']);
        }

        // Validate preferences if provided
        if ($this->preferences !== null) {
            if (!is_array($this->preferences)) {
                throw new JamboJetValidationException('preferences must be an array');
            }

            // Validate specific preference fields
            if (isset($this->preferences['together']) && !is_bool($this->preferences['together'])) {
                throw new JamboJetValidationException('preferences.together must be a boolean');
            }

            if (isset($this->preferences['maxPrice']) && (!is_numeric($this->preferences['maxPrice']) || $this->preferences['maxPrice'] < 0)) {
                throw new JamboJetValidationException('preferences.maxPrice must be a non-negative number');
            }
        }
    }

    /**
     * Create request for specific segment
     * 
     * @param string $segmentKey Segment key
     * @param bool $includePricing Include pricing
     * @return self
     */
    public static function forSegment(string $segmentKey, bool $includePricing = false): self
    {
        return new self(
            segmentKeys: [$segmentKey],
            includePricing: $includePricing
        );
    }

    /**
     * Create request for specific passenger
     * 
     * @param string $passengerKey Passenger key
     * @param array|null $preferences Optional preferences
     * @return self
     */
    public static function forPassenger(string $passengerKey, ?array $preferences = null): self
    {
        return new self(
            passengerKeys: [$passengerKey],
            preferences: $preferences
        );
    }
}
