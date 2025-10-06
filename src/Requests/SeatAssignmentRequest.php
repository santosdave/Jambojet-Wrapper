<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Requests\BaseRequest;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Seat Assignment Request for JamboJet NSK API
 * 
 * Used with: POST /api/nsk/v1/booking/seat/assignment
 * 
 * @package SantosDave\JamboJet\Requests
 */
class SeatAssignmentRequest extends BaseRequest
{
    /**
     * Create a new seat assignment request
     * 
     * @param array $seatAssignments Required: Array of seat assignment objects
     *   Structure: [
     *     [
     *       'passengerKey' => 'passenger-key-123',
     *       'segmentKey' => 'segment-key-456', 
     *       'seatNumber' => '12A',
     *       'unitKey' => 'unit-key-789'  // Optional: Specific seat unit key
     *     ]
     *   ]
     * @param bool $autoAssign Optional: Auto-assign seats if specific seats unavailable
     * @param array|null $preferences Optional: Assignment preferences
     * @param string|null $currencyCode Optional: Currency for pricing
     * @param bool $ignorePricing Optional: Ignore seat pricing restrictions
     * @param bool $validateOnly Optional: Validate assignment without committing
     */
    public function __construct(
        public array $seatAssignments,
        public bool $autoAssign = false,
        public ?array $preferences = null,
        public ?string $currencyCode = null,
        public bool $ignorePricing = false,
        public bool $validateOnly = false
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'seatAssignments' => $this->seatAssignments,
            'autoAssign' => $this->autoAssign,
            'preferences' => $this->preferences,
            'currencyCode' => $this->currencyCode,
            'ignorePricing' => $this->ignorePricing,
            'validateOnly' => $this->validateOnly
        ]);
    }

    /**
     * Validate the request
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate required seat assignments
        $this->validateRequired(['seatAssignments' => $this->seatAssignments], ['seatAssignments']);

        if (!is_array($this->seatAssignments) || empty($this->seatAssignments)) {
            throw new JamboJetValidationException('seatAssignments must be a non-empty array');
        }

        // Validate each seat assignment
        foreach ($this->seatAssignments as $index => $assignment) {
            if (!is_array($assignment)) {
                throw new JamboJetValidationException("seatAssignments[{$index}] must be an array");
            }

            $this->validateSeatAssignment($assignment, $index);
        }

        // Validate boolean flags
        if (!is_bool($this->autoAssign)) {
            throw new JamboJetValidationException('autoAssign must be a boolean');
        }

        if (!is_bool($this->ignorePricing)) {
            throw new JamboJetValidationException('ignorePricing must be a boolean');
        }

        if (!is_bool($this->validateOnly)) {
            throw new JamboJetValidationException('validateOnly must be a boolean');
        }

        // Validate currency code if provided
        if ($this->currencyCode !== null) {
            $this->validateFormats(['currencyCode' => $this->currencyCode], ['currencyCode' => 'currency']);
        }

        // Validate preferences if provided
        if ($this->preferences !== null) {
            $this->validatePreferences();
        }
    }

    /**
     * Validate individual seat assignment
     * 
     * @param array $assignment Seat assignment data
     * @param int $index Assignment index for error messages
     * @throws JamboJetValidationException
     */
    private function validateSeatAssignment(array $assignment, int $index): void
    {
        // Validate required fields
        $this->validateRequired($assignment, ['passengerKey', 'segmentKey']);

        // Validate passenger key
        if (!is_string($assignment['passengerKey']) || empty(trim($assignment['passengerKey']))) {
            throw new JamboJetValidationException("seatAssignments[{$index}].passengerKey must be a non-empty string");
        }

        if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $assignment['passengerKey'])) {
            throw new JamboJetValidationException("seatAssignments[{$index}].passengerKey format is invalid");
        }

        // Validate segment key
        if (!is_string($assignment['segmentKey']) || empty(trim($assignment['segmentKey']))) {
            throw new JamboJetValidationException("seatAssignments[{$index}].segmentKey must be a non-empty string");
        }

        if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $assignment['segmentKey'])) {
            throw new JamboJetValidationException("seatAssignments[{$index}].segmentKey format is invalid");
        }

        // Validate seat number if provided (optional for auto-assign)
        if (isset($assignment['seatNumber'])) {
            if (!is_string($assignment['seatNumber']) || empty(trim($assignment['seatNumber']))) {
                throw new JamboJetValidationException("seatAssignments[{$index}].seatNumber must be a non-empty string");
            }

            // Validate seat number format (e.g., 12A, 34F)
            if (!preg_match('/^[0-9]{1,3}[A-Z]{1,2}$/', $assignment['seatNumber'])) {
                throw new JamboJetValidationException("seatAssignments[{$index}].seatNumber must be in format like '12A' or '34F'");
            }
        }

        // Validate unit key if provided
        if (isset($assignment['unitKey'])) {
            if (!is_string($assignment['unitKey']) || empty(trim($assignment['unitKey']))) {
                throw new JamboJetValidationException("seatAssignments[{$index}].unitKey must be a non-empty string");
            }

            if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $assignment['unitKey'])) {
                throw new JamboJetValidationException("seatAssignments[{$index}].unitKey format is invalid");
            }
        }

        // Validate fee acceptance if provided
        if (isset($assignment['acceptFee']) && !is_bool($assignment['acceptFee'])) {
            throw new JamboJetValidationException("seatAssignments[{$index}].acceptFee must be a boolean");
        }

        // Validate max price if provided
        if (isset($assignment['maxPrice']) && (!is_numeric($assignment['maxPrice']) || $assignment['maxPrice'] < 0)) {
            throw new JamboJetValidationException("seatAssignments[{$index}].maxPrice must be a non-negative number");
        }
    }

    /**
     * Validate preferences structure
     * 
     * @throws JamboJetValidationException
     */
    private function validatePreferences(): void
    {
        if (!is_array($this->preferences)) {
            throw new JamboJetValidationException('preferences must be an array');
        }

        // Validate keep together preference
        if (isset($this->preferences['keepTogether']) && !is_bool($this->preferences['keepTogether'])) {
            throw new JamboJetValidationException('preferences.keepTogether must be a boolean');
        }

        // Validate preferred seat types
        if (isset($this->preferences['preferredSeatTypes'])) {
            if (!is_array($this->preferences['preferredSeatTypes'])) {
                throw new JamboJetValidationException('preferences.preferredSeatTypes must be an array');
            }

            $validTypes = ['Window', 'Aisle', 'Middle'];
            foreach ($this->preferences['preferredSeatTypes'] as $index => $type) {
                if (!in_array($type, $validTypes)) {
                    throw new JamboJetValidationException("preferences.preferredSeatTypes[{$index}] must be one of: " . implode(', ', $validTypes));
                }
            }
        }

        // Validate avoid seat types
        if (isset($this->preferences['avoidSeatTypes'])) {
            if (!is_array($this->preferences['avoidSeatTypes'])) {
                throw new JamboJetValidationException('preferences.avoidSeatTypes must be an array');
            }

            $validTypes = ['Exit', 'Bulkhead', 'Galley', 'Lavatory'];
            foreach ($this->preferences['avoidSeatTypes'] as $index => $type) {
                if (!in_array($type, $validTypes)) {
                    throw new JamboJetValidationException("preferences.avoidSeatTypes[{$index}] must be one of: " . implode(', ', $validTypes));
                }
            }
        }

        // Validate max total price
        if (isset($this->preferences['maxTotalPrice']) && (!is_numeric($this->preferences['maxTotalPrice']) || $this->preferences['maxTotalPrice'] < 0)) {
            throw new JamboJetValidationException('preferences.maxTotalPrice must be a non-negative number');
        }
    }

    /**
     * Create request for single seat assignment
     * 
     * @param string $passengerKey Passenger key
     * @param string $segmentKey Segment key
     * @param string $seatNumber Seat number (e.g., '12A')
     * @param bool $acceptFee Accept seat fees
     * @return self
     */
    public static function forSingleSeat(string $passengerKey, string $segmentKey, string $seatNumber, bool $acceptFee = true): self
    {
        return new self(
            seatAssignments: [
                [
                    'passengerKey' => $passengerKey,
                    'segmentKey' => $segmentKey,
                    'seatNumber' => $seatNumber,
                    'acceptFee' => $acceptFee
                ]
            ]
        );
    }

    /**
     * Create request for auto-assignment
     * 
     * @param array $passengerSegmentPairs Array of ['passengerKey' => 'key', 'segmentKey' => 'key'] pairs
     * @param array|null $preferences Optional assignment preferences
     * @return self
     */
    public static function forAutoAssignment(array $passengerSegmentPairs, ?array $preferences = null): self
    {
        $assignments = [];
        foreach ($passengerSegmentPairs as $pair) {
            $assignments[] = [
                'passengerKey' => $pair['passengerKey'],
                'segmentKey' => $pair['segmentKey']
            ];
        }

        return new self(
            seatAssignments: $assignments,
            autoAssign: true,
            preferences: $preferences
        );
    }
}
