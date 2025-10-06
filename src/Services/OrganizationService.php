<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\OrganizationInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Organization Service for JamboJet NSK API
 * 
 * Handles organization management operations including creation, modification,
 * hierarchy management, user assignments, and settings management
 * Base endpoints: /api/nsk/v{version}/organizations
 * 
 * Supported endpoints:
 * - GET /api/nsk/v1/organizations - Search organizations (v1)
 * - GET /api/nsk/v2/organizations - Search organizations (v2 - recommended)
 * - POST /api/nsk/v1/organizations - Create organization
 * - GET /api/nsk/v1/organizations/{organizationCode} - Get specific organization
 * - PUT /api/nsk/v1/organizations/{organizationCode} - Update organization
 * - DELETE /api/nsk/v1/organizations/{organizationCode} - Delete organization
 * - GET /api/nsk/v1/organizations/{organizationCode}/hierarchy - Get hierarchy
 * - GET /api/nsk/v1/organizations/{organizationCode}/users - Get organization users
 * - GET /api/nsk/v1/organizations/{organizationCode}/settings - Get settings
 * - PUT /api/nsk/v1/organizations/{organizationCode}/settings - Update settings
 * 
 * @package SantosDave\JamboJet\Services
 */
class OrganizationService implements OrganizationInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // INTERFACE REQUIRED METHODS
    // =================================================================

    /**
     * Get organizations with search criteria
     * 
     * GET /api/nsk/v1/organizations or /api/nsk/v2/organizations
     * Search organizations with various filtering criteria
     * 
     * @param array $criteria Search criteria (OrganizationCode, Type, Status, etc.)
     * @param int $version API version (1 or 2, default: 2)
     * @return array Organizations search results
     * @throws JamboJetApiException
     */
    public function getOrganizations(array $criteria = [], int $version = 2): array
    {
        $this->validateSearchCriteria($criteria);
        $this->validateApiVersion($version, [1, 2]);

        try {
            return $this->get("api/nsk/v{$version}/organizations", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organizations: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create new organization
     * 
     * POST /api/nsk/v1/organizations
     * Creates a new organization with specified details
     * 
     * @param array $organizationData Organization creation data
     * @return array Organization creation response
     * @throws JamboJetApiException
     */
    public function createOrganization(array $organizationData): array
    {
        $this->validateOrganizationCreateRequest($organizationData);

        try {
            return $this->post('api/nsk/v1/organizations', $organizationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create organization: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific organization by code
     * 
     * GET /api/nsk/v1/organizations/{organizationCode}
     * Retrieves detailed information for a specific organization
     * 
     * @param string $organizationCode Organization code
     * @return array Organization details
     * @throws JamboJetApiException
     */
    public function getOrganization(string $organizationCode): array
    {
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->get("api/nsk/v1/organizations/{$organizationCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update organization information
     * 
     * PUT /api/nsk/v1/organizations/{organizationCode}
     * Updates organization details and configuration
     * 
     * @param string $organizationCode Organization code
     * @param array $updateData Organization update data
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateOrganization(string $organizationCode, array $updateData): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateOrganizationUpdateRequest($updateData);

        try {
            return $this->put("api/nsk/v1/organizations/{$organizationCode}", $updateData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update organization: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete/terminate organization
     * 
     * DELETE /api/nsk/v1/organizations/{organizationCode}
     * Soft delete by setting organization status to terminated
     * 
     * @param string $organizationCode Organization code
     * @param array $terminationData Termination details
     * @return array Deletion response
     * @throws JamboJetApiException
     */
    public function deleteOrganization(string $organizationCode, array $terminationData = []): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateTerminationRequest($terminationData);

        try {
            return $this->delete("api/nsk/v1/organizations/{$organizationCode}", $terminationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete organization: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get organization hierarchy
     * 
     * GET /api/nsk/v1/organizations/{organizationCode}/hierarchy
     * Retrieves parent/child organization relationships
     * 
     * @param string $organizationCode Organization code
     * @return array Organization hierarchy
     * @throws JamboJetApiException
     */
    public function getOrganizationHierarchy(string $organizationCode): array
    {
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->get("api/nsk/v1/organizations/{$organizationCode}/hierarchy");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization hierarchy: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get organization users
     * 
     * GET /api/nsk/v1/organizations/{organizationCode}/users
     * Retrieves users assigned to the organization
     * 
     * @param string $organizationCode Organization code
     * @param array $criteria User search criteria
     * @return array Organization users
     * @throws JamboJetApiException
     */
    public function getOrganizationUsers(string $organizationCode, array $criteria = []): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateUserSearchCriteria($criteria);

        try {
            return $this->get("api/nsk/v1/organizations/{$organizationCode}/users", $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization users: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get organization settings
     * 
     * GET /api/nsk/v1/organizations/{organizationCode}/settings
     * Retrieves organization configuration settings
     * 
     * @param string $organizationCode Organization code
     * @return array Organization settings
     * @throws JamboJetApiException
     */
    public function getOrganizationSettings(string $organizationCode): array
    {
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->get("api/nsk/v1/organizations/{$organizationCode}/settings");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update organization settings
     * 
     * PUT /api/nsk/v1/organizations/{organizationCode}/settings
     * Updates organization configuration settings
     * 
     * @param string $organizationCode Organization code
     * @param array $settings Settings to update
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateOrganizationSettings(string $organizationCode, array $settings): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateOrganizationSettingsRequest($settings);

        try {
            return $this->put("api/nsk/v1/organizations/{$organizationCode}/settings", $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update organization settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND COMPLETE
    // =================================================================

    /**
     * Validate organization search criteria
     * 
     * @param array $criteria Search criteria
     * @throws JamboJetValidationException
     */
    private function validateSearchCriteria(array $criteria): void
    {
        // Validate organization code if provided
        if (isset($criteria['OrganizationCode'])) {
            $this->validateOrganizationCode($criteria['OrganizationCode']);
        }

        // Validate organization type
        if (isset($criteria['Type'])) {
            $validTypes = ['Master', 'Carrier', 'TravelAgency', 'ThirdParty'];
            if (!in_array($criteria['Type'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid organization type. Expected one of: ' . implode(', ', $validTypes)
                );
            }
        }

        // Validate status
        if (isset($criteria['Status'])) {
            $validStatuses = ['Active', 'Cancelled', 'Pending'];
            if (!in_array($criteria['Status'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid organization status. Expected one of: ' . implode(', ', $validStatuses)
                );
            }
        }

        // Validate parent organization code
        if (isset($criteria['ParentOrganizationCode'])) {
            $this->validateOrganizationCode($criteria['ParentOrganizationCode']);
        }

        // Validate pagination parameters
        if (isset($criteria['PageSize'])) {
            $this->validateNumericRanges($criteria, ['PageSize' => ['min' => 1, 'max' => 5000]]);
        }

        if (isset($criteria['PagedItemIndex'])) {
            if (!is_numeric($criteria['PagedItemIndex']) || $criteria['PagedItemIndex'] < 1) {
                throw new JamboJetValidationException('PagedItemIndex must be a positive number');
            }
        }

        // Validate string search fields
        $stringFields = ['CompanyName', 'City', 'PostalCode'];
        foreach ($stringFields as $field) {
            if (isset($criteria[$field])) {
                $this->validateStringLengths($criteria, [$field => ['max' => 100]]);
            }
        }
    }

    /**
     * Validate organization creation request
     * 
     * @param array $data Organization creation data
     * @throws JamboJetValidationException
     */
    private function validateOrganizationCreateRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['organizationCode', 'companyName', 'type']);

        // Validate organization code
        $this->validateOrganizationCode($data['organizationCode']);

        // Validate company name
        if (empty(trim($data['companyName']))) {
            throw new JamboJetValidationException('Company name cannot be empty');
        }
        $this->validateStringLengths($data, ['companyName' => ['max' => 100]]);

        // Validate organization type
        $validTypes = ['Master', 'Carrier', 'TravelAgency', 'ThirdParty'];
        if (!in_array($data['type'], $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid organization type. Expected one of: ' . implode(', ', $validTypes)
            );
        }

        // Validate parent organization if provided
        if (isset($data['parentOrganizationCode'])) {
            $this->validateOrganizationCode($data['parentOrganizationCode']);
        }

        // Validate address if provided
        if (isset($data['address'])) {
            $this->validateAddress($data['address']);
        }

        // Validate contact information if provided
        if (isset($data['contactInfo'])) {
            $this->validateContactInfo($data['contactInfo']);
        }

        // Validate currency code if provided
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate country code if provided
        if (isset($data['countryCode'])) {
            $this->validateFormats($data, ['countryCode' => 'country_code']);
        }
    }

    /**
     * Validate organization update request
     * 
     * @param array $data Organization update data
     * @throws JamboJetValidationException
     */
    private function validateOrganizationUpdateRequest(array $data): void
    {
        // For updates, most fields are optional but must be valid if provided

        if (isset($data['companyName'])) {
            if (empty(trim($data['companyName']))) {
                throw new JamboJetValidationException('Company name cannot be empty');
            }
            $this->validateStringLengths($data, ['companyName' => ['max' => 100]]);
        }

        if (isset($data['type'])) {
            $validTypes = ['Master', 'Carrier', 'TravelAgency', 'ThirdParty'];
            if (!in_array($data['type'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid organization type. Expected one of: ' . implode(', ', $validTypes)
                );
            }
        }

        if (isset($data['status'])) {
            $validStatuses = ['Active', 'Cancelled', 'Pending'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid organization status. Expected one of: ' . implode(', ', $validStatuses)
                );
            }
        }

        if (isset($data['parentOrganizationCode'])) {
            $this->validateOrganizationCode($data['parentOrganizationCode']);
        }

        if (isset($data['address'])) {
            $this->validateAddress($data['address']);
        }

        if (isset($data['contactInfo'])) {
            $this->validateContactInfo($data['contactInfo']);
        }

        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        if (isset($data['countryCode'])) {
            $this->validateFormats($data, ['countryCode' => 'country_code']);
        }
    }

    /**
     * Validate termination request
     * 
     * @param array $data Termination data
     * @throws JamboJetValidationException
     */
    private function validateTerminationRequest(array $data): void
    {
        if (isset($data['reason'])) {
            $this->validateStringLengths($data, ['reason' => ['max' => 500]]);
        }

        if (isset($data['effectiveDate'])) {
            $this->validateFormats($data, ['effectiveDate' => 'date']);

            // Effective date should not be in the past
            $effectiveDate = new \DateTime($data['effectiveDate']);
            $now = new \DateTime();

            if ($effectiveDate < $now) {
                throw new JamboJetValidationException(
                    'Effective date cannot be in the past'
                );
            }
        }

        if (isset($data['transferUsersTo'])) {
            $this->validateOrganizationCode($data['transferUsersTo']);
        }
    }

    /**
     * Validate user search criteria
     * 
     * @param array $criteria User search criteria
     * @throws JamboJetValidationException
     */
    private function validateUserSearchCriteria(array $criteria): void
    {
        if (isset($criteria['status'])) {
            $validStatuses = ['Active', 'Pending', 'Suspended', 'Terminated'];
            if (!in_array($criteria['status'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid user status. Expected one of: ' . implode(', ', $validStatuses)
                );
            }
        }

        if (isset($criteria['roleCode'])) {
            if (empty(trim($criteria['roleCode']))) {
                throw new JamboJetValidationException('Role code cannot be empty');
            }
        }

        if (isset($criteria['pageSize'])) {
            $this->validateNumericRanges($criteria, ['pageSize' => ['min' => 1, 'max' => 1000]]);
        }
    }

    /**
     * Validate organization settings request
     * 
     * @param array $settings Settings data
     * @throws JamboJetValidationException
     */
    private function validateOrganizationSettingsRequest(array $settings): void
    {
        // Validate common setting types
        if (isset($settings['defaultCurrency'])) {
            $this->validateFormats($settings, ['defaultCurrency' => 'currency_code']);
        }

        if (isset($settings['defaultCountry'])) {
            $this->validateFormats($settings, ['defaultCountry' => 'country_code']);
        }

        if (isset($settings['timezone'])) {
            try {
                new \DateTimeZone($settings['timezone']);
            } catch (\Exception $e) {
                throw new JamboJetValidationException('Invalid timezone format');
            }
        }

        if (isset($settings['language'])) {
            if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $settings['language'])) {
                throw new JamboJetValidationException(
                    'Invalid language code format. Expected format: en or en-US'
                );
            }
        }

        // Validate boolean settings
        $booleanSettings = ['allowSubOrganizations', 'requireApproval', 'enableNotifications'];
        foreach ($booleanSettings as $setting) {
            if (isset($settings[$setting]) && !is_bool($settings[$setting])) {
                throw new JamboJetValidationException("{$setting} must be a boolean value");
            }
        }
    }

    /**
     * Validate organization code format
     * 
     * @param string $organizationCode Organization code
     * @throws JamboJetValidationException
     */
    private function validateOrganizationCode(string $organizationCode): void
    {
        if (empty(trim($organizationCode))) {
            throw new JamboJetValidationException('Organization code cannot be empty');
        }

        if (!preg_match('/^[A-Z0-9]{2,10}$/', $organizationCode)) {
            throw new JamboJetValidationException(
                'Organization code must be 2-10 characters, alphanumeric uppercase'
            );
        }
    }

    /**
     * Validate API version
     * 
     * @param int $version API version
     * @param array $allowedVersions Allowed versions
     * @throws JamboJetValidationException
     */
    private function validateApiVersion(int $version, array $allowedVersions): void
    {
        if (!in_array($version, $allowedVersions)) {
            throw new JamboJetValidationException(
                'Invalid API version. Expected one of: ' . implode(', ', $allowedVersions)
            );
        }
    }

    /**
     * Validate address information
     * 
     * @param array $address Address information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateAddress(array $address): void
    {
        // Validate country code if provided
        if (isset($address['countryCode'])) {
            $this->validateFormats($address, ['countryCode' => 'country_code']);
        }

        // Validate required address fields if any address is provided
        if (!empty($address)) {
            $this->validateRequired($address, ['lineOne', 'city', 'countryCode']);
        }

        // Validate postal code format for specific countries
        if (isset($address['postalCode']) && isset($address['countryCode'])) {
            $this->validatePostalCode($address['postalCode'], $address['countryCode']);
        }
    }

    /**
     * Validate postal code based on country
     * 
     * @param string $postalCode Postal code
     * @param string $countryCode Country code
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePostalCode(string $postalCode, string $countryCode): void
    {
        $patterns = [
            'US' => '/^\d{5}(-\d{4})?$/',
            'CA' => '/^[A-Z]\d[A-Z]\s?\d[A-Z]\d$/',
            'GB' => '/^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/i',
            'KE' => '/^\d{5}$/', // Kenya uses 5-digit postal codes
        ];

        if (isset($patterns[$countryCode])) {
            if (!preg_match($patterns[$countryCode], $postalCode)) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Invalid postal code format for country {$countryCode}",
                    400
                );
            }
        }
    }

    /**
     * Validate contact information
     * 
     * @param array $contactInfo Contact information
     * @throws JamboJetValidationException
     */
    private function validateContactInfo(array $contactInfo): void
    {
        if (isset($contactInfo['email'])) {
            $this->validateFormats($contactInfo, ['email' => 'email']);
        }

        if (isset($contactInfo['phone'])) {
            if (empty(trim($contactInfo['phone']))) {
                throw new JamboJetValidationException('Phone number cannot be empty');
            }
        }

        if (isset($contactInfo['website'])) {
            if (!filter_var($contactInfo['website'], FILTER_VALIDATE_URL)) {
                throw new JamboJetValidationException('Invalid website URL format');
            }
        }
    }
}
