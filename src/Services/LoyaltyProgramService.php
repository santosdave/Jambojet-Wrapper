<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\LoyaltyProgramInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Loyalty Program Service for JamboJet NSK API
 * 
 * Handles loyalty program operations including adding programs to bookings,
 * managing membership validation, checking balances, and accessing benefits
 * Base endpoints: /api/nsk/v1/booking/loyaltyPrograms, /api/nsk/v1/loyaltyPrograms
 * 
 * Supported endpoints:
 * - GET /api/nsk/v1/booking/loyaltyPrograms - Get loyalty programs for booking
 * - POST /api/nsk/v1/booking/loyaltyPrograms - Add loyalty program to booking
 * - PUT /api/nsk/v1/booking/loyaltyPrograms/{loyaltyProgramKey} - Update loyalty program
 * - DELETE /api/nsk/v1/booking/loyaltyPrograms/{loyaltyProgramKey} - Remove loyalty program
 * - GET /api/nsk/v1/resources/loyaltyPrograms - Get available loyalty programs
 * - GET /api/nsk/v1/user/loyaltyPrograms/{programCode}/balance - Get balance
 * - POST /api/nsk/v1/loyaltyPrograms/validate - Validate membership
 * - GET /api/nsk/v1/loyaltyPrograms/{programCode}/benefits - Get benefits
 * 
 * @package SantosDave\JamboJet\Services
 */
class LoyaltyProgramService implements LoyaltyProgramInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // INTERFACE REQUIRED METHODS - STATEFUL OPERATIONS
    // =================================================================

    /**
     * Get loyalty programs for booking in state
     * 
     * GET /api/nsk/v1/booking/loyaltyPrograms
     * Retrieves all loyalty programs associated with the current booking
     * 
     * @return array Loyalty programs in booking
     * @throws JamboJetApiException
     */
    public function getLoyaltyPrograms(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/loyaltyPrograms');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get loyalty programs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add loyalty program to booking in state
     * 
     * POST /api/nsk/v1/booking/loyaltyPrograms
     * Adds a loyalty program to the current booking for a specific passenger
     * 
     * @param array $loyaltyProgramData Loyalty program data
     * @return array Add loyalty program response
     * @throws JamboJetApiException
     */
    public function addLoyaltyProgram(array $loyaltyProgramData): array
    {
        $this->validateLoyaltyProgramAddRequest($loyaltyProgramData);

        try {
            return $this->post('api/nsk/v1/booking/loyaltyPrograms', $loyaltyProgramData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add loyalty program: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update loyalty program in booking
     * 
     * PUT /api/nsk/v1/booking/loyaltyPrograms/{loyaltyProgramKey}
     * Updates loyalty program information for the booking
     * 
     * @param string $loyaltyProgramKey Loyalty program key
     * @param array $updateData Update data
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateLoyaltyProgram(string $loyaltyProgramKey, array $updateData): array
    {
        $this->validateLoyaltyProgramKey($loyaltyProgramKey);
        $this->validateLoyaltyProgramUpdateRequest($updateData);

        try {
            return $this->put("api/nsk/v1/booking/loyaltyPrograms/{$loyaltyProgramKey}", $updateData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update loyalty program: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove loyalty program from booking
     * 
     * DELETE /api/nsk/v1/booking/loyaltyPrograms/{loyaltyProgramKey}
     * Removes a loyalty program from the current booking
     * 
     * @param string $loyaltyProgramKey Loyalty program key
     * @return array Removal response
     * @throws JamboJetApiException
     */
    public function removeLoyaltyProgram(string $loyaltyProgramKey): array
    {
        $this->validateLoyaltyProgramKey($loyaltyProgramKey);

        try {
            return $this->delete("api/nsk/v1/booking/loyaltyPrograms/{$loyaltyProgramKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove loyalty program: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // EXTENDED METHODS - STATELESS OPERATIONS
    // =================================================================

    /**
     * Get available loyalty program types
     * 
     * GET /api/nsk/v1/resources/loyaltyPrograms
     * Retrieves all available loyalty programs that can be used
     * 
     * @return array Available loyalty programs
     * @throws JamboJetApiException
     */
    public function getAvailableLoyaltyPrograms(): array
    {
        try {
            return $this->get('api/nsk/v1/resources/loyaltyPrograms');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get available loyalty programs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get loyalty program balance/status
     * 
     * GET /api/nsk/v1/user/loyaltyPrograms/{programCode}/balance
     * Retrieves the current balance and status for a loyalty program membership
     * 
     * @param string $programCode Program code (e.g., 'FF' for frequent flyer)
     * @param string $membershipNumber Membership number
     * @return array Balance and status information
     * @throws JamboJetApiException
     */
    public function getLoyaltyProgramBalance(string $programCode, string $membershipNumber): array
    {
        $this->validateProgramCode($programCode);
        $this->validateMembershipNumber($membershipNumber);

        try {
            return $this->get("api/nsk/v1/user/loyaltyPrograms/{$programCode}/balance", [
                'membershipNumber' => $membershipNumber
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get loyalty program balance: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate loyalty program membership
     * 
     * POST /api/nsk/v1/loyaltyPrograms/validate
     * Validates if a loyalty program membership is active and valid
     * 
     * @param array $membershipData Membership validation data
     * @return array Validation response
     * @throws JamboJetApiException
     */
    public function validateMembership(array $membershipData): array
    {
        $this->validateMembershipValidationRequest($membershipData);

        try {
            return $this->post('api/nsk/v1/loyaltyPrograms/validate', $membershipData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to validate membership: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get loyalty program benefits for flight
     * 
     * GET /api/nsk/v1/loyaltyPrograms/{programCode}/benefits
     * Retrieves available benefits for a specific loyalty program
     * 
     * @param string $programCode Program code
     * @param array $criteria Benefit criteria (tier, route, etc.)
     * @return array Program benefits
     * @throws JamboJetApiException
     */
    public function getProgramBenefits(string $programCode, array $criteria = []): array
    {
        $this->validateProgramCode($programCode);
        $this->validateBenefitsCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/loyaltyPrograms/{$programCode}/benefits", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get program benefits: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // CONVENIENCE METHODS
    // =================================================================

    /**
     * Add frequent flyer program to booking
     * 
     * @param string $passengerKey Passenger key
     * @param string $membershipNumber FF membership number
     * @param string $tier Optional tier level
     * @return array Add response
     */
    public function addFrequentFlyer(string $passengerKey, string $membershipNumber, string $tier = null): array
    {
        $data = [
            'passengerKey' => $passengerKey,
            'programCode' => 'FF',
            'membershipNumber' => $membershipNumber
        ];

        if ($tier) {
            $data['tier'] = $tier;
        }

        return $this->addLoyaltyProgram($data);
    }

    /**
     * Get frequent flyer benefits for current user
     * 
     * @param string $membershipNumber FF membership number
     * @return array Benefits information
     */
    public function getFrequentFlyerBenefits(string $membershipNumber): array
    {
        return $this->getProgramBenefits('FF', [
            'membershipNumber' => $membershipNumber
        ]);
    }

    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND COMPLETE
    // =================================================================

    /**
     * Validate loyalty program add request
     * 
     * @param array $data Loyalty program add data
     * @throws JamboJetValidationException
     */
    private function validateLoyaltyProgramAddRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['passengerKey', 'programCode', 'membershipNumber']);

        // Validate passenger key
        $this->validatePassengerKey($data['passengerKey']);

        // Validate program code
        $this->validateProgramCode($data['programCode']);

        // Validate membership number
        $this->validateMembershipNumber($data['membershipNumber']);

        // Validate tier if provided
        if (isset($data['tier'])) {
            $this->validateTier($data['tier']);
        }

        // Validate elite status if provided
        if (isset($data['eliteStatus'])) {
            $validEliteStatuses = ['None', 'Silver', 'Gold', 'Platinum', 'Diamond'];
            if (!in_array($data['eliteStatus'], $validEliteStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid elite status. Expected one of: ' . implode(', ', $validEliteStatuses)
                );
            }
        }

        // Validate points balance if provided
        if (isset($data['pointsBalance'])) {
            if (!is_numeric($data['pointsBalance']) || $data['pointsBalance'] < 0) {
                throw new JamboJetValidationException(
                    'Points balance must be a non-negative number'
                );
            }
        }

        // Validate program type if provided
        if (isset($data['programType'])) {
            $validTypes = ['FrequentFlyer', 'Hotel', 'CreditCard', 'Partner'];
            if (!in_array($data['programType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid program type. Expected one of: ' . implode(', ', $validTypes)
                );
            }
        }
    }

    /**
     * Validate loyalty program update request
     * 
     * @param array $data Update data
     * @throws JamboJetValidationException
     */
    private function validateLoyaltyProgramUpdateRequest(array $data): void
    {
        // For updates, fields are optional but must be valid if provided

        if (isset($data['membershipNumber'])) {
            $this->validateMembershipNumber($data['membershipNumber']);
        }

        if (isset($data['tier'])) {
            $this->validateTier($data['tier']);
        }

        if (isset($data['eliteStatus'])) {
            $validEliteStatuses = ['None', 'Silver', 'Gold', 'Platinum', 'Diamond'];
            if (!in_array($data['eliteStatus'], $validEliteStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid elite status. Expected one of: ' . implode(', ', $validEliteStatuses)
                );
            }
        }

        if (isset($data['pointsBalance'])) {
            if (!is_numeric($data['pointsBalance']) || $data['pointsBalance'] < 0) {
                throw new JamboJetValidationException(
                    'Points balance must be a non-negative number'
                );
            }
        }

        if (isset($data['isActive']) && !is_bool($data['isActive'])) {
            throw new JamboJetValidationException('isActive must be a boolean value');
        }
    }

    /**
     * Validate membership validation request
     * 
     * @param array $data Membership validation data
     * @throws JamboJetValidationException
     */
    private function validateMembershipValidationRequest(array $data): void
    {
        $this->validateRequired($data, ['programCode', 'membershipNumber']);

        $this->validateProgramCode($data['programCode']);
        $this->validateMembershipNumber($data['membershipNumber']);

        // Validate additional validation fields if provided
        if (isset($data['lastName'])) {
            if (empty(trim($data['lastName']))) {
                throw new JamboJetValidationException('Last name cannot be empty');
            }
            $this->validateStringLengths($data, ['lastName' => ['max' => 50]]);
        }

        if (isset($data['dateOfBirth'])) {
            $this->validateFormats($data, ['dateOfBirth' => 'date']);
        }

        if (isset($data['postalCode'])) {
            $this->validateStringLengths($data, ['postalCode' => ['max' => 20]]);
        }
    }

    /**
     * Validate benefits criteria
     * 
     * @param array $criteria Benefits criteria
     * @throws JamboJetValidationException
     */
    private function validateBenefitsCriteria(array $criteria): void
    {
        if (isset($criteria['tier'])) {
            $this->validateTier($criteria['tier']);
        }

        if (isset($criteria['routeType'])) {
            $validRouteTypes = ['Domestic', 'International', 'Regional'];
            if (!in_array($criteria['routeType'], $validRouteTypes)) {
                throw new JamboJetValidationException(
                    'Invalid route type. Expected one of: ' . implode(', ', $validRouteTypes)
                );
            }
        }

        if (isset($criteria['cabinClass'])) {
            $validCabinClasses = ['Economy', 'Premium', 'Business', 'First'];
            if (!in_array($criteria['cabinClass'], $validCabinClasses)) {
                throw new JamboJetValidationException(
                    'Invalid cabin class. Expected one of: ' . implode(', ', $validCabinClasses)
                );
            }
        }

        if (isset($criteria['membershipNumber'])) {
            $this->validateMembershipNumber($criteria['membershipNumber']);
        }
    }

    /**
     * Validate loyalty program key
     * 
     * @param string $loyaltyProgramKey Loyalty program key
     * @throws JamboJetValidationException
     */
    private function validateLoyaltyProgramKey(string $loyaltyProgramKey): void
    {
        if (empty(trim($loyaltyProgramKey))) {
            throw new JamboJetValidationException('Loyalty program key cannot be empty');
        }

        if (strlen($loyaltyProgramKey) < 5) {
            throw new JamboJetValidationException('Invalid loyalty program key format');
        }
    }

    /**
     * Validate program code
     * 
     * @param string $programCode Program code
     * @throws JamboJetValidationException
     */
    private function validateProgramCode(string $programCode): void
    {
        if (empty(trim($programCode))) {
            throw new JamboJetValidationException('Program code cannot be empty');
        }

        if (!preg_match('/^[A-Z]{2,10}$/', $programCode)) {
            throw new JamboJetValidationException(
                'Program code must be 2-10 characters, uppercase letters only'
            );
        }
    }

    /**
     * Validate membership number
     * 
     * @param string $membershipNumber Membership number
     * @throws JamboJetValidationException
     */
    private function validateMembershipNumber(string $membershipNumber): void
    {
        if (empty(trim($membershipNumber))) {
            throw new JamboJetValidationException('Membership number cannot be empty');
        }

        if (strlen($membershipNumber) < 3 || strlen($membershipNumber) > 50) {
            throw new JamboJetValidationException(
                'Membership number must be 3-50 characters'
            );
        }

        if (!preg_match('/^[A-Za-z0-9\-]+$/', $membershipNumber)) {
            throw new JamboJetValidationException(
                'Membership number can only contain letters, numbers, and hyphens'
            );
        }
    }

    /**
     * Validate tier
     * 
     * @param string $tier Tier level
     * @throws JamboJetValidationException
     */
    private function validateTier(string $tier): void
    {
        $validTiers = ['Base', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Elite'];
        if (!in_array($tier, $validTiers)) {
            throw new JamboJetValidationException(
                'Invalid tier. Expected one of: ' . implode(', ', $validTiers)
            );
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
}
