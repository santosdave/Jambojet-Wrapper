<?php

namespace SantosDave\JamboJet\Contracts;

/**
 * Voucher Interface
 * 
 * Defines contract for voucher operations including issuance, search,
 * management, and configuration
 * 
 * @package SantosDave\JamboJet\Contracts
 */
interface VoucherInterface
{
    // =================================================================
    // VOUCHER ISSUANCE
    // =================================================================

    /**
     * Create vouchers based on request
     * POST /api/nsk/v2/voucherIssuance
     * Note: High volume creates batch job which may not process immediately
     * 
     * @param array $voucherData Voucher issuance data
     * @return array Created voucher issuance response
     */
    public function createVoucher(array $voucherData): array;

    /**
     * Get voucher issuance by key
     * GET /api/nsk/v1/voucherIssuance/{voucherIssuanceKey}
     * 
     * @param string $voucherIssuanceKey Voucher issuance key
     * @return array Voucher issuance details
     */
    public function getVoucherIssuance(string $voucherIssuanceKey): array;

    // =================================================================
    // VOUCHER SEARCH & RETRIEVAL
    // =================================================================

    /**
     * Search vouchers based on criteria
     * GET /api/nsk/v2/vouchers
     * Only returns vouchers from agent's non-restricted configurations
     * 
     * @param array $criteria Search criteria (market, agent, dates, customer, etc.)
     * @return array Paged vouchers response
     */
    public function searchVouchers(array $criteria = []): array;

    /**
     * Get specific voucher by key
     * GET /api/nsk/v1/vouchers/{voucherKey}
     * 
     * @param string $voucherKey Voucher key
     * @return array Voucher details
     */
    public function getVoucher(string $voucherKey): array;

    /**
     * Get vouchers by agent information
     * GET /api/nsk/v2/vouchers/byAgent
     * 
     * @param array $criteria Agent search criteria (name, domain, dates, pagination)
     * @return array Paged vouchers response
     */
    public function getVouchersByAgent(array $criteria): array;

    /**
     * Search vouchers by date range
     * GET /api/nsk/v1/vouchers/byDate
     * 
     * @param array $criteria Date range criteria (beginDate, endDate, pagination)
     * @return array Paged vouchers response
     */
    public function getVouchersByDate(array $criteria): array;

    /**
     * Get vouchers by issuance key
     * GET /api/nsk/v2/vouchers/byIssuance/{voucherIssuanceKey}
     * 
     * @param string $voucherIssuanceKey Voucher issuance key
     * @param array $options Pagination and sort criteria
     * @return array Paged vouchers response
     */
    public function getVouchersByIssuance(string $voucherIssuanceKey, array $options = []): array;

    /**
     * Get vouchers by market information
     * GET /api/nsk/v2/vouchers/byMarket
     * 
     * @param array $criteria Market search criteria (origin, destination, date, etc.)
     * @return array Paged vouchers response
     */
    public function getVouchersByMarket(array $criteria): array;

    /**
     * Get voucher by reference number
     * GET /api/nsk/v1/vouchers/byReference/{voucherReferenceNumber}
     * 
     * @param string $voucherReferenceNumber Voucher reference number
     * @return array Voucher reference details
     */
    public function getVoucherByReference(string $voucherReferenceNumber): array;

    // =================================================================
    // VOUCHER MANAGEMENT
    // =================================================================

    /**
     * Update voucher status, type, or expiration
     * PATCH /api/nsk/v1/vouchers/{voucherKey}
     * Note: Only one field can be updated per call
     * 
     * @param string $voucherKey Voucher key
     * @param array $updateData Update data (status, type, or expiration)
     * @return array Update response
     */
    public function updateVoucher(string $voucherKey, array $updateData): array;

    /**
     * Update voucher owner
     * PATCH /api/nsk/v1/vouchers/{voucherKey}/owner
     * 
     * @param string $voucherKey Voucher key
     * @param array $ownerData New owner data
     * @return array Update response
     */
    public function updateVoucherOwner(string $voucherKey, array $ownerData): array;

    // =================================================================
    // VOUCHER CONFIGURATION
    // =================================================================

    /**
     * Get all voucher configurations (shallow call)
     * GET /api/nsk/v1/vouchers/configuration
     * 
     * @return array List of voucher configurations
     */
    public function getVoucherConfigurations(): array;

    /**
     * Get specific voucher configuration (deep call)
     * GET /api/nsk/v1/vouchers/configuration/{configurationCode}
     * 
     * @param string $configurationCode Configuration code
     * @return array Voucher configuration details
     */
    public function getVoucherConfiguration(string $configurationCode): array;

    /**
     * Create new voucher configuration
     * POST /api/nsk/v2/vouchers/configuration
     * 
     * @param array $configurationData Configuration data
     * @return array Created configuration response
     */
    public function createVoucherConfiguration(array $configurationData): array;

    /**
     * Update voucher configuration
     * PUT /api/nsk/v2/vouchers/configuration
     * 
     * @param array $configurationData Configuration data
     * @return array Update response
     */
    public function updateVoucherConfiguration(array $configurationData): array;

    /**
     * Delete voucher configuration
     * DELETE /api/nsk/v1/vouchers/configuration
     * Note: Cannot delete if active vouchers exist
     * 
     * @param array $deleteData Delete request data (configurationCode, expirationDate)
     * @return array Delete response
     */
    public function deleteVoucherConfiguration(array $deleteData): array;
}
