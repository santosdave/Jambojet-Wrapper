<?php

namespace SantosDave\JamboJet\Contracts;

/**
 * Manifest Interface
 * 
 * Defines contract for flight manifest operations including passenger details,
 * bags, bookings, security information, seat assignments, and SSRs
 * 
 * @package SantosDave\JamboJet\Contracts
 */
interface ManifestInterface
{
    /**
     * Search manifests by trip information
     * GET /api/nsk/v2/manifest
     * 
     * @param array $criteria Search criteria (origin, destination, carrierCode, beginDate, identifier, flightType)
     * @return array List of trip information
     */
    public function searchManifests(array $criteria = []): array;

    /**
     * Get manifest for specified leg
     * GET /api/nsk/v1/manifest/{legKey}
     * 
     * @param string $legKey The manifest leg key (base64 encoded)
     * @return array ManifestLight with passengers, counts, inventory
     */
    public function getManifest(string $legKey): array;

    /**
     * Get passenger bags for specified leg
     * GET /api/nsk/v1/manifest/{legKey}/bags
     * 
     * @param string $legKey The manifest leg key
     * @param int $weightType Weight type (0=Default, 1=Pounds, 2=Kilograms)
     * @return array Dictionary of bags by recordLocator and passenger
     */
    public function getManifestBags(string $legKey, int $weightType = 0): array;

    /**
     * Get manifest bookings data for specified leg
     * GET /api/nsk/v1/manifest/{legKey}/bookings
     * 
     * @param string $legKey The manifest leg key
     * @return array Dictionary of manifest bookings
     */
    public function getManifestBookings(string $legKey): array;

    /**
     * Get passenger government security information
     * GET /api/nsk/v1/manifest/{legKey}/governmentSecurityInfo
     * 
     * @param string $legKey The manifest leg key
     * @return array Dictionary of security info by recordLocator
     */
    public function getGovernmentSecurityInfo(string $legKey): array;

    /**
     * Get passenger details for specified leg
     * GET /api/nsk/v2/manifest/{legKey}/passengerDetails
     * Requires agent permissions and role permissions
     * 
     * @param string $legKey The manifest leg key
     * @return array Passenger details with summary
     */
    public function getPassengerDetails(string $legKey): array;

    /**
     * Get weight balance passenger details
     * GET /api/dcs/v1/manifest/{legKey}/passengerDetails/weightBalance
     * 
     * @param string $legKey The manifest leg key
     * @return array Passenger weight balance summary
     */
    public function getWeightBalanceDetails(string $legKey): array;

    /**
     * Get seat assignment reports for specified leg
     * GET /api/nsk/v1/manifest/{legKey}/passengerSeatAssignments
     * 
     * @param string $legKey The manifest leg key
     * @return array List of passenger seat assignments
     */
    public function getPassengerSeatAssignments(string $legKey): array;

    /**
     * Get manifest segment data for specified leg
     * GET /api/nsk/v1/manifest/{legKey}/segments
     * 
     * @param string $legKey The manifest leg key
     * @param bool $includeNonOriginData Include segments associated outside the requested leg
     * @return array List of manifest by segment
     */
    public function getManifestSegments(string $legKey, bool $includeNonOriginData = false): array;

    /**
     * Get manifest SSRs (Special Service Requests)
     * GET /api/nsk/v1/manifest/{legKey}/ssrs
     * 
     * @param string $legKey The manifest leg key
     * @param bool $includeNonOriginSsrs Include SSRs outside the requested leg
     * @return array List of manifest SSRs
     */
    public function getManifestSSRs(string $legKey, bool $includeNonOriginSsrs = false): array;
}
