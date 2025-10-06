<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Requests\BaseRequest;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Organization Create Request for JamboJet NSK API
 * 
 * Used with: POST /api/nsk/v1/organizations
 * 
 * @package SantosDave\JamboJet\Requests
 */
class OrganizationCreateRequest extends BaseRequest
{
    /**
     * Create a new organization creation request
     * 
     * @param string $organizationCode Required: Unique organization code (3-10 characters)
     * @param string $name Required: Organization name
     * @param string $type Required: Organization type (Corporate, Travel Agent, etc.)
     * @param array $contactInfo Required: Contact information (email, phone, address)
     * @param string|null $parentOrganizationCode Optional: Parent organization code
     * @param array|null $settings Optional: Organization-specific settings
     * @param array|null $creditTerms Optional: Credit terms and limits
     * @param array|null $discounts Optional: Discount configurations
     * @param array|null $restrictions Optional: Booking restrictions
     * @param bool $isActive Optional: Organization active status (default: true)
     * @param string|null $currencyCode Optional: Default currency code
     * @param string|null $timeZone Optional: Organization timezone
     * @param array|null $customFields Optional: Custom field values
     */
    public function __construct(
        public string $organizationCode,
        public string $name,
        public string $type,
        public array $contactInfo,
        public ?string $parentOrganizationCode = null,
        public ?array $settings = null,
        public ?array $creditTerms = null,
        public ?array $discounts = null,
        public ?array $restrictions = null,
        public bool $isActive = true,
        public ?string $currencyCode = null,
        public ?string $timeZone = null,
        public ?array $customFields = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'organizationCode' => $this->organizationCode,
            'name' => $this->name,
            'type' => $this->type,
            'contactInfo' => $this->contactInfo,
            'parentOrganizationCode' => $this->parentOrganizationCode,
            'settings' => $this->settings,
            'creditTerms' => $this->creditTerms,
            'discounts' => $this->discounts,
            'restrictions' => $this->restrictions,
            'isActive' => $this->isActive,
            'currencyCode' => $this->currencyCode,
            'timeZone' => $this->timeZone,
            'customFields' => $this->customFields
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
            'organizationCode' => $this->organizationCode,
            'name' => $this->name,
            'type' => $this->type,
            'contactInfo' => $this->contactInfo
        ], ['organizationCode', 'name', 'type', 'contactInfo']);

        // Validate organization code
        $this->validateOrganizationCode();

        // Validate organization name
        $this->validateOrganizationName();

        // Validate organization type
        $this->validateOrganizationType();

        // Validate contact information
        $this->validateContactInfo();

        // Validate parent organization code if provided
        if ($this->parentOrganizationCode !== null) {
            $this->validateParentOrganizationCode();
        }

        // Validate settings if provided
        if ($this->settings !== null) {
            $this->validateSettings();
        }

        // Validate credit terms if provided
        if ($this->creditTerms !== null) {
            $this->validateCreditTerms();
        }

        // Validate discounts if provided
        if ($this->discounts !== null) {
            $this->validateDiscounts();
        }

        // Validate restrictions if provided
        if ($this->restrictions !== null) {
            $this->validateRestrictions();
        }

        // Validate boolean flag
        if (!is_bool($this->isActive)) {
            throw new JamboJetValidationException('isActive must be a boolean');
        }

        // Validate currency code if provided
        if ($this->currencyCode !== null) {
            $this->validateFormats(['currencyCode' => $this->currencyCode], ['currencyCode' => 'currency']);
        }

        // Validate timezone if provided
        if ($this->timeZone !== null) {
            $this->validateTimeZone();
        }

        // Validate custom fields if provided
        if ($this->customFields !== null && !is_array($this->customFields)) {
            throw new JamboJetValidationException('customFields must be an array');
        }
    }

    /**
     * Validate organization code
     * 
     * @throws JamboJetValidationException
     */
    private function validateOrganizationCode(): void
    {
        if (!is_string($this->organizationCode) || empty(trim($this->organizationCode))) {
            throw new JamboJetValidationException('organizationCode must be a non-empty string');
        }

        if (strlen($this->organizationCode) < 3 || strlen($this->organizationCode) > 10) {
            throw new JamboJetValidationException('organizationCode must be 3-10 characters long');
        }

        if (!preg_match('/^[A-Z0-9_-]+$/', $this->organizationCode)) {
            throw new JamboJetValidationException('organizationCode must contain only uppercase letters, numbers, underscores, and hyphens');
        }
    }

    /**
     * Validate organization name
     * 
     * @throws JamboJetValidationException
     */
    private function validateOrganizationName(): void
    {
        if (!is_string($this->name) || empty(trim($this->name))) {
            throw new JamboJetValidationException('name must be a non-empty string');
        }

        if (strlen($this->name) < 2 || strlen($this->name) > 100) {
            throw new JamboJetValidationException('name must be 2-100 characters long');
        }
    }

    /**
     * Validate organization type
     * 
     * @throws JamboJetValidationException
     */
    private function validateOrganizationType(): void
    {
        if (!is_string($this->type) || empty(trim($this->type))) {
            throw new JamboJetValidationException('type must be a non-empty string');
        }

        $validTypes = [
            'Corporate',
            'TravelAgent',
            'Consolidator',
            'Airline',
            'Partner',
            'Government',
            'Educational',
            'NonProfit',
            'Supplier',
            'Distributor',
            'Other'
        ];

        if (!in_array($this->type, $validTypes)) {
            throw new JamboJetValidationException('type must be one of: ' . implode(', ', $validTypes));
        }
    }

    /**
     * Validate contact information
     * 
     * @throws JamboJetValidationException
     */
    private function validateContactInfo(): void
    {
        if (!is_array($this->contactInfo)) {
            throw new JamboJetValidationException('contactInfo must be an array');
        }

        // Validate required contact fields
        $this->validateRequired($this->contactInfo, ['email', 'phone']);

        // Validate email format
        $this->validateFormats($this->contactInfo, ['email' => 'email']);

        // Validate phone format
        $this->validateFormats($this->contactInfo, ['phone' => 'phone']);

        // Validate address if provided
        if (isset($this->contactInfo['address'])) {
            if (!is_array($this->contactInfo['address'])) {
                throw new JamboJetValidationException('contactInfo.address must be an array');
            }

            $this->validateRequired($this->contactInfo['address'], ['street', 'city', 'countryCode']);
            $this->validateFormats($this->contactInfo['address'], ['countryCode' => 'country']);

            // Validate postal code if provided
            if (isset($this->contactInfo['address']['postalCode'])) {
                if (!is_string($this->contactInfo['address']['postalCode']) || empty(trim($this->contactInfo['address']['postalCode']))) {
                    throw new JamboJetValidationException('contactInfo.address.postalCode must be a non-empty string');
                }
            }
        }

        // Validate contact person if provided
        if (isset($this->contactInfo['contactPerson'])) {
            if (!is_array($this->contactInfo['contactPerson'])) {
                throw new JamboJetValidationException('contactInfo.contactPerson must be an array');
            }

            $this->validateRequired($this->contactInfo['contactPerson'], ['firstName', 'lastName']);
        }
    }

    /**
     * Validate parent organization code
     * 
     * @throws JamboJetValidationException
     */
    private function validateParentOrganizationCode(): void
    {
        if (!is_string($this->parentOrganizationCode) || empty(trim($this->parentOrganizationCode))) {
            throw new JamboJetValidationException('parentOrganizationCode must be a non-empty string');
        }

        if (strlen($this->parentOrganizationCode) < 3 || strlen($this->parentOrganizationCode) > 10) {
            throw new JamboJetValidationException('parentOrganizationCode must be 3-10 characters long');
        }

        if (!preg_match('/^[A-Z0-9_-]+$/', $this->parentOrganizationCode)) {
            throw new JamboJetValidationException('parentOrganizationCode must contain only uppercase letters, numbers, underscores, and hyphens');
        }

        // Cannot be parent of itself
        if ($this->parentOrganizationCode === $this->organizationCode) {
            throw new JamboJetValidationException('parentOrganizationCode cannot be the same as organizationCode');
        }
    }

    /**
     * Validate settings
     * 
     * @throws JamboJetValidationException
     */
    private function validateSettings(): void
    {
        if (!is_array($this->settings)) {
            throw new JamboJetValidationException('settings must be an array');
        }

        // Validate booking settings if provided
        if (isset($this->settings['booking'])) {
            if (!is_array($this->settings['booking'])) {
                throw new JamboJetValidationException('settings.booking must be an array');
            }

            // Validate auto-approval settings
            if (isset($this->settings['booking']['autoApprove']) && !is_bool($this->settings['booking']['autoApprove'])) {
                throw new JamboJetValidationException('settings.booking.autoApprove must be a boolean');
            }

            // Validate booking window
            if (isset($this->settings['booking']['advanceBookingDays']) && (!is_int($this->settings['booking']['advanceBookingDays']) || $this->settings['booking']['advanceBookingDays'] < 0)) {
                throw new JamboJetValidationException('settings.booking.advanceBookingDays must be a non-negative integer');
            }
        }
    }

    /**
     * Validate credit terms
     * 
     * @throws JamboJetValidationException
     */
    private function validateCreditTerms(): void
    {
        if (!is_array($this->creditTerms)) {
            throw new JamboJetValidationException('creditTerms must be an array');
        }

        // Validate credit limit
        if (isset($this->creditTerms['creditLimit']) && (!is_numeric($this->creditTerms['creditLimit']) || $this->creditTerms['creditLimit'] < 0)) {
            throw new JamboJetValidationException('creditTerms.creditLimit must be a non-negative number');
        }

        // Validate payment terms
        if (isset($this->creditTerms['paymentTermsDays']) && (!is_int($this->creditTerms['paymentTermsDays']) || $this->creditTerms['paymentTermsDays'] < 0)) {
            throw new JamboJetValidationException('creditTerms.paymentTermsDays must be a non-negative integer');
        }

        // Validate credit approval required
        if (isset($this->creditTerms['approvalRequired']) && !is_bool($this->creditTerms['approvalRequired'])) {
            throw new JamboJetValidationException('creditTerms.approvalRequired must be a boolean');
        }
    }

    /**
     * Validate discounts
     * 
     * @throws JamboJetValidationException
     */
    private function validateDiscounts(): void
    {
        if (!is_array($this->discounts)) {
            throw new JamboJetValidationException('discounts must be an array');
        }

        foreach ($this->discounts as $index => $discount) {
            if (!is_array($discount)) {
                throw new JamboJetValidationException("discounts[{$index}] must be an array");
            }

            // Validate required discount fields
            $this->validateRequired($discount, ['type', 'value']);

            // Validate discount type
            $validDiscountTypes = ['Percentage', 'FixedAmount', 'FareClass'];
            if (!in_array($discount['type'], $validDiscountTypes)) {
                throw new JamboJetValidationException("discounts[{$index}].type must be one of: " . implode(', ', $validDiscountTypes));
            }

            // Validate discount value
            if (!is_numeric($discount['value']) || $discount['value'] < 0) {
                throw new JamboJetValidationException("discounts[{$index}].value must be a non-negative number");
            }

            // Additional validation for percentage discounts
            if ($discount['type'] === 'Percentage' && $discount['value'] > 100) {
                throw new JamboJetValidationException("discounts[{$index}].value cannot exceed 100 for percentage discounts");
            }
        }
    }

    /**
     * Validate restrictions
     * 
     * @throws JamboJetValidationException
     */
    private function validateRestrictions(): void
    {
        if (!is_array($this->restrictions)) {
            throw new JamboJetValidationException('restrictions must be an array');
        }

        // Validate route restrictions
        if (isset($this->restrictions['allowedRoutes'])) {
            if (!is_array($this->restrictions['allowedRoutes'])) {
                throw new JamboJetValidationException('restrictions.allowedRoutes must be an array');
            }

            foreach ($this->restrictions['allowedRoutes'] as $index => $route) {
                if (!is_string($route) || !preg_match('/^[A-Z]{3}-[A-Z]{3}$/', $route)) {
                    throw new JamboJetValidationException("restrictions.allowedRoutes[{$index}] must be in format 'XXX-XXX' (airport codes)");
                }
            }
        }

        // Validate booking class restrictions
        if (isset($this->restrictions['allowedClasses'])) {
            if (!is_array($this->restrictions['allowedClasses'])) {
                throw new JamboJetValidationException('restrictions.allowedClasses must be an array');
            }

            $validClasses = ['Economy', 'Business', 'First'];
            foreach ($this->restrictions['allowedClasses'] as $index => $class) {
                if (!in_array($class, $validClasses)) {
                    throw new JamboJetValidationException("restrictions.allowedClasses[{$index}] must be one of: " . implode(', ', $validClasses));
                }
            }
        }
    }

    /**
     * Validate timezone
     * 
     * @throws JamboJetValidationException
     */
    private function validateTimeZone(): void
    {
        if (!is_string($this->timeZone) || empty(trim($this->timeZone))) {
            throw new JamboJetValidationException('timeZone must be a non-empty string');
        }

        // Validate timezone format (e.g., 'Africa/Nairobi', 'UTC', 'GMT+3')
        if (!preg_match('/^[A-Za-z]+\/[A-Za-z_]+$|^(UTC|GMT)([+-]\d{1,2})?$/', $this->timeZone)) {
            throw new JamboJetValidationException('timeZone must be in valid timezone format (e.g., Africa/Nairobi, UTC, GMT+3)');
        }
    }

    /**
     * Create request for corporate organization
     * 
     * @param string $organizationCode Organization code
     * @param string $name Organization name
     * @param array $contactInfo Contact information
     * @param array|null $creditTerms Optional credit terms
     * @return self
     */
    public static function forCorporate(string $organizationCode, string $name, array $contactInfo, ?array $creditTerms = null): self
    {
        return new self(
            organizationCode: $organizationCode,
            name: $name,
            type: 'Corporate',
            contactInfo: $contactInfo,
            creditTerms: $creditTerms
        );
    }

    /**
     * Create request for travel agent organization
     * 
     * @param string $organizationCode Organization code
     * @param string $name Organization name
     * @param array $contactInfo Contact information
     * @param array|null $discounts Optional discount configuration
     * @return self
     */
    public static function forTravelAgent(string $organizationCode, string $name, array $contactInfo, ?array $discounts = null): self
    {
        return new self(
            organizationCode: $organizationCode,
            name: $name,
            type: 'TravelAgent',
            contactInfo: $contactInfo,
            discounts: $discounts
        );
    }
}
