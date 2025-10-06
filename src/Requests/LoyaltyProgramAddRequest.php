<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Requests\BaseRequest;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Loyalty Program Add Request for JamboJet NSK API
 * 
 * Used with: POST /api/nsk/v1/booking/loyaltyPrograms
 * 
 * @package SantosDave\JamboJet\Requests
 */
class LoyaltyProgramAddRequest extends BaseRequest
{
    /**
     * Create a new loyalty program add request
     * 
     * @param string $passengerKey Required: Passenger key to add loyalty program to
     * @param string $programCode Required: Loyalty program code (e.g., 'FF' for frequent flyer)
     * @param string $membershipNumber Required: Membership number in the loyalty program
     * @param string|null $tier Optional: Membership tier level (Silver, Gold, Platinum, etc.)
     * @param string|null $eliteStatus Optional: Elite status level
     * @param array|null $benefits Optional: Specific benefits to apply
     * @param bool $validateMembership Optional: Validate membership with program (default: true)
     * @param bool $applyBenefits Optional: Apply tier benefits automatically (default: true)
     * @param string|null $expiryDate Optional: Membership expiry date (YYYY-MM-DD)
     * @param array|null $customData Optional: Custom program-specific data
     */
    public function __construct(
        public string $passengerKey,
        public string $programCode,
        public string $membershipNumber,
        public ?string $tier = null,
        public ?string $eliteStatus = null,
        public ?array $benefits = null,
        public bool $validateMembership = true,
        public bool $applyBenefits = true,
        public ?string $expiryDate = null,
        public ?array $customData = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'passengerKey' => $this->passengerKey,
            'programCode' => $this->programCode,
            'membershipNumber' => $this->membershipNumber,
            'tier' => $this->tier,
            'eliteStatus' => $this->eliteStatus,
            'benefits' => $this->benefits,
            'validateMembership' => $this->validateMembership,
            'applyBenefits' => $this->applyBenefits,
            'expiryDate' => $this->expiryDate,
            'customData' => $this->customData
        ]);
    }

    /**
     * Validate the request
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate required fields
        $this->validateRequired([
            'passengerKey' => $this->passengerKey,
            'programCode' => $this->programCode,
            'membershipNumber' => $this->membershipNumber
        ], ['passengerKey', 'programCode', 'membershipNumber']);

        // Validate passenger key
        $this->validatePassengerKey();

        // Validate program code
        $this->validateProgramCode();

        // Validate membership number
        $this->validateMembershipNumber();

        // Validate tier if provided
        if ($this->tier !== null) {
            $this->validateTier();
        }

        // Validate elite status if provided
        if ($this->eliteStatus !== null) {
            $this->validateEliteStatus();
        }

        // Validate benefits if provided
        if ($this->benefits !== null) {
            $this->validateBenefits();
        }

        // Validate boolean flags
        if (!is_bool($this->validateMembership)) {
            throw new JamboJetValidationException('validateMembership must be a boolean');
        }

        if (!is_bool($this->applyBenefits)) {
            throw new JamboJetValidationException('applyBenefits must be a boolean');
        }

        // Validate expiry date if provided
        if ($this->expiryDate !== null) {
            $this->validateFormats(['expiryDate' => $this->expiryDate], ['expiryDate' => 'date']);

            // Check if expiry date is in the future
            if (strtotime($this->expiryDate) <= time()) {
                throw new JamboJetValidationException('expiryDate must be in the future');
            }
        }

        // Validate custom data if provided
        if ($this->customData !== null && !is_array($this->customData)) {
            throw new JamboJetValidationException('customData must be an array');
        }
    }

    /**
     * Validate passenger key
     * 
     * @throws JamboJetValidationException
     */
    private function validatePassengerKey(): void
    {
        if (!is_string($this->passengerKey) || empty(trim($this->passengerKey))) {
            throw new JamboJetValidationException('passengerKey must be a non-empty string');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $this->passengerKey)) {
            throw new JamboJetValidationException('passengerKey format is invalid');
        }
    }

    /**
     * Validate program code
     * 
     * @throws JamboJetValidationException
     */
    private function validateProgramCode(): void
    {
        if (!is_string($this->programCode) || empty(trim($this->programCode))) {
            throw new JamboJetValidationException('programCode must be a non-empty string');
        }

        // Program codes are typically 2-10 uppercase alphanumeric characters
        if (!preg_match('/^[A-Z0-9]{2,10}$/', $this->programCode)) {
            throw new JamboJetValidationException('programCode must be 2-10 uppercase alphanumeric characters');
        }

        // Validate against known program codes
        $validPrograms = [
            'FF',       // Frequent Flyer
            'LP',       // Loyalty Program
            'VIP',      // VIP Program
            'CORP',     // Corporate Program
            'MILES',    // Miles Program
            'POINTS',   // Points Program
            'ELITE',    // Elite Program
            'PREMIUM'   // Premium Program
        ];

        if (!in_array($this->programCode, $validPrograms)) {
            throw new JamboJetValidationException('programCode must be one of: ' . implode(', ', $validPrograms));
        }
    }

    /**
     * Validate membership number
     * 
     * @throws JamboJetValidationException
     */
    private function validateMembershipNumber(): void
    {
        if (!is_string($this->membershipNumber) || empty(trim($this->membershipNumber))) {
            throw new JamboJetValidationException('membershipNumber must be a non-empty string');
        }

        // Membership numbers are typically 6-20 alphanumeric characters
        if (strlen($this->membershipNumber) < 6 || strlen($this->membershipNumber) > 20) {
            throw new JamboJetValidationException('membershipNumber must be 6-20 characters long');
        }

        if (!preg_match('/^[A-Z0-9]+$/', $this->membershipNumber)) {
            throw new JamboJetValidationException('membershipNumber must contain only uppercase letters and numbers');
        }
    }

    /**
     * Validate tier level
     * 
     * @throws JamboJetValidationException
     */
    private function validateTier(): void
    {
        if (!is_string($this->tier) || empty(trim($this->tier))) {
            throw new JamboJetValidationException('tier must be a non-empty string');
        }

        $validTiers = [
            'Basic',
            'Bronze',
            'Silver',
            'Gold',
            'Platinum',
            'Diamond',
            'Executive',
            'Premier',
            'Elite',
            'VIP',
            'Chairman'
        ];

        if (!in_array($this->tier, $validTiers)) {
            throw new JamboJetValidationException('tier must be one of: ' . implode(', ', $validTiers));
        }
    }

    /**
     * Validate elite status
     * 
     * @throws JamboJetValidationException
     */
    private function validateEliteStatus(): void
    {
        if (!is_string($this->eliteStatus) || empty(trim($this->eliteStatus))) {
            throw new JamboJetValidationException('eliteStatus must be a non-empty string');
        }

        $validStatuses = ['None', 'Silver', 'Gold', 'Platinum', 'Diamond', 'Chairman'];

        if (!in_array($this->eliteStatus, $validStatuses)) {
            throw new JamboJetValidationException('eliteStatus must be one of: ' . implode(', ', $validStatuses));
        }
    }

    /**
     * Validate benefits array
     * 
     * @throws JamboJetValidationException
     */
    private function validateBenefits(): void
    {
        if (!is_array($this->benefits)) {
            throw new JamboJetValidationException('benefits must be an array');
        }

        $validBenefits = [
            'PriorityBoarding',
            'ExtraBaggage',
            'SeatUpgrade',
            'LoungeAccess',
            'FastTrack',
            'PriorityCheckin',
            'BonusMiles',
            'WaivedFees',
            'CompanionTicket',
            'UpgradeVouchers'
        ];

        foreach ($this->benefits as $index => $benefit) {
            if (!is_string($benefit)) {
                throw new JamboJetValidationException("benefits[{$index}] must be a string");
            }

            if (!in_array($benefit, $validBenefits)) {
                throw new JamboJetValidationException("benefits[{$index}] must be one of: " . implode(', ', $validBenefits));
            }
        }
    }

    /**
     * Create request for frequent flyer program
     * 
     * @param string $passengerKey Passenger key
     * @param string $membershipNumber FF membership number
     * @param string|null $tier Optional tier level
     * @return self
     */
    public static function forFrequentFlyer(string $passengerKey, string $membershipNumber, ?string $tier = null): self
    {
        return new self(
            passengerKey: $passengerKey,
            programCode: 'FF',
            membershipNumber: $membershipNumber,
            tier: $tier
        );
    }

    /**
     * Create request for corporate program
     * 
     * @param string $passengerKey Passenger key
     * @param string $membershipNumber Corporate membership number
     * @param array|null $benefits Optional corporate benefits
     * @return self
     */
    public static function forCorporateProgram(string $passengerKey, string $membershipNumber, ?array $benefits = null): self
    {
        return new self(
            passengerKey: $passengerKey,
            programCode: 'CORP',
            membershipNumber: $membershipNumber,
            benefits: $benefits,
            validateMembership: false // Corporate programs often don't require real-time validation
        );
    }

    /**
     * Create request for VIP program
     * 
     * @param string $passengerKey Passenger key
     * @param string $membershipNumber VIP membership number
     * @param string $eliteStatus Elite status level
     * @return self
     */
    public static function forVipProgram(string $passengerKey, string $membershipNumber, string $eliteStatus = 'Gold'): self
    {
        return new self(
            passengerKey: $passengerKey,
            programCode: 'VIP',
            membershipNumber: $membershipNumber,
            eliteStatus: $eliteStatus,
            applyBenefits: true
        );
    }
}
