<?php

namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;

/**
 * Inventory Interface for JamboJet NSK API
 * 
 * Manages flight inventory including legs, nests, classes, and SSR LIDs.
 * Base path: /api/dcs/v1 and /api/dcs/v2 (DCS = Departure Control System)
 * 
 * Requires agent permissions for all modification operations.
 * 
 * @package SantosDave\JamboJet\Contracts
 */
interface InventoryInterface
{
    // =================================================================
    // LEG OPERATIONS (2 methods)
    // =================================================================

    /**
     * Get inventory leg details
     * GET /api/dcs/v2/inventory/legs/{legKey}
     * 
     * @param string $legKey The leg key (base64-encoded flight identifier)
     * @return array Inventory leg details including nests and classes
     * @throws JamboJetApiException
     */
    public function getInventoryLeg(string $legKey): array;

    /**
     * Update inventory leg, nests, and classes
     * PATCH /api/dcs/v2/inventory/legs/{legKey}
     * 
     * Can update leg, nests, and classes in a single operation.
     * Supports partial updates - only specified fields are modified.
     * 
     * @param string $legKey The leg key
     * @param array $legEditData Leg edit data (lid, adjustedCapacity, sendAvsMessages, status, nests)
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateInventoryLeg(string $legKey, array $legEditData): array;

    // =================================================================
    // NEST OPERATIONS (2 methods)
    // =================================================================

    /**
     * Get specific inventory nest within a leg
     * GET /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}
     * 
     * @param string $legKey The leg key
     * @param string $legNestKey The leg nest key
     * @return array Inventory nest details
     * @throws JamboJetApiException
     */
    public function getInventoryNest(string $legKey, string $legNestKey): array;

    /**
     * Update inventory nest configuration
     * PATCH /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}
     * 
     * @param string $legKey The leg key
     * @param string $legNestKey The leg nest key
     * @param array $nestEditData Nest edit data (lid, adjustedCapacity)
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateInventoryNest(string $legKey, string $legNestKey, array $nestEditData): array;

    // =================================================================
    // CLASS OPERATIONS (2 methods)
    // =================================================================

    /**
     * Get specific inventory class within a nest
     * GET /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}/classes/{legClassKey}
     * 
     * @param string $legKey The leg key
     * @param string $legNestKey The leg nest key
     * @param string $legClassKey The leg class key
     * @return array Inventory class details
     * @throws JamboJetApiException
     */
    public function getInventoryClass(string $legKey, string $legNestKey, string $legClassKey): array;

    /**
     * Update inventory class configuration
     * PATCH /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}/classes/{legClassKey}
     * 
     * Updates class rank, authorized units, allotment, and advance reservation settings.
     * 
     * @param string $legKey The leg key
     * @param string $legNestKey The leg nest key
     * @param string $legClassKey The leg class key
     * @param array $classEditData Class edit data (type, rank, authorizedUnits, allotted, latestAdvancedReservation)
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateInventoryClass(string $legKey, string $legNestKey, string $legClassKey, array $classEditData): array;

    // =================================================================
    // SSR LID BATCH OPERATIONS (2 methods)
    // =================================================================

    /**
     * Get SSR LIDs for multiple legs (batch operation)
     * POST /api/dcs/v1/inventory/legs/ssrs/lids (behaves as GET)
     * 
     * Returns SSR nest codes and their LID counts for each leg.
     * May return HTTP 207 for partial success.
     * 
     * SSR codes include: BLND, CHLD, MEAL, WIFI, PETC, BG15, BG20, LEGX, etc.
     * 
     * @param array $legKeys Array of leg keys to retrieve
     * @return array Dictionary of leg keys to SSR LID lists
     * @throws JamboJetApiException
     */
    public function getInventorySsrLids(array $legKeys): array;

    /**
     * Update SSR LIDs for multiple legs (batch operation)
     * PATCH /api/dcs/v1/inventory/legs/ssrs/lids
     * 
     * Updates SSR inventory limits for multiple legs in single request.
     * SSRs not included in request remain unchanged.
     * May return HTTP 207 for partial success.
     * 
     * @param array $ssrLidUpdates Array of leg keys with their SSR LID updates
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateInventorySsrLids(array $ssrLidUpdates): array;

    // =================================================================
    // ROUTE CLASS OPERATIONS (3 methods)
    // =================================================================

    /**
     * Get all classes for a route
     * GET /api/dcs/v1/inventory/routes/{tripKey}/classes
     * 
     * @param string $tripKey Trip key from manifest or flight info
     * @return array List of route classes
     * @throws JamboJetApiException
     */
    public function getRouteClasses(string $tripKey): array;

    /**
     * Update route class configuration
     * PATCH /api/dcs/v1/inventory/routes/{tripKey}/classes/{classOfService}
     * 
     * @param string $tripKey Trip key
     * @param string $classOfService Class of service (Economy, Business, First, etc.)
     * @param array $classEditData Route class edit data
     * @return array Update result
     * @throws JamboJetApiException
     */
    public function updateRouteClass(string $tripKey, string $classOfService, array $classEditData): array;

    /**
     * Delete authorized unit for route class
     * DELETE /api/dcs/v1/inventory/routes/{tripKey}/classes/{classOfService}/authorizedUnit
     * 
     * @param string $tripKey Trip key
     * @param string $classOfService Class of service
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteRouteClassAuthorizedUnit(string $tripKey, string $classOfService): array;
}
