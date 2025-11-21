<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\VoucherInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Requests\VoucherIssuanceRequest;
use SantosDave\JamboJet\Requests\VoucherSearchRequest;
use SantosDave\JamboJet\Requests\VoucherUpdateRequest;
use SantosDave\JamboJet\Requests\VoucherOwnerUpdateRequest;

/**
 * Voucher Service for JamboJet NSK API
 * 
 * Handles all voucher operations including issuance, search, management, and configuration
 * Base endpoints: /api/nsk/v1/vouchers, /api/nsk/v2/vouchers, /api/nsk/v2/voucherIssuance
 * 
 * All endpoints require agent permissions
 * 
 * Supported endpoints:
 * - POST /api/nsk/v2/voucherIssuance - Create vouchers
 * - GET /api/nsk/v1/voucherIssuance/{key} - Get issuance
 * - GET /api/nsk/v2/vouchers - Search vouchers
 * - GET /api/nsk/v1/vouchers/{key} - Get voucher
 * - GET /api/nsk/v2/vouchers/byAgent - Search by agent
 * - GET /api/nsk/v1/vouchers/byDate - Search by date
 * - GET /api/nsk/v2/vouchers/byIssuance/{key} - Search by issuance
 * - GET /api/nsk/v2/vouchers/byMarket - Search by market
 * - GET /api/nsk/v1/vouchers/byReference/{ref} - Get by reference
 * - PATCH /api/nsk/v1/vouchers/{key} - Update voucher
 * - PATCH /api/nsk/v1/vouchers/{key}/owner - Update owner
 * - GET /api/nsk/v1/vouchers/configuration - Get configurations
 * - GET /api/nsk/v1/vouchers/configuration/{code} - Get configuration
 * - POST /api/nsk/v2/vouchers/configuration - Create configuration
 * - PUT /api/nsk/v2/vouchers/configuration - Update configuration
 * - DELETE /api/nsk/v1/vouchers/configuration - Delete configuration
 * 
 * @package SantosDave\JamboJet\Services
 */
class VoucherService implements VoucherInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // VOUCHER ISSUANCE METHODS
    // =================================================================

    /**
     * Create vouchers based on request
     * 
     * POST /api/nsk/v2/voucherIssuance
     * Note: High volume creates batch job which may not process immediately
     * 
     * @param VoucherIssuanceRequest|array $request Voucher issuance data or request object
     * @return array Created voucher issuance response
     * @throws JamboJetApiException
     */
    public function createVoucher(VoucherIssuanceRequest|array $request): array
    {
        if (is_array($request)) {
            $request = VoucherIssuanceRequest::fromArray($request);
        }

        $request->validate();

        try {
            return $this->post('api/nsk/v2/voucherIssuance', $request->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create voucher: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get voucher issuance by key
     * 
     * GET /api/nsk/v1/voucherIssuance/{voucherIssuanceKey}
     * 
     * @param string $voucherIssuanceKey Voucher issuance key
     * @return array Voucher issuance details
     * @throws JamboJetApiException
     */
    public function getVoucherIssuance(string $voucherIssuanceKey): array
    {
        $this->validateKey($voucherIssuanceKey, 'Voucher issuance key');

        try {
            return $this->get("api/nsk/v1/voucherIssuance/{$voucherIssuanceKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get voucher issuance: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VOUCHER SEARCH & RETRIEVAL METHODS
    // =================================================================

    /**
     * Search vouchers based on criteria
     * 
     * GET /api/nsk/v2/vouchers
     * Only returns vouchers from agent's non-restricted configurations
     * 
     * @param VoucherSearchRequest|array $request Search criteria or request object
     * @return array Paged vouchers response
     * @throws JamboJetApiException
     */
    public function searchVouchers(VoucherSearchRequest|array $request = []): array
    {
        if (is_array($request)) {
            $request = VoucherSearchRequest::fromArray($request);
        }

        $request->validate();

        try {
            $queryParams = $request->toArray();
            return $this->get('api/nsk/v2/vouchers', $queryParams);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search vouchers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific voucher by key
     * 
     * GET /api/nsk/v1/vouchers/{voucherKey}
     * 
     * @param string $voucherKey Voucher key
     * @return array Voucher details
     * @throws JamboJetApiException
     */
    public function getVoucher(string $voucherKey): array
    {
        $this->validateKey($voucherKey, 'Voucher key');

        try {
            return $this->get("api/nsk/v1/vouchers/{$voucherKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get voucher: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get vouchers by agent information
     * 
     * GET /api/nsk/v2/vouchers/byAgent
     * 
     * @param array $criteria Agent search criteria (name, domain, dates, pagination)
     * @return array Paged vouchers response
     * @throws JamboJetApiException
     */
    public function getVouchersByAgent(array $criteria): array
    {
        $this->validateAgentCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/vouchers/byAgent', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get vouchers by agent: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Search vouchers by date range
     * 
     * GET /api/nsk/v1/vouchers/byDate
     * 
     * @param array $criteria Date range criteria (beginDate, endDate, pagination)
     * @return array Paged vouchers response
     * @throws JamboJetApiException
     */
    public function getVouchersByDate(array $criteria): array
    {
        $this->validateDateCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/vouchers/byDate', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get vouchers by date: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get vouchers by issuance key
     * 
     * GET /api/nsk/v2/vouchers/byIssuance/{voucherIssuanceKey}
     * 
     * @param string $voucherIssuanceKey Voucher issuance key
     * @param array $options Pagination and sort criteria
     * @return array Paged vouchers response
     * @throws JamboJetApiException
     */
    public function getVouchersByIssuance(string $voucherIssuanceKey, array $options = []): array
    {
        $this->validateKey($voucherIssuanceKey, 'Voucher issuance key');

        try {
            return $this->get("api/nsk/v2/vouchers/byIssuance/{$voucherIssuanceKey}", $options);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get vouchers by issuance: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get vouchers by market information
     * 
     * GET /api/nsk/v2/vouchers/byMarket
     * 
     * @param array $criteria Market search criteria (origin, destination, date, etc.)
     * @return array Paged vouchers response
     * @throws JamboJetApiException
     */
    public function getVouchersByMarket(array $criteria): array
    {
        $this->validateMarketCriteria($criteria);

        try {
            return $this->get('api/nsk/v2/vouchers/byMarket', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get vouchers by market: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get voucher by reference number
     * 
     * GET /api/nsk/v1/vouchers/byReference/{voucherReferenceNumber}
     * 
     * @param string $voucherReferenceNumber Voucher reference number
     * @return array Voucher reference details
     * @throws JamboJetApiException
     */
    public function getVoucherByReference(string $voucherReferenceNumber): array
    {
        if (empty(trim($voucherReferenceNumber))) {
            throw new JamboJetValidationException(
                'Voucher reference number cannot be empty',
                400
            );
        }

        try {
            return $this->get("api/nsk/v1/vouchers/byReference/{$voucherReferenceNumber}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get voucher by reference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VOUCHER MANAGEMENT METHODS
    // =================================================================

    /**
     * Update voucher status, type, or expiration
     * 
     * PATCH /api/nsk/v1/vouchers/{voucherKey}
     * Note: Only one field can be updated per call
     * 
     * @param string $voucherKey Voucher key
     * @param VoucherUpdateRequest|array $updateData Update data or request object
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateVoucher(string $voucherKey, VoucherUpdateRequest|array $updateData): array
    {
        $this->validateKey($voucherKey, 'Voucher key');

        if (is_array($updateData)) {
            $updateData = VoucherUpdateRequest::fromArray($updateData);
        }

        $updateData->validate();

        try {
            return $this->patch("api/nsk/v1/vouchers/{$voucherKey}", $updateData->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update voucher: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update voucher owner
     * 
     * PATCH /api/nsk/v1/vouchers/{voucherKey}/owner
     * 
     * @param string $voucherKey Voucher key
     * @param VoucherOwnerUpdateRequest|array $ownerData New owner data or request object
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateVoucherOwner(string $voucherKey, VoucherOwnerUpdateRequest|array $ownerData): array
    {
        $this->validateKey($voucherKey, 'Voucher key');

        if (is_array($ownerData)) {
            $ownerData = VoucherOwnerUpdateRequest::fromArray($ownerData);
        }

        $ownerData->validate();

        try {
            return $this->patch("api/nsk/v1/vouchers/{$voucherKey}/owner", $ownerData->toArray());
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update voucher owner: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VOUCHER CONFIGURATION METHODS
    // =================================================================

    /**
     * Get all voucher configurations (shallow call)
     * 
     * GET /api/nsk/v1/vouchers/configuration
     * 
     * @return array List of voucher configurations
     * @throws JamboJetApiException
     */
    public function getVoucherConfigurations(): array
    {
        try {
            return $this->get('api/nsk/v1/vouchers/configuration');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get voucher configurations: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific voucher configuration (deep call)
     * 
     * GET /api/nsk/v1/vouchers/configuration/{configurationCode}
     * 
     * @param string $configurationCode Configuration code
     * @return array Voucher configuration details
     * @throws JamboJetApiException
     */
    public function getVoucherConfiguration(string $configurationCode): array
    {
        $this->validateConfigurationCode($configurationCode);

        try {
            return $this->get("api/nsk/v1/vouchers/configuration/{$configurationCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get voucher configuration: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create new voucher configuration
     * 
     * POST /api/nsk/v2/vouchers/configuration
     * 
     * @param array $configurationData Configuration data
     * @return array Created configuration response
     * @throws JamboJetApiException
     */
    public function createVoucherConfiguration(array $configurationData): array
    {
        $this->validateConfigurationData($configurationData);

        try {
            return $this->post('api/nsk/v2/vouchers/configuration', $configurationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create voucher configuration: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update voucher configuration
     * 
     * PUT /api/nsk/v2/vouchers/configuration
     * 
     * @param array $configurationData Configuration data
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateVoucherConfiguration(array $configurationData): array
    {
        $this->validateConfigurationData($configurationData);

        try {
            return $this->put('api/nsk/v2/vouchers/configuration', $configurationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update voucher configuration: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete voucher configuration
     * 
     * DELETE /api/nsk/v1/vouchers/configuration
     * Note: Cannot delete if active vouchers exist
     * 
     * @param array $deleteData Delete request data (configurationCode, expirationDate)
     * @return array Delete response
     * @throws JamboJetApiException
     */
    public function deleteVoucherConfiguration(array $deleteData): array
    {
        $this->validateDeleteData($deleteData);

        try {
            return $this->delete('api/nsk/v1/vouchers/configuration', $deleteData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete voucher configuration: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate key format
     * 
     * @param string $key Key to validate
     * @param string $keyType Key type for error message
     * @throws JamboJetValidationException
     */
    private function validateKey(string $key, string $keyType): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException(
                "{$keyType} cannot be empty",
                400
            );
        }

        if (strlen($key) > 500) {
            throw new JamboJetValidationException(
                "{$keyType} exceeds maximum length",
                400
            );
        }
    }

    /**
     * Validate agent search criteria
     * 
     * @param array $criteria Agent criteria
     * @throws JamboJetValidationException
     */
    private function validateAgentCriteria(array $criteria): void
    {
        if (!isset($criteria['Name']) || !isset($criteria['Domain'])) {
            throw new JamboJetValidationException(
                'Both Name and Domain are required for agent search',
                400
            );
        }
    }

    /**
     * Validate date search criteria
     * 
     * @param array $criteria Date criteria
     * @throws JamboJetValidationException
     */
    private function validateDateCriteria(array $criteria): void
    {
        if (!isset($criteria['BeginDate'])) {
            throw new JamboJetValidationException(
                'BeginDate is required for date search',
                400
            );
        }
    }

    /**
     * Validate market search criteria
     * 
     * @param array $criteria Market criteria
     * @throws JamboJetValidationException
     */
    private function validateMarketCriteria(array $criteria): void
    {
        $requiredFields = ['Origin', 'Destination', 'DepartureDate', 'Identifier', 'CarrierCode'];
        foreach ($requiredFields as $field) {
            if (!isset($criteria[$field])) {
                throw new JamboJetValidationException(
                    "Market field '{$field}' is required",
                    400
                );
            }
        }
    }

    /**
     * Validate configuration code
     * 
     * @param string $code Configuration code
     * @throws JamboJetValidationException
     */
    private function validateConfigurationCode(string $code): void
    {
        if (empty(trim($code))) {
            throw new JamboJetValidationException(
                'Configuration code cannot be empty',
                400
            );
        }

        if (strlen($code) > 6) {
            throw new JamboJetValidationException(
                'Configuration code cannot exceed 6 characters',
                400
            );
        }
    }

    /**
     * Validate configuration data
     * 
     * @param array $data Configuration data
     * @throws JamboJetValidationException
     */
    private function validateConfigurationData(array $data): void
    {
        if (!isset($data['configurationCode'])) {
            throw new JamboJetValidationException(
                'Configuration code is required',
                400
            );
        }

        $this->validateConfigurationCode($data['configurationCode']);
    }

    /**
     * Validate delete request data
     * 
     * @param array $data Delete data
     * @throws JamboJetValidationException
     */
    private function validateDeleteData(array $data): void
    {
        if (!isset($data['configurationCode']) || !isset($data['expirationDate'])) {
            throw new JamboJetValidationException(
                'Both configurationCode and expirationDate are required for deletion',
                400
            );
        }

        $this->validateConfigurationCode($data['configurationCode']);
    }
}