<?php

namespace SantosDave\JamboJet\Contracts;

interface OrganizationInterface
{
    /**
     * Get organizations with search criteria
     * GET /api/nsk/v1/organizations
     * GET /api/nsk/v2/organizations
     */
    public function getOrganizations(array $criteria = [], int $version = 2): array;

    /**
     * Create new organization
     * POST /api/nsk/v1/organizations
     */
    public function createOrganization(array $organizationData): array;

    /**
     * Get specific organization by code
     * GET /api/nsk/v1/organizations/{organizationCode}
     */
    public function getOrganization(string $organizationCode): array;

    /**
     * Update organization information
     * PUT /api/nsk/v1/organizations/{organizationCode}
     */
    public function updateOrganization(string $organizationCode, array $updateData): array;

    /**
     * Delete/terminate organization
     * DELETE /api/nsk/v1/organizations/{organizationCode}
     */
    public function deleteOrganization(string $organizationCode, array $terminationData = []): array;

    /**
     * Get organization hierarchy
     * GET /api/nsk/v1/organizations/{organizationCode}/hierarchy
     */
    public function getOrganizationHierarchy(string $organizationCode): array;

    /**
     * Get organization users
     * GET /api/nsk/v1/organizations/{organizationCode}/users
     */
    public function getOrganizationUsers(string $organizationCode, array $criteria = []): array;

    /**
     * Get organization settings
     * GET /api/nsk/v1/organizations/{organizationCode}/settings
     */
    public function getOrganizationSettings(string $organizationCode): array;

    /**
     * Update organization settings
     * PUT /api/nsk/v1/organizations/{organizationCode}/settings
     */
    public function updateOrganizationSettings(string $organizationCode, array $settings): array;

    // =================================================================
// 1️⃣ ORGANIZATION GROUPS (7 endpoints)
// =================================================================

    /**
     * Create organization group and add organization
     * POST /api/nsk/v2/organizationGroup
     * 
     * @param array $groupData Organization group data
     *              - organizationGroupName: string (required)
     *              - organization: array (required)
     *                  - organizationCode: string (1-10 chars)
     *                  - name: string (optional)
     * @return array Created organization group
     */
    public function createOrganizationGroup(array $groupData): array;

    /**
     * Modify organization group
     * PUT /api/nsk/v1/organizationGroups/{organizationGroupCode}
     * 
     * @param string $organizationGroupCode Organization group code
     * @param array $groupData Update data
     * @return array Updated organization group
     */
    public function updateOrganizationGroup(string $organizationGroupCode, array $groupData): array;

    /**
     * Delete organization group
     * DELETE /api/nsk/v1/organizationGroups/{organizationGroupCode}
     * 
     * @param string $organizationGroupCode Organization group code
     * @return array Deletion confirmation
     */
    public function deleteOrganizationGroup(string $organizationGroupCode): array;

    /**
     * Remove all organizations from group
     * PUT /api/nsk/v1/organizationGroups/{organizationGroupCode}/organizations
     * 
     * @param string $organizationGroupCode Organization group code
     * @return array Update confirmation
     */
    public function removeAllOrganizationsFromGroup(string $organizationGroupCode): array;

    /**
     * Remove specific organization from group
     * PUT /api/nsk/v1/organizationGroups/{organizationGroupCode}/organizations/{organizationCode}
     * 
     * @param string $organizationGroupCode Organization group code
     * @param string $organizationCode Organization code to remove
     * @return array Update confirmation
     */
    public function removeOrganizationFromGroup(string $organizationGroupCode, string $organizationCode): array;

    /**
     * Get all organization groups
     * GET /api/nsk/v1/organizations/groups
     * 
     * @param array $criteria Optional search criteria
     * @return array List of organization groups
     */
    public function getOrganizationGroups(array $criteria = []): array;

    /**
     * Get organization group details
     * GET /api/nsk/v1/organizations/groups/{organizationGroupCode}
     * 
     * @param string $organizationGroupCode Organization group code
     * @return array Organization group details
     */
    public function getOrganizationGroupDetails(string $organizationGroupCode): array;

// =================================================================
// 2️⃣ ORGANIZATIONS2 CRUD (5 endpoints)
// =================================================================

    /**
     * Create new organization (v2)
     * POST /api/nsk/v1/organizations2
     * 
     * @param array $organizationData Organization creation data
     * @return array Created organization
     */
    public function createOrganization2(array $organizationData): array;

    /**
     * Search organizations with pagination (v2)
     * GET /api/nsk/v2/organizations2
     * 
     * @param array $criteria Search criteria with pagination
     * @return array Paginated organization results
     */
    public function searchOrganizations2(array $criteria = []): array;

    /**
     * Get organization details (v2)
     * GET /api/nsk/v1/organizations2/{organizationCode}
     * 
     * @param string $organizationCode Organization code
     * @return array Organization details
     */
    public function getOrganization2(string $organizationCode): array;

    /**
     * Update organization (v2)
     * PUT /api/nsk/v1/organizations2/{organizationCode}
     * 
     * @param string $organizationCode Organization code
     * @param array $updateData Update data
     * @return array Updated organization
     */
    public function updateOrganization2(string $organizationCode, array $updateData): array;

    /**
     * Partial update organization (v2)
     * PATCH /api/nsk/v1/organizations2/{organizationCode}
     * 
     * @param string $organizationCode Organization code
     * @param array $patchData Partial update data
     * @return array Updated organization
     */
    public function patchOrganization2(string $organizationCode, array $patchData): array;

// =================================================================
// 3️⃣ ORGANIZATION ACCOUNTS (5 endpoints)
// =================================================================

    /**
     * Get organization account
     * GET /api/nsk/v1/organizations2/{organizationCode}/account
     * 
     * @param string $organizationCode Organization code
     * @return array Organization account details
     */
    public function getOrganizationAccount(string $organizationCode): array;

    /**
     * Create organization account
     * POST /api/nsk/v1/organizations2/{organizationCode}/account
     * 
     * @param string $organizationCode Organization code
     * @param array $accountData Account creation data
     * @return array Created account
     */
    public function createOrganizationAccount(string $organizationCode, array $accountData): array;

    /**
     * Adjust child account available amount
     * POST /api/nsk/v1/organizations2/{organizationCode}/account/childAccountTransactions
     * 
     * @param string $organizationCode Organization code
     * @param array $transactionData Transaction data
     * @return array Transaction result
     */
    public function adjustChildAccountAmount(string $organizationCode, array $transactionData): array;

    /**
     * Adjust child account credit limit
     * PUT /api/nsk/v1/organizations2/{organizationCode}/account/childAccountTransactions
     * 
     * @param string $organizationCode Organization code
     * @param array $transactionData Transaction data
     * @return array Transaction result
     */
    public function adjustChildAccountCreditLimit(string $organizationCode, array $transactionData): array;

    /**
     * Update account status
     * PUT /api/nsk/v1/organizations2/{organizationCode}/account/status
     * 
     * @param string $organizationCode Organization code
     * @param int $status Account status (0=Open, 1=Closed, 2=AgencyInactive, 3=Unknown)
     * @return array Updated account status
     */
    public function updateAccountStatus(string $organizationCode, int $status): array;

// =================================================================
// 4️⃣ ORGANIZATION TRANSACTIONS (2 endpoints)
// =================================================================

    /**
     * Create organization transaction
     * POST /api/nsk/v1/organizations2/{organizationCode}/account/transactions
     * 
     * @param string $organizationCode Organization code
     * @param array $transactionData Transaction creation data
     * @return array Created transaction
     */
    public function createOrganizationTransaction(string $organizationCode, array $transactionData): array;

    /**
     * Get organization transactions
     * GET /api/nsk/v1/organizations2/{organizationCode}/account/transactions
     * 
     * @param string $organizationCode Organization code
     * @param array $criteria Search criteria (dates, pagination)
     * @return array Transaction list
     */
    public function getOrganizationTransactions(string $organizationCode, array $criteria = []): array;

    // =================================================================
// 5️⃣ ALLOTMENT CONTRACTS (3 endpoints)
// =================================================================

    /**
     * Get all allotment contracts for organization
     * GET /api/nsk/v1/organizations2/{organizationCode}/allotments/contracts
     * 
     * @param string $organizationCode Organization code
     * @param array $criteria Optional filtering criteria
     * @return array List of allotment contracts
     */
    public function getAllotmentContracts(string $organizationCode, array $criteria = []): array;

    /**
     * Create allotment contract
     * POST /api/nsk/v1/organizations2/{organizationCode}/allotments/contracts
     * 
     * @param string $organizationCode Organization code
     * @param array $contractData Contract creation data
     * @return array Created contract
     */
    public function createAllotmentContract(string $organizationCode, array $contractData): array;

    /**
     * Update allotment contract
     * PUT /api/nsk/v1/organizations2/{organizationCode}/allotments/contracts/{contractCode}
     * 
     * @param string $organizationCode Organization code
     * @param string $contractCode Contract code
     * @param array $contractData Contract update data
     * @return array Updated contract
     */
    public function updateAllotmentContract(
        string $organizationCode,
        string $contractCode,
        array $contractData
    ): array;

// =================================================================
// 6️⃣ COMMISSION RATES (3 endpoints)
// =================================================================

    /**
     * Create commission rate
     * POST /api/nsk/v1/organizations2/{organizationCode}/commissionRates
     * 
     * @param string $organizationCode Organization code
     * @param array $rateData Commission rate data
     * @return array Created commission rate
     */
    public function createCommissionRate(string $organizationCode, array $rateData): array;

    /**
     * Get commission rate
     * GET /api/nsk/v1/organizations2/{organizationCode}/commissionRates/{commissionRateCode}
     * 
     * @param string $organizationCode Organization code
     * @param string $commissionRateCode Commission rate code
     * @return array Commission rate details
     */
    public function getCommissionRate(string $organizationCode, string $commissionRateCode): array;

    /**
     * Delete commission rate
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/commissionRates/{commissionRateCode}
     * 
     * @param string $organizationCode Organization code
     * @param string $commissionRateCode Commission rate code
     * @return array Deletion confirmation
     */
    public function deleteCommissionRate(string $organizationCode, string $commissionRateCode): array;

// =================================================================
// 9️⃣ EXTERNAL ACCOUNTS (3 endpoints)
// =================================================================

    /**
     * Create external account
     * POST /api/nsk/v1/organizations2/{organizationCode}/externalAccounts
     * 
     * @param string $organizationCode Organization code
     * @param array $accountData External account data
     * @return array Created external account
     */
    public function createExternalAccount(string $organizationCode, array $accountData): array;

    /**
     * Get external account
     * GET /api/nsk/v1/organizations2/{organizationCode}/externalAccounts/{externalAccountKey}
     * 
     * @param string $organizationCode Organization code
     * @param string $externalAccountKey External account key
     * @return array External account details
     */
    public function getExternalAccount(string $organizationCode, string $externalAccountKey): array;

    /**
     * Delete external account
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/externalAccounts/{externalAccountKey}
     * 
     * @param string $organizationCode Organization code
     * @param string $externalAccountKey External account key
     * @return array Deletion confirmation
     */
    public function deleteExternalAccount(string $organizationCode, string $externalAccountKey): array;

// =================================================================
// 🔟 PROCESS SCHEDULES (5 endpoints)
// =================================================================

    /**
     * Get all process schedules for organization
     * GET /api/nsk/v1/organizations2/{organizationCode}/processSchedules
     * 
     * @param string $organizationCode Organization code
     * @return array List of process schedules
     */
    public function getProcessSchedules(string $organizationCode): array;

    /**
     * Add process schedule to organization
     * POST /api/nsk/v1/organizations2/{organizationCode}/processSchedules/{processScheduleId}
     * 
     * @param string $organizationCode Organization code
     * @param int $processScheduleId Process schedule ID
     * @return array Added process schedule
     */
    public function addProcessSchedule(string $organizationCode, int $processScheduleId): array;

    /**
     * Delete process schedule from organization
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/processSchedules/{processScheduleId}
     * 
     * @param string $organizationCode Organization code
     * @param int $processScheduleId Process schedule ID
     * @return array Deletion confirmation
     */
    public function deleteProcessSchedule(string $organizationCode, int $processScheduleId): array;

    /**
     * Get specific process schedule (v2)
     * GET /api/nsk/v2/organizations2/processSchedule/{processScheduleId}
     * 
     * @param int $processScheduleId Process schedule ID
     * @return array Process schedule details
     */
    public function getProcessScheduleDetails(int $processScheduleId): array;

    /**
     * Get all active process schedules (v2)
     * GET /api/nsk/v2/organizations2/processSchedules
     * 
     * @param array $criteria Optional filtering criteria
     * @return array List of active process schedules
     */
    public function getAllProcessSchedules(array $criteria = []): array;

// =================================================================
// 1️⃣1️⃣ ALLOTMENTS (6 endpoints)
// =================================================================

    /**
     * Get all allotments for leg
     * GET /api/nsk/v1/organizations2/allotments
     * 
     * @param array $criteria Required: legKey, Optional: allotmentBasisCode
     * @return array List of allotments
     */
    public function getAllotments(array $criteria): array;

    /**
     * Get allotment by basis code
     * GET /api/nsk/v1/organizations2/allotments/{allotmentBasisCode}
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @param array $criteria Required: legKey
     * @return array Allotment details
     */
    public function getAllotmentByBasisCode(string $allotmentBasisCode, array $criteria): array;

    /**
     * Get allotments by basis code
     * GET /api/nsk/v1/organizations2/allotments/byAllotmentBasisCode/{allotmentBasisCode}
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @return array Allotments matching basis code
     */
    public function getAllotmentsByBasisCode(string $allotmentBasisCode): array;

    /**
     * Get allotment contract
     * GET /api/nsk/v1/organizations2/allotments/contracts/{contractCode}
     * 
     * @param string $contractCode Contract code
     * @return array Allotment contract details
     */
    public function getAllotmentContractDetails(string $contractCode): array;

    /**
     * Create allotment for leg
     * POST /api/nsk/v1/organizations2/contracts/basis/{allotmentBasisCode}/allotments/legs/{legKey}
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @param string $legKey Leg key
     * @param array $allotmentData Allotment creation data
     * @return array Created allotment
     */
    public function createAllotmentForLeg(
        string $allotmentBasisCode,
        string $legKey,
        array $allotmentData
    ): array;

    /**
     * Update allotment for leg
     * PUT /api/nsk/v1/organizations2/contracts/basis/{allotmentBasisCode}/allotments/legs/{legKey}
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @param string $legKey Leg key
     * @param array $allotmentData Allotment update data
     * @return array Updated allotment
     */
    public function updateAllotmentForLeg(
        string $allotmentBasisCode,
        string $legKey,
        array $allotmentData
    ): array;

// =================================================================
// 1️⃣2️⃣ ALLOTMENT BASES (4 endpoints)
// =================================================================

    /**
     * Get all allotment bases for contract
     * GET /api/nsk/v1/organizations2/allotments/{contractCode}/bases
     * 
     * @param string $contractCode Contract code
     * @param array $criteria Optional: PageSize (10-5000), LastPageKey
     * @return array List of allotment bases
     */
    public function getAllotmentBases(string $contractCode, array $criteria = []): array;

    /**
     * Create allotment basis
     * POST /api/nsk/v1/organizations2/allotments/{contractCode}/bases
     * 
     * @param string $contractCode Contract code
     * @param array $basisData Allotment basis data
     * @return array Created allotment basis
     */
    public function createAllotmentBasis(string $contractCode, array $basisData): array;

    /**
     * Get specific allotment basis
     * GET /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @return array Allotment basis details
     */
    public function getAllotmentBasisDetails(string $allotmentBasisCode): array;

    /**
     * Update allotment basis
     * PUT /api/nsk/v1/organizations2/allotments/{contractCode}/bases/{allotmentBasisCode}
     * 
     * @param string $contractCode Contract code
     * @param string $allotmentBasisCode Allotment basis code
     * @param array $basisData Allotment basis update data
     * @return array Updated allotment basis
     */
    public function updateAllotmentBasis(
        string $contractCode,
        string $allotmentBasisCode,
        array $basisData
    ): array;

// =================================================================
// 1️⃣3️⃣ ALLOTMENT MARKET FARES (5 endpoints)
// =================================================================

    /**
     * Get all market fares for allotment basis
     * GET /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @return array List of market fares
     */
    public function getMarketFares(string $allotmentBasisCode): array;

    /**
     * Create market fare
     * POST /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @param array $fareData Market fare data
     * @return array Created market fare
     */
    public function createMarketFare(string $allotmentBasisCode, array $fareData): array;

    /**
     * Get specific market fare
     * GET /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares/{allotmentMarketFareKey}
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @param string $allotmentMarketFareKey Market fare key
     * @return array Market fare details
     */
    public function getMarketFareDetails(
        string $allotmentBasisCode,
        string $allotmentMarketFareKey
    ): array;

    /**
     * Update market fare
     * PUT /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares/{allotmentMarketFareKey}
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @param string $allotmentMarketFareKey Market fare key
     * @param array $fareData Market fare update data
     * @return array Updated market fare
     */
    public function updateMarketFare(
        string $allotmentBasisCode,
        string $allotmentMarketFareKey,
        array $fareData
    ): array;

    /**
     * Delete market fare
     * DELETE /api/nsk/v1/organizations2/allotments/bases/{allotmentBasisCode}/marketFares/{allotmentMarketFareKey}
     * 
     * @param string $allotmentBasisCode Allotment basis code
     * @param string $allotmentMarketFareKey Market fare key
     * @return array Deletion confirmation
     */
    public function deleteMarketFare(
        string $allotmentBasisCode,
        string $allotmentMarketFareKey
    ): array;

// =================================================================
// 1️⃣4️⃣ ORGANIZATION REGISTRATION (1 endpoint)
// =================================================================

    /**
     * Request to register new organization
     * POST /api/nsk/v1/organizations2/register
     * 
     * Note: This is only a REQUEST - requires agent approval
     * 
     * @param array $registrationData Organization registration data
     * @return array Registration request confirmation
     */
    public function registerOrganization(array $registrationData): array;

    // =================================================================
// 7️⃣ COMPANY PHONE NUMBERS (3 endpoints)
// =================================================================

    /**
     * Create company phone number
     * POST /api/nsk/v1/organizations2/{organizationCode}/company/phoneNumbers
     * 
     * @param string $organizationCode Organization code
     * @param array $phoneData Phone number data
     *              - type: int (0=Other, 1=Home, 2=Work, 3=Mobile, 4=Fax)
     *              - number: string (required)
     * @return array Created phone number
     */
    public function createCompanyPhoneNumber(string $organizationCode, array $phoneData): array;

    /**
     * Update company phone number
     * PUT /api/nsk/v1/organizations2/{organizationCode}/company/phoneNumbers/{phoneNumberType}
     * 
     * @param string $organizationCode Organization code
     * @param int $phoneNumberType Phone number type (0-4)
     * @param array $phoneData Phone number update data
     *              - number: string (required)
     * @return array Updated phone number
     */
    public function updateCompanyPhoneNumber(
        string $organizationCode,
        int $phoneNumberType,
        array $phoneData
    ): array;

    /**
     * Delete company phone number
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/company/phoneNumbers/{phoneNumberType}
     * 
     * @param string $organizationCode Organization code
     * @param int $phoneNumberType Phone number type (0-4)
     * @return array Deletion confirmation
     */
    public function deleteCompanyPhoneNumber(
        string $organizationCode,
        int $phoneNumberType
    ): array;

// =================================================================
// 8️⃣ CONTACT PHONE NUMBERS (3 endpoints)
// =================================================================

    /**
     * Create contact phone number
     * POST /api/nsk/v1/organizations2/{organizationCode}/contact/phoneNumbers
     * 
     * @param string $organizationCode Organization code
     * @param array $phoneData Phone number data
     *              - type: int (0=Other, 1=Home, 2=Work, 3=Mobile, 4=Fax)
     *              - number: string (required)
     * @return array Created phone number
     */
    public function createContactPhoneNumber(string $organizationCode, array $phoneData): array;

    /**
     * Update contact phone number
     * PUT /api/nsk/v1/organizations2/{organizationCode}/contact/phoneNumbers/{phoneNumberType}
     * 
     * @param string $organizationCode Organization code
     * @param int $phoneNumberType Phone number type (0-4)
     * @param array $phoneData Phone number update data
     *              - number: string (required)
     * @return array Updated phone number
     */
    public function updateContactPhoneNumber(
        string $organizationCode,
        int $phoneNumberType,
        array $phoneData
    ): array;

    /**
     * Delete contact phone number
     * DELETE /api/nsk/v1/organizations2/{organizationCode}/contact/phoneNumbers/{phoneNumberType}
     * 
     * @param string $organizationCode Organization code
     * @param int $phoneNumberType Phone number type (0-4)
     * @return array Deletion confirmation
     */
    public function deleteContactPhoneNumber(
        string $organizationCode,
        int $phoneNumberType
    ): array;
}
