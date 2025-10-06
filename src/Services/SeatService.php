<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\SeatInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Seat Service for JamboJet NSK API
 * 
 * Handles seat selection, assignment, availability checking, and seat map operations
 * for bookings in the NSK system
 * Base endpoints: /api/nsk/v1/booking/seat
 * 
 * Supported endpoints:
 * - GET /api/nsk/v1/booking/seat/availability - Get seat availability
 * - POST /api/nsk/v1/booking/seat/assignment - Assign seats
 * - DELETE /api/nsk/v1/booking/seat/assignment/{seatAssignmentKey} - Remove assignment
 * - GET /api/nsk/v1/booking/seat/assignments - Get current assignments
 * - PUT /api/nsk/v1/booking/seat/assignment/{seatAssignmentKey} - Update assignment
 * - GET /api/nsk/v1/booking/seat/map/{segmentKey} - Get seat map
 * - GET /api/nsk/v1/booking/seat/pricing - Get seat pricing
 * - POST /api/nsk/v1/booking/seat/autoAssign - Auto-assign seats
 * 
 * @package SantosDave\JamboJet\Services
 */
class SeatService implements SeatInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // INTERFACE REQUIRED METHODS - SEAT OPERATIONS
    // =================================================================

    /**
     * Get seat availability for booking in state
     * 
     * GET /api/nsk/v1/booking/seat/availability
     * Retrieves available seats for all segments in the current booking
     * 
     * @param array $criteria Availability criteria (segment, cabin class, etc.)
     * @return array Seat availability information
     * @throws JamboJetApiException
     */
    public function getSeatAvailability(array $criteria = []): array
    {
        $this->validateAvailabilityCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/seat/availability', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get seat availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Assign seats to passengers in booking
     * 
     * POST /api/nsk/v1/booking/seat/assignment
     * Assigns specific seats to passengers for flight segments
     * 
     * @param array $seatAssignments Array of seat assignment data
     * @return array Seat assignment response
     * @throws JamboJetApiException
     */
    public function assignSeats(array $seatAssignments): array
    {
        $this->validateSeatAssignments($seatAssignments);

        try {
            return $this->post('api/nsk/v1/booking/seat/assignment', $seatAssignments);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to assign seats: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove seat assignment
     * 
     * DELETE /api/nsk/v1/booking/seat/assignment/{seatAssignmentKey}
     * Removes a specific seat assignment from the booking
     * 
     * @param string $seatAssignmentKey Seat assignment key
     * @return array Removal response
     * @throws JamboJetApiException
     */
    public function removeSeatAssignment(string $seatAssignmentKey): array
    {
        $this->validateSeatAssignmentKey($seatAssignmentKey);

        try {
            return $this->delete("api/nsk/v1/booking/seat/assignment/{$seatAssignmentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove seat assignment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get current seat assignments for booking
     * 
     * GET /api/nsk/v1/booking/seat/assignments
     * Retrieves all current seat assignments for the booking
     * 
     * @return array Current seat assignments
     * @throws JamboJetApiException
     */
    public function getSeatAssignments(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/seat/assignments');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get seat assignments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update seat assignment
     * 
     * PUT /api/nsk/v1/booking/seat/assignment/{seatAssignmentKey}
     * Updates an existing seat assignment with new seat information
     * 
     * @param string $seatAssignmentKey Seat assignment key
     * @param array $updateData Update data
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateSeatAssignment(string $seatAssignmentKey, array $updateData): array
    {
        $this->validateSeatAssignmentKey($seatAssignmentKey);
        $this->validateSeatAssignmentUpdate($updateData);

        try {
            return $this->put("api/nsk/v1/booking/seat/assignment/{$seatAssignmentKey}", $updateData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update seat assignment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get seat map for flight segment
     * 
     * GET /api/nsk/v1/booking/seat/map/{segmentKey}
     * Retrieves the seat map layout for a specific flight segment
     * 
     * @param string $segmentKey Flight segment key
     * @param array $options Seat map options (showPricing, showAvailability, etc.)
     * @return array Seat map information
     * @throws JamboJetApiException
     */
    public function getSeatMap(string $segmentKey, array $options = []): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validateSeatMapOptions($options);

        try {
            return $this->get("api/nsk/v1/booking/seat/map/{$segmentKey}", $options);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get seat map: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get seat pricing information
     * 
     * GET /api/nsk/v1/booking/seat/pricing
     * Retrieves pricing information for seat selections
     * 
     * @param array $pricingCriteria Pricing criteria
     * @return array Seat pricing information
     * @throws JamboJetApiException
     */
    public function getSeatPricing(array $pricingCriteria): array
    {
        $this->validatePricingCriteria($pricingCriteria);

        try {
            return $this->get('api/nsk/v1/booking/seat/pricing', $pricingCriteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get seat pricing: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Auto-assign seats based on preferences
     * 
     * POST /api/nsk/v1/booking/seat/autoAssign
     * Automatically assigns seats to passengers based on preferences and availability
     * 
     * @param array $preferences Assignment preferences
     * @return array Auto-assignment response
     * @throws JamboJetApiException
     */
    public function autoAssignSeats(array $preferences = []): array
    {
        $this->validateAutoAssignPreferences($preferences);

        try {
            return $this->post('api/nsk/v1/booking/seat/autoAssign', $preferences);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to auto-assign seats: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // CONVENIENCE METHODS
    // =================================================================

    /**
     * Assign specific seat to passenger
     * 
     * @param string $passengerKey Passenger key
     * @param string $segmentKey Segment key
     * @param string $seatNumber Seat number (e.g., "12A")
     * @return array Assignment response
     */
    public function assignSeat(string $passengerKey, string $segmentKey, string $seatNumber): array
    {
        return $this->assignSeats([
            'assignments' => [
                [
                    'passengerKey' => $passengerKey,
                    'segmentKey' => $segmentKey,
                    'seatNumber' => $seatNumber
                ]
            ]
        ]);
    }

    /**
     * Get available window seats
     * 
     * @param string $segmentKey Segment key
     * @return array Available window seats
     */
    public function getWindowSeats(string $segmentKey): array
    {
        return $this->getSeatAvailability([
            'segmentKey' => $segmentKey,
            'seatType' => 'Window'
        ]);
    }

    /**
     * Get available aisle seats
     * 
     * @param string $segmentKey Segment key
     * @return array Available aisle seats
     */
    public function getAisleSeats(string $segmentKey): array
    {
        return $this->getSeatAvailability([
            'segmentKey' => $segmentKey,
            'seatType' => 'Aisle'
        ]);
    }

    /**
     * Get premium seats (extra legroom, etc.)
     * 
     * @param string $segmentKey Segment key
     * @return array Available premium seats
     */
    public function getPremiumSeats(string $segmentKey): array
    {
        return $this->getSeatAvailability([
            'segmentKey' => $segmentKey,
            'seatCategory' => 'Premium'
        ]);
    }

    /**
     * Auto-assign seats for family traveling together
     * 
     * @param array $familyPassengerKeys Array of passenger keys
     * @return array Assignment response
     */
    public function assignFamilySeats(array $familyPassengerKeys): array
    {
        return $this->autoAssignSeats([
            'passengerKeys' => $familyPassengerKeys,
            'keepTogether' => true,
            'preferences' => ['familyFriendly' => true]
        ]);
    }

    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND COMPLETE
    // =================================================================

    /**
     * Validate availability criteria
     * 
     * @param array $criteria Availability criteria
     * @throws JamboJetValidationException
     */
    private function validateAvailabilityCriteria(array $criteria): void
    {
        if (isset($criteria['segmentKey'])) {
            $this->validateSegmentKey($criteria['segmentKey']);
        }

        if (isset($criteria['cabinClass'])) {
            $validCabinClasses = ['Economy', 'Premium', 'Business', 'First'];
            if (!in_array($criteria['cabinClass'], $validCabinClasses)) {
                throw new JamboJetValidationException(
                    'Invalid cabin class. Expected one of: ' . implode(', ', $validCabinClasses)
                );
            }
        }

        if (isset($criteria['seatType'])) {
            $validSeatTypes = ['Window', 'Middle', 'Aisle', 'Any'];
            if (!in_array($criteria['seatType'], $validSeatTypes)) {
                throw new JamboJetValidationException(
                    'Invalid seat type. Expected one of: ' . implode(', ', $validSeatTypes)
                );
            }
        }

        if (isset($criteria['seatCategory'])) {
            $validCategories = ['Standard', 'Premium', 'ExtraLegroom', 'Preferred'];
            if (!in_array($criteria['seatCategory'], $validCategories)) {
                throw new JamboJetValidationException(
                    'Invalid seat category. Expected one of: ' . implode(', ', $validCategories)
                );
            }
        }

        if (isset($criteria['maxPrice'])) {
            if (!is_numeric($criteria['maxPrice']) || $criteria['maxPrice'] < 0) {
                throw new JamboJetValidationException(
                    'Max price must be a non-negative number'
                );
            }
        }

        if (isset($criteria['includeOccupied']) && !is_bool($criteria['includeOccupied'])) {
            throw new JamboJetValidationException('includeOccupied must be a boolean value');
        }
    }

    /**
     * Validate seat assignments
     * 
     * @param array $seatAssignments Seat assignment data
     * @throws JamboJetValidationException
     */
    private function validateSeatAssignments(array $seatAssignments): void
    {
        if (!isset($seatAssignments['assignments']) || !is_array($seatAssignments['assignments'])) {
            throw new JamboJetValidationException('assignments array is required');
        }

        if (empty($seatAssignments['assignments'])) {
            throw new JamboJetValidationException('At least one seat assignment is required');
        }

        foreach ($seatAssignments['assignments'] as $index => $assignment) {
            $this->validateSingleSeatAssignment($assignment, $index);
        }

        // Validate assignment options if provided
        if (isset($seatAssignments['options'])) {
            $this->validateAssignmentOptions($seatAssignments['options']);
        }
    }

    /**
     * Validate single seat assignment
     * 
     * @param array $assignment Single assignment data
     * @param int $index Assignment index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateSingleSeatAssignment(array $assignment, int $index): void
    {
        // Required fields for seat assignment
        $this->validateRequired($assignment, ['passengerKey', 'segmentKey', 'seatNumber']);

        // Validate passenger key
        $this->validatePassengerKey($assignment['passengerKey']);

        // Validate segment key
        $this->validateSegmentKey($assignment['segmentKey']);

        // Validate seat number format
        $this->validateSeatNumber($assignment['seatNumber']);

        // Validate optional fields
        if (isset($assignment['seatPrice'])) {
            if (!is_numeric($assignment['seatPrice']) || $assignment['seatPrice'] < 0) {
                throw new JamboJetValidationException(
                    "Seat price for assignment {$index} must be a non-negative number"
                );
            }
        }

        if (isset($assignment['currency'])) {
            $this->validateFormats($assignment, ['currency' => 'currency_code']);
        }

        if (isset($assignment['unitKey'])) {
            if (empty(trim($assignment['unitKey']))) {
                throw new JamboJetValidationException(
                    "Unit key for assignment {$index} cannot be empty"
                );
            }
        }
    }

    /**
     * Validate assignment options
     * 
     * @param array $options Assignment options
     * @throws JamboJetValidationException
     */
    private function validateAssignmentOptions(array $options): void
    {
        if (isset($options['overridePricing']) && !is_bool($options['overridePricing'])) {
            throw new JamboJetValidationException('overridePricing must be a boolean value');
        }

        if (isset($options['validateAvailability']) && !is_bool($options['validateAvailability'])) {
            throw new JamboJetValidationException('validateAvailability must be a boolean value');
        }

        if (isset($options['allowUpgrade']) && !is_bool($options['allowUpgrade'])) {
            throw new JamboJetValidationException('allowUpgrade must be a boolean value');
        }
    }

    /**
     * Validate seat assignment update
     * 
     * @param array $updateData Update data
     * @throws JamboJetValidationException
     */
    private function validateSeatAssignmentUpdate(array $updateData): void
    {
        // For updates, most fields are optional but must be valid if provided

        if (isset($updateData['seatNumber'])) {
            $this->validateSeatNumber($updateData['seatNumber']);
        }

        if (isset($updateData['segmentKey'])) {
            $this->validateSegmentKey($updateData['segmentKey']);
        }

        if (isset($updateData['seatPrice'])) {
            if (!is_numeric($updateData['seatPrice']) || $updateData['seatPrice'] < 0) {
                throw new JamboJetValidationException(
                    'Seat price must be a non-negative number'
                );
            }
        }

        if (isset($updateData['currency'])) {
            $this->validateFormats($updateData, ['currency' => 'currency_code']);
        }

        if (isset($updateData['notes'])) {
            $this->validateStringLengths($updateData, ['notes' => ['max' => 500]]);
        }
    }

    /**
     * Validate seat map options
     * 
     * @param array $options Seat map options
     * @throws JamboJetValidationException
     */
    private function validateSeatMapOptions(array $options): void
    {
        if (isset($options['showPricing']) && !is_bool($options['showPricing'])) {
            throw new JamboJetValidationException('showPricing must be a boolean value');
        }

        if (isset($options['showAvailability']) && !is_bool($options['showAvailability'])) {
            throw new JamboJetValidationException('showAvailability must be a boolean value');
        }

        if (isset($options['includeBlocked']) && !is_bool($options['includeBlocked'])) {
            throw new JamboJetValidationException('includeBlocked must be a boolean value');
        }

        if (isset($options['cabinClass'])) {
            $validCabinClasses = ['Economy', 'Premium', 'Business', 'First'];
            if (!in_array($options['cabinClass'], $validCabinClasses)) {
                throw new JamboJetValidationException(
                    'Invalid cabin class. Expected one of: ' . implode(', ', $validCabinClasses)
                );
            }
        }

        if (isset($options['format'])) {
            $validFormats = ['Standard', 'Detailed', 'Compact'];
            if (!in_array($options['format'], $validFormats)) {
                throw new JamboJetValidationException(
                    'Invalid format. Expected one of: ' . implode(', ', $validFormats)
                );
            }
        }
    }

    /**
     * Validate pricing criteria
     * 
     * @param array $pricingCriteria Pricing criteria
     * @throws JamboJetValidationException
     */
    private function validatePricingCriteria(array $pricingCriteria): void
    {
        if (isset($pricingCriteria['segmentKeys']) && is_array($pricingCriteria['segmentKeys'])) {
            foreach ($pricingCriteria['segmentKeys'] as $segmentKey) {
                $this->validateSegmentKey($segmentKey);
            }
        }

        if (isset($pricingCriteria['seatNumbers']) && is_array($pricingCriteria['seatNumbers'])) {
            foreach ($pricingCriteria['seatNumbers'] as $seatNumber) {
                $this->validateSeatNumber($seatNumber);
            }
        }

        if (isset($pricingCriteria['currency'])) {
            $this->validateFormats($pricingCriteria, ['currency' => 'currency_code']);
        }

        if (isset($pricingCriteria['includeTaxes']) && !is_bool($pricingCriteria['includeTaxes'])) {
            throw new JamboJetValidationException('includeTaxes must be a boolean value');
        }
    }

    /**
     * Validate auto-assign preferences
     * 
     * @param array $preferences Assignment preferences
     * @throws JamboJetValidationException
     */
    private function validateAutoAssignPreferences(array $preferences): void
    {
        if (isset($preferences['passengerKeys']) && is_array($preferences['passengerKeys'])) {
            foreach ($preferences['passengerKeys'] as $passengerKey) {
                $this->validatePassengerKey($passengerKey);
            }
        }

        if (isset($preferences['keepTogether']) && !is_bool($preferences['keepTogether'])) {
            throw new JamboJetValidationException('keepTogether must be a boolean value');
        }

        if (isset($preferences['seatType'])) {
            $validSeatTypes = ['Window', 'Middle', 'Aisle', 'Any'];
            if (!in_array($preferences['seatType'], $validSeatTypes)) {
                throw new JamboJetValidationException(
                    'Invalid seat type. Expected one of: ' . implode(', ', $validSeatTypes)
                );
            }
        }

        if (isset($preferences['maxPrice'])) {
            if (!is_numeric($preferences['maxPrice']) || $preferences['maxPrice'] < 0) {
                throw new JamboJetValidationException(
                    'Max price must be a non-negative number'
                );
            }
        }

        if (isset($preferences['avoidMiddleSeats']) && !is_bool($preferences['avoidMiddleSeats'])) {
            throw new JamboJetValidationException('avoidMiddleSeats must be a boolean value');
        }

        if (isset($preferences['preferFront']) && !is_bool($preferences['preferFront'])) {
            throw new JamboJetValidationException('preferFront must be a boolean value');
        }
    }

    /**
     * Validate seat assignment key
     * 
     * @param string $seatAssignmentKey Seat assignment key
     * @throws JamboJetValidationException
     */
    private function validateSeatAssignmentKey(string $seatAssignmentKey): void
    {
        if (empty(trim($seatAssignmentKey))) {
            throw new JamboJetValidationException('Seat assignment key cannot be empty');
        }

        if (strlen($seatAssignmentKey) < 5) {
            throw new JamboJetValidationException('Invalid seat assignment key format');
        }
    }

    /**
     * Validate segment key
     * 
     * @param string $segmentKey Segment key
     * @throws JamboJetValidationException
     */
    private function validateSegmentKey(string $segmentKey): void
    {
        if (empty(trim($segmentKey))) {
            throw new JamboJetValidationException('Segment key cannot be empty');
        }

        if (strlen($segmentKey) < 5) {
            throw new JamboJetValidationException('Invalid segment key format');
        }
    }

    /**
     * Validate passenger key
     * 
     * @param string $passengerKey Passenger key
     * @throws JamboJetValidationException
     */
    private function validatePassengerKey(string $passengerKey): void
    {
        if (empty(trim($passengerKey))) {
            throw new JamboJetValidationException('Passenger key cannot be empty');
        }

        if (strlen($passengerKey) < 5) {
            throw new JamboJetValidationException('Invalid passenger key format');
        }
    }

    /**
     * Validate seat number format
     * 
     * @param string $seatNumber Seat number
     * @throws JamboJetValidationException
     */
    private function validateSeatNumber(string $seatNumber): void
    {
        if (empty(trim($seatNumber))) {
            throw new JamboJetValidationException('Seat number cannot be empty');
        }

        // Seat numbers are typically format like "12A", "1F", "23D"
        if (!preg_match('/^[0-9]{1,3}[A-Z]$/', $seatNumber)) {
            throw new JamboJetValidationException(
                'Invalid seat number format. Expected format: 12A, 1F, etc.'
            );
        }
    }
}
