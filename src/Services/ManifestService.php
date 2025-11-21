<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\ManifestInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;
use SantosDave\JamboJet\Requests\ManifestSearchRequest;

/**
 * Manifest Service for JamboJet NSK API
 * 
 * Handles flight manifest operations including passenger details, bags, bookings,
 * security information, seat assignments, segments, and SSRs
 * Base endpoints: /api/nsk/v1/manifest, /api/nsk/v2/manifest, /api/dcs/v1/manifest
 * 
 * Supported endpoints:
 * - GET /api/nsk/v2/manifest - Search manifests
 * - GET /api/nsk/v1/manifest/{legKey} - Get manifest details
 * - GET /api/nsk/v1/manifest/{legKey}/bags - Get passenger bags
 * - GET /api/nsk/v1/manifest/{legKey}/bookings - Get bookings
 * - GET /api/nsk/v1/manifest/{legKey}/governmentSecurityInfo - Get security info
 * - GET /api/nsk/v2/manifest/{legKey}/passengerDetails - Get passenger details
 * - GET /api/dcs/v1/manifest/{legKey}/passengerDetails/weightBalance - Get weight balance
 * - GET /api/nsk/v1/manifest/{legKey}/passengerSeatAssignments - Get seat assignments
 * - GET /api/nsk/v1/manifest/{legKey}/segments - Get segments
 * - GET /api/nsk/v1/manifest/{legKey}/ssrs - Get SSRs
 * 
 * @package SantosDave\JamboJet\Services
 */
class ManifestService implements ManifestInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // Weight type constants
    public const WEIGHT_TYPE_DEFAULT = 0;
    public const WEIGHT_TYPE_POUNDS = 1;
    public const WEIGHT_TYPE_KILOGRAMS = 2;

    // =================================================================
    // INTERFACE REQUIRED METHODS - MANIFEST OPERATIONS
    // =================================================================

    /**
     * Search manifests by trip information
     * 
     * GET /api/nsk/v2/manifest
     * Gets manifest trip information response list
     * 
     * @param ManifestSearchRequest|array $request Search criteria or request object
     * @return array List of trip information
     * @throws JamboJetApiException
     */
    public function searchManifests(ManifestSearchRequest|array $request = []): array
    {
        // Convert array to Request object if needed
        if (is_array($request)) {
            $request = ManifestSearchRequest::fromArray($request);
        }

        $request->validate();

        try {
            $queryParams = $request->toArray();
            return $this->get('api/nsk/v2/manifest', $queryParams);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search manifests: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get manifest for specified leg
     * 
     * GET /api/nsk/v1/manifest/{legKey}
     * Returns ManifestLight with passengers, counts, and inventory
     * 
     * @param string $legKey The manifest leg key (base64 encoded)
     * @return array Manifest details
     * @throws JamboJetApiException
     */
    public function getManifest(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v1/manifest/{$legKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get manifest: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get passenger bags for specified leg
     * 
     * GET /api/nsk/v1/manifest/{legKey}/bags
     * Returns dictionary of bags by recordLocator and passenger
     * 
     * @param string $legKey The manifest leg key
     * @param int $weightType Weight type (0=Default, 1=Pounds, 2=Kilograms)
     * @return array Dictionary of passenger bags
     * @throws JamboJetApiException
     */
    public function getManifestBags(string $legKey, int $weightType = self::WEIGHT_TYPE_DEFAULT): array
    {
        $this->validateLegKey($legKey);
        $this->validateWeightType($weightType);

        try {
            $queryParams = ['weightType' => $weightType];
            return $this->get("api/nsk/v1/manifest/{$legKey}/bags", $queryParams);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get manifest bags: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get manifest bookings data for specified leg
     * 
     * GET /api/nsk/v1/manifest/{legKey}/bookings
     * Returns dictionary of manifest bookings
     * 
     * @param string $legKey The manifest leg key
     * @return array Dictionary of manifest bookings
     * @throws JamboJetApiException
     */
    public function getManifestBookings(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v1/manifest/{$legKey}/bookings");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get manifest bookings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get passenger government security information
     * 
     * GET /api/nsk/v1/manifest/{legKey}/governmentSecurityInfo
     * Returns dictionary of security info by recordLocator
     * 
     * @param string $legKey The manifest leg key
     * @return array Dictionary of government security information
     * @throws JamboJetApiException
     */
    public function getGovernmentSecurityInfo(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v1/manifest/{$legKey}/governmentSecurityInfo");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get government security info: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get passenger details for specified leg
     * 
     * GET /api/nsk/v2/manifest/{legKey}/passengerDetails
     * Requires agent permissions and role permissions in Management Console
     * Returns passenger details with summary
     * 
     * @param string $legKey The manifest leg key
     * @return array Passenger details with summary
     * @throws JamboJetApiException
     */
    public function getPassengerDetails(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v2/manifest/{$legKey}/passengerDetails");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get passenger details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get weight balance passenger details
     * 
     * GET /api/dcs/v1/manifest/{legKey}/passengerDetails/weightBalance
     * Returns passenger weight balance summary
     * 
     * @param string $legKey The manifest leg key
     * @return array Passenger weight balance summary
     * @throws JamboJetApiException
     */
    public function getWeightBalanceDetails(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/dcs/v1/manifest/{$legKey}/passengerDetails/weightBalance");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get weight balance details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get seat assignment reports for specified leg
     * 
     * GET /api/nsk/v1/manifest/{legKey}/passengerSeatAssignments
     * Returns list of passenger seat assignments
     * 
     * @param string $legKey The manifest leg key
     * @return array List of passenger seat assignments
     * @throws JamboJetApiException
     */
    public function getPassengerSeatAssignments(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v1/manifest/{$legKey}/passengerSeatAssignments");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get passenger seat assignments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get manifest segment data for specified leg
     * 
     * GET /api/nsk/v1/manifest/{legKey}/segments
     * Returns list of manifest by segment
     * Structure resembles original v2 manifest without duplicate data
     * 
     * @param string $legKey The manifest leg key
     * @param bool $includeNonOriginData Include segments associated outside the requested leg
     * @return array List of manifest by segment
     * @throws JamboJetApiException
     */
    public function getManifestSegments(string $legKey, bool $includeNonOriginData = false): array
    {
        $this->validateLegKey($legKey);

        try {
            $queryParams = ['includeNonOriginData' => $includeNonOriginData];
            return $this->get("api/nsk/v1/manifest/{$legKey}/segments", $queryParams);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get manifest segments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get manifest SSRs (Special Service Requests)
     * 
     * GET /api/nsk/v1/manifest/{legKey}/ssrs
     * Returns list of manifest SSRs
     * 
     * @param string $legKey The manifest leg key
     * @param bool $includeNonOriginSsrs Include SSRs outside the requested leg
     * @return array List of manifest SSRs
     * @throws JamboJetApiException
     */
    public function getManifestSSRs(string $legKey, bool $includeNonOriginSsrs = false): array
    {
        $this->validateLegKey($legKey);

        try {
            $queryParams = ['includeNonOriginSsrs' => $includeNonOriginSsrs];
            return $this->get("api/nsk/v1/manifest/{$legKey}/ssrs", $queryParams);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get manifest SSRs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate leg key format
     * 
     * @param string $legKey Leg key
     * @throws JamboJetValidationException
     */
    private function validateLegKey(string $legKey): void
    {
        if (empty(trim($legKey))) {
            throw new JamboJetValidationException(
                'Leg key cannot be empty',
                400
            );
        }

        // Leg keys are typically base64 encoded, should not be extremely long
        if (strlen($legKey) > 500) {
            throw new JamboJetValidationException(
                'Leg key exceeds maximum length',
                400
            );
        }
    }

    /**
     * Validate weight type value
     * 
     * @param int $weightType Weight type
     * @throws JamboJetValidationException
     */
    private function validateWeightType(int $weightType): void
    {
        $validTypes = [
            self::WEIGHT_TYPE_DEFAULT,
            self::WEIGHT_TYPE_POUNDS,
            self::WEIGHT_TYPE_KILOGRAMS
        ];

        if (!in_array($weightType, $validTypes, true)) {
            throw new JamboJetValidationException(
                'Invalid weight type. Must be 0 (Default), 1 (Pounds), or 2 (Kilograms)',
                400
            );
        }
    }
}
