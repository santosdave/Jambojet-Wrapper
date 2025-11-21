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
// 1️⃣ ORGANIZATION GROUPS IMPLEMENTATION (7 endpoints)
// =================================================================

    /**
     * Create organization group and add organization
     * POST /api/nsk/v2/organizationGroup
     */
    public function createOrganizationGroup(array $groupData): array
    {
        $this->validateOrganizationGroupRequest($groupData);

        try {
            return $this->post('api/nsk/v2/organizationGroup', $groupData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create organization group: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Modify organization group
     * PUT /api/nsk/v1/organizationGroups/{organizationGroupCode}
     */
    public function updateOrganizationGroup(string $organizationGroupCode, array $groupData): array
    {
        $this->validateOrganizationGroupCode($organizationGroupCode);
        $this->validateOrganizationGroupRequest($groupData);

        try {
            return $this->put(
                "api/nsk/v1/organizationGroups/{$organizationGroupCode}",
                $groupData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update organization group: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete organization group
     * DELETE /api/nsk/v1/organizationGroups/{organizationGroupCode}
     */
    public function deleteOrganizationGroup(string $organizationGroupCode): array
    {
        $this->validateOrganizationGroupCode($organizationGroupCode);

        try {
            return $this->delete("api/nsk/v1/organizationGroups/{$organizationGroupCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete organization group: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove all organizations from group
     * PUT /api/nsk/v1/organizationGroups/{organizationGroupCode}/organizations
     */
    public function removeAllOrganizationsFromGroup(string $organizationGroupCode): array
    {
        $this->validateOrganizationGroupCode($organizationGroupCode);

        try {
            return $this->put(
                "api/nsk/v1/organizationGroups/{$organizationGroupCode}/organizations",
                []
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove all organizations from group: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove specific organization from group
     * PUT /api/nsk/v1/organizationGroups/{organizationGroupCode}/organizations/{organizationCode}
     */
    public function removeOrganizationFromGroup(
        string $organizationGroupCode,
        string $organizationCode
    ): array {
        $this->validateOrganizationGroupCode($organizationGroupCode);
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->put(
                "api/nsk/v1/organizationGroups/{$organizationGroupCode}/organizations/{$organizationCode}",
                []
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove organization from group: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all organization groups
     * GET /api/nsk/v1/organizations/groups
     */
    public function getOrganizationGroups(array $criteria = []): array
    {
        try {
            return $this->get('api/nsk/v1/organizations/groups', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization groups: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get organization group details
     * GET /api/nsk/v1/organizations/groups/{organizationGroupCode}
     */
    public function getOrganizationGroupDetails(string $organizationGroupCode): array
    {
        $this->validateOrganizationGroupCode($organizationGroupCode);

        try {
            return $this->get("api/nsk/v1/organizations/groups/{$organizationGroupCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization group details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 2️⃣ ORGANIZATIONS2 CRUD IMPLEMENTATION (5 endpoints)
// =================================================================

    /**
     * Create new organization (v2)
     * POST /api/nsk/v1/organizations2
     */
    public function createOrganization2(array $organizationData): array
    {
        $this->validateOrganizationCreateData($organizationData);

        try {
            return $this->post('api/nsk/v1/organizations2', $organizationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create organization (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Search organizations with pagination (v2)
     * GET /api/nsk/v2/organizations2
     */
    public function searchOrganizations2(array $criteria = []): array
    {
        $this->validateSearchCriteria2($criteria);

        try {
            return $this->get('api/nsk/v2/organizations2', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search organizations (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get organization details (v2)
     * GET /api/nsk/v1/organizations2/{organizationCode}
     */
    public function getOrganization2(string $organizationCode): array
    {
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->get("api/nsk/v1/organizations2/{$organizationCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update organization (v2)
     * PUT /api/nsk/v1/organizations2/{organizationCode}
     */
    public function updateOrganization2(string $organizationCode, array $updateData): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateOrganizationUpdateData($updateData);

        try {
            return $this->put("api/nsk/v1/organizations2/{$organizationCode}", $updateData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update organization (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Partial update organization (v2)
     * PATCH /api/nsk/v1/organizations2/{organizationCode}
     */
    public function patchOrganization2(string $organizationCode, array $patchData): array
    {
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->patch("api/nsk/v1/organizations2/{$organizationCode}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch organization (v2): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 3️⃣ ORGANIZATION ACCOUNTS IMPLEMENTATION (5 endpoints)
// =================================================================

    /**
     * Get organization account
     * GET /api/nsk/v1/organizations2/{organizationCode}/account
     */
    public function getOrganizationAccount(string $organizationCode): array
    {
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->get("api/nsk/v1/organizations2/{$organizationCode}/account");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create organization account
     * POST /api/nsk/v1/organizations2/{organizationCode}/account
     */
    public function createOrganizationAccount(string $organizationCode, array $accountData): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateAccountData($accountData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/account",
                $accountData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create organization account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Adjust child account available amount
     * POST /api/nsk/v1/organizations2/{organizationCode}/account/childAccountTransactions
     */
    public function adjustChildAccountAmount(
        string $organizationCode,
        array $transactionData
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateChildAccountTransaction($transactionData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/account/childAccountTransactions",
                $transactionData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to adjust child account amount: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Adjust child account credit limit
     * PUT /api/nsk/v1/organizations2/{organizationCode}/account/childAccountTransactions
     */
    public function adjustChildAccountCreditLimit(
        string $organizationCode,
        array $transactionData
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateChildAccountTransaction($transactionData);

        try {
            return $this->put(
                "api/nsk/v1/organizations2/{$organizationCode}/account/childAccountTransactions",
                $transactionData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to adjust child account credit limit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update account status
     * PUT /api/nsk/v1/organizations2/{organizationCode}/account/status
     */
    public function updateAccountStatus(string $organizationCode, int $status): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateAccountStatus($status);

        try {
            return $this->put(
                "api/nsk/v1/organizations2/{$organizationCode}/account/status",
                ['status' => $status]
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update account status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 4️⃣ ORGANIZATION TRANSACTIONS IMPLEMENTATION (2 endpoints)
// =================================================================

    /**
     * Create organization transaction
     * POST /api/nsk/v1/organizations2/{organizationCode}/account/transactions
     */
    public function createOrganizationTransaction(
        string $organizationCode,
        array $transactionData
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateTransactionData($transactionData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/account/transactions",
                $transactionData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create organization transaction: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get organization transactions
     * GET /api/nsk/v1/organizations2/{organizationCode}/account/transactions
     */
    public function getOrganizationTransactions(
        string $organizationCode,
        array $criteria = []
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateTransactionCriteria($criteria);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/{$organizationCode}/account/transactions",
                $criteria
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
// 5️⃣ ALLOTMENT CONTRACTS IMPLEMENTATION (3 endpoints)
// =================================================================

    /**
     * Get all allotment contracts for organization
     * GET /api/nsk/v1/organizations2/{organizationCode}/allotments/contracts
     */
    public function getAllotmentContracts(string $organizationCode, array $criteria = []): array
    {
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/{$organizationCode}/allotments/contracts",
                $criteria
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get allotment contracts: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create allotment contract
     * POST /api/nsk/v1/organizations2/{organizationCode}/allotments/contracts
     */
    public function createAllotmentContract(string $organizationCode, array $contractData): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateAllotmentContractData($contractData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/allotments/contracts",
                $contractData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create allotment contract: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update allotment contract
     * PUT /api/nsk/v1/organizations2/{organizationCode}/allotments/contracts/{contractCode}
     */
    public function updateAllotmentContract(
        string $organizationCode,
        string $contractCode,
        array $contractData
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateContractCode($contractCode);
        $this->validateAllotmentContractData($contractData);

        try {
            return $this->put(
                "api/nsk/v1/organizations2/{$organizationCode}/allotments/contracts/{$contractCode}",
                $contractData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update allotment contract: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 6️⃣ COMMISSION RATES IMPLEMENTATION (3 endpoints)
// =================================================================

    /**
     * Create commission rate
     * POST /api/nsk/v1/organizations2/{organizationCode}/commissionRates
     */
    public function createCommissionRate(string $organizationCode, array $rateData): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateCommissionRateData($rateData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/commissionRates",
                $rateData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create commission rate: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get commission rate
     * GET /api/nsk/v1/organizations2/{organizationCode}/commissionRates/{commissionRateCode}
     */
    public function getCommissionRate(string $organizationCode, string $commissionRateCode): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateCommissionRateCode($commissionRateCode);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/{$organizationCode}/commissionRates/{$commissionRateCode}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get commission rate: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete commission rate
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/commissionRates/{commissionRateCode}
     */
    public function deleteCommissionRate(
        string $organizationCode,
        string $commissionRateCode
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateCommissionRateCode($commissionRateCode);

        try {
            return $this->delete(
                "api/nsk/v1/organizations2/{$organizationCode}/commissionRates/{$commissionRateCode}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete commission rate: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 9️⃣ EXTERNAL ACCOUNTS IMPLEMENTATION (3 endpoints)
// =================================================================

    /**
     * Create external account
     * POST /api/nsk/v1/organizations2/{organizationCode}/externalAccounts
     */
    public function createExternalAccount(string $organizationCode, array $accountData): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateExternalAccountData($accountData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/externalAccounts",
                $accountData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create external account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get external account
     * GET /api/nsk/v1/organizations2/{organizationCode}/externalAccounts/{externalAccountKey}
     */
    public function getExternalAccount(
        string $organizationCode,
        string $externalAccountKey
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateExternalAccountKey($externalAccountKey);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/{$organizationCode}/externalAccounts/{$externalAccountKey}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get external account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete external account
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/externalAccounts/{externalAccountKey}
     */
    public function deleteExternalAccount(
        string $organizationCode,
        string $externalAccountKey
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateExternalAccountKey($externalAccountKey);

        try {
            return $this->delete(
                "api/nsk/v1/organizations2/{$organizationCode}/externalAccounts/{$externalAccountKey}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete external account: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 🔟 PROCESS SCHEDULES IMPLEMENTATION (5 endpoints)
// =================================================================

    /**
     * Get all process schedules for organization
     * GET /api/nsk/v1/organizations2/{organizationCode}/processSchedules
     */
    public function getProcessSchedules(string $organizationCode): array
    {
        $this->validateOrganizationCode($organizationCode);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/{$organizationCode}/processSchedules"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get process schedules: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add process schedule to organization
     * POST /api/nsk/v1/organizations2/{organizationCode}/processSchedules/{processScheduleId}
     */
    public function addProcessSchedule(string $organizationCode, int $processScheduleId): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validateProcessScheduleId($processScheduleId);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/processSchedules/{$processScheduleId}",
                []
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add process schedule: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete process schedule from organization
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/processSchedules/{processScheduleId}
     */
    public function deleteProcessSchedule(
        string $organizationCode,
        int $processScheduleId
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validateProcessScheduleId($processScheduleId);

        try {
            return $this->delete(
                "api/nsk/v1/organizations2/{$organizationCode}/processSchedules/{$processScheduleId}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete process schedule: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific process schedule (v2)
     * GET /api/nsk/v2/organizations2/processSchedule/{processScheduleId}
     */
    public function getProcessScheduleDetails(int $processScheduleId): array
    {
        $this->validateProcessScheduleId($processScheduleId);

        try {
            return $this->get(
                "api/nsk/v2/organizations2/processSchedule/{$processScheduleId}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get process schedule details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all active process schedules (v2)
     * GET /api/nsk/v2/organizations2/processSchedules
     */
    public function getAllProcessSchedules(array $criteria = []): array
    {
        try {
            return $this->get('api/nsk/v2/organizations2/processSchedules', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get all process schedules: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
// 1️⃣1️⃣ ALLOTMENTS IMPLEMENTATION (6 endpoints)
// =================================================================

    /**
     * Get all allotments for leg
     * GET /api/nsk/v1/organizations2/allotments
     */
    public function getAllotments(array $criteria): array
    {
        $this->validateAllotmentsCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/organizations2/allotments', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get allotments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get allotment by basis code
     * GET /api/nsk/v1/organizations2/allotments/{allotmentBasisCode}
     */
    public function getAllotmentByBasisCode(string $allotmentBasisCode, array $criteria): array
    {
        $this->validateAllotmentBasisCode($allotmentBasisCode);
        $this->validateAllotmentsCriteria($criteria);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/allotments/{$allotmentBasisCode}",
                $criteria
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get allotment by basis code: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get allotments by basis code
     * GET /api/nsk/v1/organizations2/allotments/byAllotmentBasisCode/{allotmentBasisCode}
     */
    public function getAllotmentsByBasisCode(string $allotmentBasisCode): array
    {
        $this->validateAllotmentBasisCode($allotmentBasisCode);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/allotments/byAllotmentBasisCode/{$allotmentBasisCode}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get allotments by basis code: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get allotment contract
     * GET /api/nsk/v1/organizations2/allotments/contracts/{contractCode}
     */
    public function getAllotmentContractDetails(string $contractCode): array
    {
        $this->validateContractCode($contractCode);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/allotments/contracts/{$contractCode}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get allotment contract details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create allotment for leg
     * POST /api/nsk/v1/organizations2/contracts/basis/{allotmentBasisCode}/allotments/legs/{legKey}
     */
    public function createAllotmentForLeg(
        string $allotmentBasisCode,
        string $legKey,
        array $allotmentData
    ): array {
        $this->validateAllotmentBasisCode($allotmentBasisCode);
        $this->validateLegKey($legKey);
        $this->validateAllotmentData($allotmentData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/contracts/basis/{$allotmentBasisCode}/allotments/legs/{$legKey}",
                $allotmentData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create allotment for leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update allotment for leg
     * PUT /api/nsk/v1/organizations2/contracts/basis/{allotmentBasisCode}/allotments/legs/{legKey}
     */
    public function updateAllotmentForLeg(
        string $allotmentBasisCode,
        string $legKey,
        array $allotmentData
    ): array {
        $this->validateAllotmentBasisCode($allotmentBasisCode);
        $this->validateLegKey($legKey);
        $this->validateAllotmentData($allotmentData);

        try {
            return $this->put(
                "api/nsk/v1/organizations2/contracts/basis/{$allotmentBasisCode}/allotments/legs/{$legKey}",
                $allotmentData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update allotment for leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 1️⃣2️⃣ ALLOTMENT BASES IMPLEMENTATION (4 endpoints)
// =================================================================

    /**
     * Get all allotment bases for contract
     * GET /api/nsk/v1/organizations2/allotments/{contractCode}/bases
     */
    public function getAllotmentBases(string $contractCode, array $criteria = []): array
    {
        $this->validateContractCode($contractCode);
        $this->validateAllotmentBasesCriteria($criteria);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/allotments/{$contractCode}/bases",
                $criteria
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get allotment bases: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create allotment basis
     * POST /api/nsk/v1/organizations2/allotments/{contractCode}/bases
     */
    public function createAllotmentBasis(string $contractCode, array $basisData): array
    {
        $this->validateContractCode($contractCode);
        $this->validateAllotmentBasisData($basisData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/allotments/{$contractCode}/bases",
                $basisData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create allotment basis: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific allotment basis
     * GET /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}
     */
    public function getAllotmentBasisDetails(string $allotmentBasisCode): array
    {
        $this->validateAllotmentBasisCode($allotmentBasisCode);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/allotments/bases/{$allotmentBasisCode}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get allotment basis details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update allotment basis
     * PUT /api/nsk/v1/organizations2/allotments/{contractCode}/bases/{allotmentBasisCode}
     */
    public function updateAllotmentBasis(
        string $contractCode,
        string $allotmentBasisCode,
        array $basisData
    ): array {
        $this->validateContractCode($contractCode);
        $this->validateAllotmentBasisCode($allotmentBasisCode);
        $this->validateAllotmentBasisData($basisData);

        try {
            return $this->put(
                "api/nsk/v1/organizations2/allotments/{$contractCode}/bases/{$allotmentBasisCode}",
                $basisData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update allotment basis: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 1️⃣3️⃣ ALLOTMENT MARKET FARES IMPLEMENTATION (5 endpoints)
// =================================================================

    /**
     * Get all market fares for allotment basis
     * GET /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares
     */
    public function getMarketFares(string $allotmentBasisCode): array
    {
        $this->validateAllotmentBasisCode($allotmentBasisCode);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/allotments/bases/{$allotmentBasisCode}/marketFares"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get market fares: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create market fare
     * POST /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares
     */
    public function createMarketFare(string $allotmentBasisCode, array $fareData): array
    {
        $this->validateAllotmentBasisCode($allotmentBasisCode);
        $this->validateMarketFareData($fareData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/allotments/bases/{$allotmentBasisCode}/marketFares",
                $fareData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create market fare: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific market fare
     * GET /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares/{allotmentMarketFareKey}
     */
    public function getMarketFareDetails(
        string $allotmentBasisCode,
        string $allotmentMarketFareKey
    ): array {
        $this->validateAllotmentBasisCode($allotmentBasisCode);
        $this->validateMarketFareKey($allotmentMarketFareKey);

        try {
            return $this->get(
                "api/nsk/v1/organizations2/allotments/bases/{$allotmentBasisCode}/marketFares/{$allotmentMarketFareKey}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get market fare details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update market fare
     * PUT /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares/{allotmentMarketFareKey}
     */
    public function updateMarketFare(
        string $allotmentBasisCode,
        string $allotmentMarketFareKey,
        array $fareData
    ): array {
        $this->validateAllotmentBasisCode($allotmentBasisCode);
        $this->validateMarketFareKey($allotmentMarketFareKey);
        $this->validateMarketFareData($fareData);

        try {
            return $this->put(
                "api/nsk/v1/organizations2/allotments/bases/{$allotmentBasisCode}/marketFares/{$allotmentMarketFareKey}",
                $fareData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update market fare: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete market fare
     * DELETE /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares/{allotmentMarketFareKey}
     */
    public function deleteMarketFare(
        string $allotmentBasisCode,
        string $allotmentMarketFareKey
    ): array {
        $this->validateAllotmentBasisCode($allotmentBasisCode);
        $this->validateMarketFareKey($allotmentMarketFareKey);

        try {
            return $this->delete(
                "api/nsk/v1/organizations2/allotments/bases/{$allotmentBasisCode}/marketFares/{$allotmentMarketFareKey}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete market fare: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 1️⃣4️⃣ ORGANIZATION REGISTRATION IMPLEMENTATION (1 endpoint)
// =================================================================

    /**
     * Request to register new organization
     * POST /api/nsk/v1/organizations2/register
     * 
     * Note: This is only a REQUEST - requires agent approval
     */
    public function registerOrganization(array $registrationData): array
    {
        $this->validateOrganizationCreateData($registrationData);

        try {
            return $this->post('api/nsk/v1/organizations2/register', $registrationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to register organization: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
// 7️⃣ COMPANY PHONE NUMBERS IMPLEMENTATION (3 endpoints)
// =================================================================

    /**
     * Create company phone number
     * POST /api/nsk/v1/organizations2/{organizationCode}/company/phoneNumbers
     */
    public function createCompanyPhoneNumber(string $organizationCode, array $phoneData): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validatePhoneNumberData($phoneData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/company/phoneNumbers",
                $phoneData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create company phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update company phone number
     * PUT /api/nsk/v1/organizations2/{organizationCode}/company/phoneNumbers/{phoneNumberType}
     */
    public function updateCompanyPhoneNumber(
        string $organizationCode,
        int $phoneNumberType,
        array $phoneData
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validatePhoneNumberType($phoneNumberType);
        $this->validatePhoneNumberData($phoneData, false); // Skip type validation for update

        try {
            return $this->put(
                "api/nsk/v1/organizations2/{$organizationCode}/company/phoneNumbers/{$phoneNumberType}",
                $phoneData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update company phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete company phone number
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/company/phoneNumbers/{phoneNumberType}
     */
    public function deleteCompanyPhoneNumber(
        string $organizationCode,
        int $phoneNumberType
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validatePhoneNumberType($phoneNumberType);

        try {
            return $this->delete(
                "api/nsk/v1/organizations2/{$organizationCode}/company/phoneNumbers/{$phoneNumberType}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete company phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// 8️⃣ CONTACT PHONE NUMBERS IMPLEMENTATION (3 endpoints)
// =================================================================

    /**
     * Create contact phone number
     * POST /api/nsk/v1/organizations2/{organizationCode}/contact/phoneNumbers
     */
    public function createContactPhoneNumber(string $organizationCode, array $phoneData): array
    {
        $this->validateOrganizationCode($organizationCode);
        $this->validatePhoneNumberData($phoneData);

        try {
            return $this->post(
                "api/nsk/v1/organizations2/{$organizationCode}/contact/phoneNumbers",
                $phoneData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create contact phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update contact phone number
     * PUT /api/nsk/v1/organizations2/{organizationCode}/contact/phoneNumbers/{phoneNumberType}
     */
    public function updateContactPhoneNumber(
        string $organizationCode,
        int $phoneNumberType,
        array $phoneData
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validatePhoneNumberType($phoneNumberType);
        $this->validatePhoneNumberData($phoneData, false); // Skip type validation for update

        try {
            return $this->put(
                "api/nsk/v1/organizations2/{$organizationCode}/contact/phoneNumbers/{$phoneNumberType}",
                $phoneData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update contact phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete contact phone number
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/contact/phoneNumbers/{phoneNumberType}
     */
    public function deleteContactPhoneNumber(
        string $organizationCode,
        int $phoneNumberType
    ): array {
        $this->validateOrganizationCode($organizationCode);
        $this->validatePhoneNumberType($phoneNumberType);

        try {
            return $this->delete(
                "api/nsk/v1/organizations2/{$organizationCode}/contact/phoneNumbers/{$phoneNumberType}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete contact phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND COMPLETE
    // =================================================================

    /**
     * Validate phone number type
     * 
     * Phone Number Types:
     * - 0 = Other
     * - 1 = Home
     * - 2 = Work
     * - 3 = Mobile
     * - 4 = Fax
     */
    private function validatePhoneNumberType(int $type): void
    {
        $validTypes = [0, 1, 2, 3, 4];

        if (!in_array($type, $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid phone number type. Must be: 0 (Other), 1 (Home), 2 (Work), 3 (Mobile), or 4 (Fax)'
            );
        }
    }

    /**
     * Validate phone number data
     * 
     * @param array $data Phone number data
     * @param bool $requireType Whether to require type field (true for create, false for update)
     */
    private function validatePhoneNumberData(array $data, bool $requireType = true): void
    {
        // Phone number is required
        if (!isset($data['number']) || empty(trim($data['number']))) {
            throw new JamboJetValidationException('Phone number is required');
        }

        $number = trim($data['number']);

        // Basic phone number format validation
        // Allow digits, spaces, parentheses, hyphens, plus signs
        if (!preg_match('/^[\d\s\(\)\-\+]+$/', $number)) {
            throw new JamboJetValidationException(
                'Phone number contains invalid characters. Only digits, spaces, parentheses, hyphens, and plus signs are allowed'
            );
        }

        // Check minimum length (at least 5 digits)
        $digitsOnly = preg_replace('/\D/', '', $number);
        if (strlen($digitsOnly) < 5) {
            throw new JamboJetValidationException(
                'Phone number must contain at least 5 digits'
            );
        }

        // Check maximum length (15 digits per E.164 standard)
        if (strlen($digitsOnly) > 15) {
            throw new JamboJetValidationException(
                'Phone number cannot exceed 15 digits'
            );
        }

        // Validate type if required (for create operations)
        if ($requireType) {
            if (!isset($data['type'])) {
                throw new JamboJetValidationException(
                    'Phone number type is required'
                );
            }

            $this->validatePhoneNumberType($data['type']);
        }

        // If type is provided in update, validate it
        if (isset($data['type'])) {
            $this->validatePhoneNumberType($data['type']);
        }
    }

    /**
     * Format phone number for display (helper method)
     * 
     * @param string $number Raw phone number
     * @return string Formatted phone number
     */
    private function formatPhoneNumber(string $number): string
    {
        // Remove all non-digit characters
        $digitsOnly = preg_replace('/\D/', '', $number);

        // Format based on length
        $length = strlen($digitsOnly);

        if ($length === 10) {
            // US/CA format: (XXX) XXX-XXXX
            return sprintf(
                '(%s) %s-%s',
                substr($digitsOnly, 0, 3),
                substr($digitsOnly, 3, 3),
                substr($digitsOnly, 6, 4)
            );
        } elseif ($length === 11 && $digitsOnly[0] === '1') {
            // US/CA with country code: +1 (XXX) XXX-XXXX
            return sprintf(
                '+1 (%s) %s-%s',
                substr($digitsOnly, 1, 3),
                substr($digitsOnly, 4, 3),
                substr($digitsOnly, 7, 4)
            );
        } elseif ($length > 11) {
            // International format: +XX XXX XXX XXXX
            return '+' . $digitsOnly;
        }

        // Default: return with minimal formatting
        return $number;
    }

    /**
     * Get phone number type name (helper method)
     * 
     * @param int $type Phone number type code
     * @return string Phone number type name
     */
    private function getPhoneNumberTypeName(int $type): string
    {
        $typeNames = [
            0 => 'Other',
            1 => 'Home',
            2 => 'Work',
            3 => 'Mobile',
            4 => 'Fax'
        ];

        return $typeNames[$type] ?? 'Unknown';
    }

    /**
     * Validate allotments criteria
     */
    private function validateAllotmentsCriteria(array $criteria): void
    {
        // legKey is required
        if (!isset($criteria['legKey']) || empty(trim($criteria['legKey']))) {
            throw new JamboJetValidationException('legKey is required for allotment queries');
        }

        $this->validateLegKey($criteria['legKey']);
    }

    /**
     * Validate allotment basis code
     */
    private function validateAllotmentBasisCode(string $code): void
    {
        if (empty(trim($code))) {
            throw new JamboJetValidationException('Allotment basis code is required');
        }

        if (strlen($code) > 50) {
            throw new JamboJetValidationException(
                'Allotment basis code must not exceed 50 characters'
            );
        }
    }

    /**
     * Validate leg key
     */
    private function validateLegKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Leg key is required');
        }
    }

    /**
     * Validate allotment data
     */
    private function validateAllotmentData(array $data): void
    {
        // Only NegoAllotment and ProrataAllotment supported
        if (isset($data['type'])) {
            $validTypes = ['NegoAllotment', 'ProrataAllotment'];
            if (!in_array($data['type'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Only NegoAllotment and ProrataAllotment types are supported'
                );
            }
        }

        // Inventory cache refresh required after updates
        if (isset($data['quantity']) && !is_int($data['quantity'])) {
            throw new JamboJetValidationException('Allotment quantity must be an integer');
        }
    }

    /**
     * Validate allotment bases criteria
     */
    private function validateAllotmentBasesCriteria(array $criteria): void
    {
        // Validate pagination
        if (isset($criteria['PageSize'])) {
            $pageSize = $criteria['PageSize'];
            if (!is_int($pageSize) || $pageSize < 10 || $pageSize > 5000) {
                throw new JamboJetValidationException(
                    'PageSize must be between 10 and 5000'
                );
            }
        }

        if (isset($criteria['LastPageKey']) && !is_string($criteria['LastPageKey'])) {
            throw new JamboJetValidationException('LastPageKey must be a string');
        }
    }

    /**
     * Validate allotment basis data
     */
    private function validateAllotmentBasisData(array $data): void
    {
        if (!isset($data['allotmentBasisCode']) || empty(trim($data['allotmentBasisCode']))) {
            throw new JamboJetValidationException('Allotment basis code is required');
        }

        $this->validateAllotmentBasisCode($data['allotmentBasisCode']);
    }

    /**
     * Validate market fare data
     */
    private function validateMarketFareData(array $data): void
    {
        // Validate station codes
        if (isset($data['originStationCode']) && isset($data['destinationStationCode'])) {
            $origin = $data['originStationCode'];
            $destination = $data['destinationStationCode'];

            // Station codes cannot be the same
            if ($origin === $destination) {
                throw new JamboJetValidationException(
                    'Origin and destination station codes cannot be the same'
                );
            }

            // Station codes must be in alphabetical order
            if (strcmp($origin, $destination) > 0) {
                throw new JamboJetValidationException(
                    'Station codes must be in alphabetical order (origin < destination)'
                );
            }
        }

        // Validate dates
        if (isset($data['releaseDate'])) {
            $releaseDate = \DateTime::createFromFormat('Y-m-d', $data['releaseDate']);
            if (!$releaseDate) {
                throw new JamboJetValidationException('Release date must be in Y-m-d format');
            }

            // Release date cannot be in the past
            $now = new \DateTime();
            if ($releaseDate < $now) {
                throw new JamboJetValidationException('Release date cannot be in the past');
            }
        }

        if (isset($data['discontinueDate'])) {
            $discontinueDate = \DateTime::createFromFormat('Y-m-d', $data['discontinueDate']);
            if (!$discontinueDate) {
                throw new JamboJetValidationException(
                    'Discontinue date must be in Y-m-d format'
                );
            }

            // Discontinue date cannot be in the past
            $now = new \DateTime();
            if ($discontinueDate < $now) {
                throw new JamboJetValidationException(
                    'Discontinue date cannot be in the past'
                );
            }

            // Discontinue date must be after release date
            if (isset($data['releaseDate'])) {
                $releaseDate = \DateTime::createFromFormat('Y-m-d', $data['releaseDate']);
                if ($discontinueDate <= $releaseDate) {
                    throw new JamboJetValidationException(
                        'Discontinue date must be after release date'
                    );
                }
            }
        }

        // Validate price
        if (isset($data['price'])) {
            if (!is_numeric($data['price']) || $data['price'] <= 0) {
                throw new JamboJetValidationException('Price must be a positive number');
            }
        }

        // Directionality: 3 = Both directions (default)
        if (isset($data['directionality'])) {
            $validDirections = [1, 2, 3]; // One-way, Return, Both
            if (!in_array($data['directionality'], $validDirections)) {
                throw new JamboJetValidationException(
                    'Invalid directionality value'
                );
            }
        }
    }

    /**
     * Validate market fare key
     */
    private function validateMarketFareKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Market fare key is required');
        }
    }

    /**
     * Validate allotment contract data
     */
    private function validateAllotmentContractData(array $data): void
    {
        // Contract status cannot be 0 (Default), must be Active
        if (isset($data['status']) && $data['status'] === 0) {
            throw new JamboJetValidationException(
                'Contract status cannot be Default (0), must be Active'
            );
        }

        if (!isset($data['contractCode']) || empty(trim($data['contractCode']))) {
            throw new JamboJetValidationException('Contract code is required');
        }
    }

    /**
     * Validate contract code
     */
    private function validateContractCode(string $code): void
    {
        if (empty(trim($code))) {
            throw new JamboJetValidationException('Contract code is required');
        }

        if (strlen($code) > 50) {
            throw new JamboJetValidationException(
                'Contract code must not exceed 50 characters'
            );
        }
    }

    /**
     * Validate commission rate data
     */
    private function validateCommissionRateData(array $data): void
    {
        if (!isset($data['commissionRateCode']) || empty(trim($data['commissionRateCode']))) {
            throw new JamboJetValidationException('Commission rate code is required');
        }

        if (isset($data['rate'])) {
            if (!is_numeric($data['rate'])) {
                throw new JamboJetValidationException('Commission rate must be numeric');
            }

            if ($data['rate'] < 0 || $data['rate'] > 100) {
                throw new JamboJetValidationException(
                    'Commission rate must be between 0 and 100'
                );
            }
        }
    }

    /**
     * Validate commission rate code
     */
    private function validateCommissionRateCode(string $code): void
    {
        if (empty(trim($code))) {
            throw new JamboJetValidationException('Commission rate code is required');
        }
    }

    /**
     * Validate external account data
     */
    private function validateExternalAccountData(array $data): void
    {
        if (!isset($data['name']) || empty(trim($data['name']))) {
            throw new JamboJetValidationException('External account name is required');
        }

        if (isset($data['accountNumber']) && empty(trim($data['accountNumber']))) {
            throw new JamboJetValidationException(
                'External account number cannot be empty if provided'
            );
        }
    }

    /**
     * Validate external account key
     */
    private function validateExternalAccountKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('External account key is required');
        }
    }

    /**
     * Validate process schedule ID
     */
    private function validateProcessScheduleId(int $id): void
    {
        if ($id <= 0) {
            throw new JamboJetValidationException(
                'Process schedule ID must be a positive integer'
            );
        }
    }

    /**
     * Validate organization group code
     */
    private function validateOrganizationGroupCode(string $code): void
    {
        if (empty(trim($code))) {
            throw new JamboJetValidationException('Organization group code is required');
        }

        if (strlen($code) > 10) {
            throw new JamboJetValidationException(
                'Organization group code must not exceed 10 characters'
            );
        }
    }

    /**
     * Validate organization group request
     */
    private function validateOrganizationGroupRequest(array $data): void
    {
        if (isset($data['organization'])) {
            if (!isset($data['organization']['organizationCode'])) {
                throw new JamboJetValidationException(
                    'Organization code is required in organization data'
                );
            }

            $code = $data['organization']['organizationCode'];
            if (strlen($code) < 1 || strlen($code) > 10) {
                throw new JamboJetValidationException(
                    'Organization code must be 1-10 characters'
                );
            }
        }
    }

    /**
     * Validate search criteria for v2
     */
    private function validateSearchCriteria2(array $criteria): void
    {
        // Validate pagination parameters
        if (isset($criteria['PageSize'])) {
            $pageSize = $criteria['PageSize'];
            if (!is_int($pageSize) || $pageSize < 1 || $pageSize > 100) {
                throw new JamboJetValidationException(
                    'PageSize must be between 1 and 100'
                );
            }
        }

        if (isset($criteria['PagedItemIndex'])) {
            $index = $criteria['PagedItemIndex'];
            if (!is_int($index) || $index < 0) {
                throw new JamboJetValidationException(
                    'PagedItemIndex must be 0 or greater'
                );
            }
        }

        // Validate match criteria
        if (isset($criteria['MatchCriteria'])) {
            $validCriteria = [0, 1, 2, 3]; // StartsWith, EndsWith, Contains, ExactMatch
            if (!in_array($criteria['MatchCriteria'], $validCriteria)) {
                throw new JamboJetValidationException(
                    'MatchCriteria must be 0 (StartsWith), 1 (EndsWith), 2 (Contains), or 3 (ExactMatch)'
                );
            }
        }

        // Validate organization type
        if (isset($criteria['OrganizationType'])) {
            $validTypes = [0, 1, 2, 3, 4]; // Default, Master, Carrier, TravelAgency, ThirdParty
            if (!in_array($criteria['OrganizationType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid organization type'
                );
            }
        }

        // Validate organization status
        if (isset($criteria['OrganizationStatus'])) {
            $validStatuses = [0, 1, 2, 3]; // Default, Active, Cancelled, Pending
            if (!in_array($criteria['OrganizationStatus'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid organization status'
                );
            }
        }
    }

    /**
     * Validate account data
     */
    private function validateAccountData(array $data): void
    {
        // Account validation will depend on account type
        // Basic validation for required fields
        if (isset($data['accountType'])) {
            $validTypes = ['Credit', 'Prepaid', 'Supplemental'];
            if (!in_array($data['accountType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid account type. Must be Credit, Prepaid, or Supplemental'
                );
            }
        }
    }

    /**
     * Validate account status
     */
    private function validateAccountStatus(int $status): void
    {
        $validStatuses = [0, 1, 2, 3]; // Open, Closed, AgencyInactive, Unknown
        if (!in_array($status, $validStatuses)) {
            throw new JamboJetValidationException(
                'Invalid account status. Must be 0 (Open), 1 (Closed), 2 (AgencyInactive), or 3 (Unknown)'
            );
        }
    }

    /**
     * Validate child account transaction
     */
    private function validateChildAccountTransaction(array $data): void
    {
        if (!isset($data['amount']) || !is_numeric($data['amount'])) {
            throw new JamboJetValidationException(
                'Transaction amount is required and must be numeric'
            );
        }
    }

    /**
     * Validate transaction data
     */
    private function validateTransactionData(array $data): void
    {
        if (!isset($data['amount']) || !is_numeric($data['amount'])) {
            throw new JamboJetValidationException(
                'Transaction amount is required and must be numeric'
            );
        }

        if (isset($data['currencyCode']) && strlen($data['currencyCode']) !== 3) {
            throw new JamboJetValidationException(
                'Currency code must be 3 characters (ISO 4217)'
            );
        }
    }

    /**
     * Validate transaction criteria
     */
    private function validateTransactionCriteria(array $criteria): void
    {
        // Date range is mandatory
        if (!isset($criteria['beginDate']) || !isset($criteria['endDate'])) {
            throw new JamboJetValidationException(
                'Both beginDate and endDate are required for transaction queries'
            );
        }

        // Validate date format
        foreach (['beginDate', 'endDate'] as $field) {
            if (isset($criteria[$field])) {
                $date = \DateTime::createFromFormat('Y-m-d', $criteria[$field]);
                if (!$date) {
                    throw new JamboJetValidationException(
                        "{$field} must be in Y-m-d format"
                    );
                }
            }
        }

        // Validate pagination
        if (isset($criteria['PageSize'])) {
            $pageSize = $criteria['PageSize'];
            if (!is_int($pageSize) || $pageSize < 1 || $pageSize > 100) {
                throw new JamboJetValidationException(
                    'PageSize must be between 1 and 100'
                );
            }
        }
    }

    /**
     * Validate organization create data
     */
    private function validateOrganizationCreateData(array $data): void
    {
        if (!isset($data['organizationCode']) || empty(trim($data['organizationCode']))) {
            throw new JamboJetValidationException('Organization code is required');
        }

        $code = $data['organizationCode'];
        $type = $data['type'] ?? null;

        // Validate code based on organization type
        if ($type !== null && $type !== 3) { // Not Pending status
            switch ($type) {
                case 4: // ThirdParty: 3-10 chars alphanumeric
                    if (strlen($code) < 3 || strlen($code) > 10) {
                        throw new JamboJetValidationException(
                            'Third Party organization code must be 3-10 characters'
                        );
                    }
                    if (!preg_match('/^[A-Za-z0-9]+$/', $code)) {
                        throw new JamboJetValidationException(
                            'Third Party organization code must be alphanumeric'
                        );
                    }
                    break;

                case 3: // TravelAgency: 7-10 chars numeric
                    if (strlen($code) < 7 || strlen($code) > 10) {
                        throw new JamboJetValidationException(
                            'Travel Agency organization code must be 7-10 characters'
                        );
                    }
                    if (!preg_match('/^[0-9]+$/', $code)) {
                        throw new JamboJetValidationException(
                            'Travel Agency organization code must be numeric'
                        );
                    }
                    break;

                case 2: // Carrier: 2-3 chars alphanumeric
                    if (strlen($code) < 2 || strlen($code) > 3) {
                        throw new JamboJetValidationException(
                            'Carrier organization code must be 2-3 characters'
                        );
                    }
                    if (!preg_match('/^[A-Za-z0-9]+$/', $code)) {
                        throw new JamboJetValidationException(
                            'Carrier organization code must be alphanumeric'
                        );
                    }
                    break;
            }
        }
    }

    /**
     * Validate organization update data
     */
    private function validateOrganizationUpdateData(array $data): void
    {
        // Similar to create validation but allow optional fields
        if (isset($data['type'])) {
            $validTypes = [0, 1, 2, 3, 4];
            if (!in_array($data['type'], $validTypes)) {
                throw new JamboJetValidationException('Invalid organization type');
            }
        }

        if (isset($data['status'])) {
            $validStatuses = [0, 1, 2, 3];
            if (!in_array($data['status'], $validStatuses)) {
                throw new JamboJetValidationException('Invalid organization status');
            }
        }
    }

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
