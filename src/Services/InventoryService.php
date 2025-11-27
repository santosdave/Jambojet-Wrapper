<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\InventoryInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Inventory Service for JamboJet NSK API
 * 
 * Manages flight inventory including legs, nests, classes, and SSR LIDs.
 * Base path: /api/dcs/v1 and /api/dcs/v2 (DCS = Departure Control System)
 * 
 * All modification operations require agent permissions.
 * 
 * Supported endpoints:
 * - GET/PATCH /api/dcs/v2/inventory/legs/{legKey}
 * - GET/PATCH /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}
 * - GET/PATCH /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}/classes/{legClassKey}
 * - POST/PATCH /api/dcs/v1/inventory/legs/ssrs/lids
 * - GET /api/dcs/v1/inventory/routes/{tripKey}/classes
 * - PATCH /api/dcs/v1/inventory/routes/{tripKey}/classes/{classOfService}
 * - DELETE /api/dcs/v1/inventory/routes/{tripKey}/classes/{classOfService}/authorizedUnit
 * 
 * @package SantosDave\JamboJet\Services
 */
class InventoryService implements InventoryInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // LEG OPERATIONS
    // =================================================================

    /**
     * Get inventory leg details
     * GET /api/dcs/v2/inventory/legs/{legKey}
     */
    public function getInventoryLeg(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/dcs/v2/inventory/legs/{$legKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get inventory leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update inventory leg, nests, and classes
     * PATCH /api/dcs/v2/inventory/legs/{legKey}
     */
    public function updateInventoryLeg(string $legKey, array $legEditData): array
    {
        $this->validateLegKey($legKey);
        $this->validateLegEditData($legEditData);

        try {
            return $this->patch("api/dcs/v2/inventory/legs/{$legKey}", $legEditData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update inventory leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // NEST OPERATIONS
    // =================================================================

    /**
     * Get specific inventory nest within a leg
     * GET /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}
     */
    public function getInventoryNest(string $legKey, string $legNestKey): array
    {
        $this->validateLegKey($legKey);
        $this->validateNestKey($legNestKey);

        try {
            return $this->get("api/dcs/v1/inventory/legs/{$legKey}/nests/{$legNestKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get inventory nest: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update inventory nest configuration
     * PATCH /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}
     */
    public function updateInventoryNest(string $legKey, string $legNestKey, array $nestEditData): array
    {
        $this->validateLegKey($legKey);
        $this->validateNestKey($legNestKey);
        $this->validateNestEditData($nestEditData);

        try {
            return $this->patch("api/dcs/v1/inventory/legs/{$legKey}/nests/{$legNestKey}", $nestEditData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update inventory nest: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // CLASS OPERATIONS
    // =================================================================

    /**
     * Get specific inventory class within a nest
     * GET /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}/classes/{legClassKey}
     */
    public function getInventoryClass(string $legKey, string $legNestKey, string $legClassKey): array
    {
        $this->validateLegKey($legKey);
        $this->validateNestKey($legNestKey);
        $this->validateClassKey($legClassKey);

        try {
            return $this->get("api/dcs/v1/inventory/legs/{$legKey}/nests/{$legNestKey}/classes/{$legClassKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get inventory class: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update inventory class configuration
     * PATCH /api/dcs/v1/inventory/legs/{legKey}/nests/{legNestKey}/classes/{legClassKey}
     */
    public function updateInventoryClass(
        string $legKey,
        string $legNestKey,
        string $legClassKey,
        array $classEditData
    ): array {
        $this->validateLegKey($legKey);
        $this->validateNestKey($legNestKey);
        $this->validateClassKey($legClassKey);
        $this->validateClassEditData($classEditData);

        try {
            return $this->patch(
                "api/dcs/v1/inventory/legs/{$legKey}/nests/{$legNestKey}/classes/{$legClassKey}",
                $classEditData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update inventory class: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // SSR LID BATCH OPERATIONS
    // =================================================================

    /**
     * Get SSR LIDs for multiple legs (batch operation)
     * POST /api/dcs/v1/inventory/legs/ssrs/lids (behaves as GET)
     */
    public function getInventorySsrLids(array $legKeys): array
    {
        if (empty($legKeys)) {
            throw new JamboJetValidationException('At least one leg key is required', 400);
        }

        foreach ($legKeys as $legKey) {
            $this->validateLegKey($legKey);
        }

        try {
            return $this->post('api/dcs/v1/inventory/legs/ssrs/lids', $legKeys);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get inventory SSR LIDs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update SSR LIDs for multiple legs (batch operation)
     * PATCH /api/dcs/v1/inventory/legs/ssrs/lids
     */
    public function updateInventorySsrLids(array $ssrLidUpdates): array
    {
        $this->validateSsrLidUpdates($ssrLidUpdates);

        try {
            return $this->patch('api/dcs/v1/inventory/legs/ssrs/lids', $ssrLidUpdates);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update inventory SSR LIDs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // ROUTE CLASS OPERATIONS
    // =================================================================

    /**
     * Get all classes for a route
     * GET /api/dcs/v1/inventory/routes/{tripKey}/classes
     */
    public function getRouteClasses(string $tripKey): array
    {
        $this->validateTripKey($tripKey);

        try {
            return $this->get("api/dcs/v1/inventory/routes/{$tripKey}/classes");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get route classes: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update route class configuration
     * PATCH /api/dcs/v1/inventory/routes/{tripKey}/classes/{classOfService}
     */
    public function updateRouteClass(string $tripKey, string $classOfService, array $classEditData): array
    {
        $this->validateTripKey($tripKey);
        $this->validateClassOfService($classOfService);
        $this->validateRouteClassEditData($classEditData);

        try {
            return $this->patch(
                "api/dcs/v1/inventory/routes/{$tripKey}/classes/{$classOfService}",
                $classEditData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update route class: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete authorized unit for route class
     * DELETE /api/dcs/v1/inventory/routes/{tripKey}/classes/{classOfService}/authorizedUnit
     */
    public function deleteRouteClassAuthorizedUnit(string $tripKey, string $classOfService): array
    {
        $this->validateTripKey($tripKey);
        $this->validateClassOfService($classOfService);

        try {
            return $this->delete("api/dcs/v1/inventory/routes/{$tripKey}/classes/{$classOfService}/authorizedUnit");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete route class authorized unit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION HELPERS
    // =================================================================

    /**
     * Validate leg key format
     */
    private function validateLegKey(string $legKey): void
    {
        if (empty($legKey)) {
            throw new JamboJetValidationException('Leg key is required', 400);
        }

        // Leg keys are typically base64-encoded, should be at least 20 chars
        if (strlen($legKey) < 20) {
            throw new JamboJetValidationException('Invalid leg key format', 400);
        }
    }

    /**
     * Validate nest key format
     */
    private function validateNestKey(string $nestKey): void
    {
        if (empty($nestKey)) {
            throw new JamboJetValidationException('Nest key is required', 400);
        }
    }

    /**
     * Validate class key format
     */
    private function validateClassKey(string $classKey): void
    {
        if (empty($classKey)) {
            throw new JamboJetValidationException('Class key is required', 400);
        }
    }

    /**
     * Validate trip key format
     */
    private function validateTripKey(string $tripKey): void
    {
        if (empty($tripKey)) {
            throw new JamboJetValidationException('Trip key is required', 400);
        }
    }

    /**
     * Validate class of service
     */
    private function validateClassOfService(string $classOfService): void
    {
        if (empty($classOfService)) {
            throw new JamboJetValidationException('Class of service is required', 400);
        }

        // Class of service is typically 1-2 characters (Y, J, F, etc.)
        if (strlen($classOfService) > 10) {
            throw new JamboJetValidationException('Invalid class of service format', 400);
        }
    }

    /**
     * Validate leg edit data
     */
    private function validateLegEditData(array $data): void
    {
        // Validate lid if provided
        if (isset($data['lid']) && $data['lid'] !== null && $data['lid'] < 0) {
            throw new JamboJetValidationException('LID must be non-negative', 400);
        }

        // Validate adjustedCapacity if provided
        if (isset($data['adjustedCapacity']) && $data['adjustedCapacity'] !== null && $data['adjustedCapacity'] < 0) {
            throw new JamboJetValidationException('Adjusted capacity must be non-negative', 400);
        }

        // Validate status if provided
        if (isset($data['status']) && $data['status'] !== null && !in_array($data['status'], [0, 1, 2, 3])) {
            throw new JamboJetValidationException('Invalid status value', 400);
        }

        // Validate nests structure if provided
        if (isset($data['nests']) && $data['nests'] !== null && !is_array($data['nests'])) {
            throw new JamboJetValidationException('Nests must be an array', 400);
        }
    }

    /**
     * Validate nest edit data
     */
    private function validateNestEditData(array $data): void
    {
        if (isset($data['lid']) && $data['lid'] !== null && $data['lid'] < 0) {
            throw new JamboJetValidationException('Nest LID must be non-negative', 400);
        }

        if (isset($data['adjustedCapacity']) && $data['adjustedCapacity'] !== null && $data['adjustedCapacity'] < 0) {
            throw new JamboJetValidationException('Adjusted capacity must be non-negative', 400);
        }
    }

    /**
     * Validate class edit data
     */
    private function validateClassEditData(array $data): void
    {
        if (isset($data['rank']) && $data['rank'] !== null && $data['rank'] < 0) {
            throw new JamboJetValidationException('Class rank must be non-negative', 400);
        }

        if (isset($data['authorizedUnits']) && $data['authorizedUnits'] !== null && $data['authorizedUnits'] < 0) {
            throw new JamboJetValidationException('Authorized units must be non-negative', 400);
        }

        if (isset($data['allotted']) && $data['allotted'] !== null && $data['allotted'] < 0) {
            throw new JamboJetValidationException('Allotted must be non-negative', 400);
        }

        if (isset($data['latestAdvancedReservation']) && $data['latestAdvancedReservation'] !== null && $data['latestAdvancedReservation'] < 0) {
            throw new JamboJetValidationException('Latest advanced reservation must be non-negative', 400);
        }
    }

    /**
     * Validate SSR LID updates
     */
    private function validateSsrLidUpdates(array $updates): void
    {
        if (empty($updates)) {
            throw new JamboJetValidationException('At least one SSR LID update is required', 400);
        }

        foreach ($updates as $update) {
            if (!isset($update['legKey']) || empty($update['legKey'])) {
                throw new JamboJetValidationException('Each update must have a leg key', 400);
            }

            $this->validateLegKey($update['legKey']);

            if (!isset($update['ssrLids']) || !is_array($update['ssrLids']) || empty($update['ssrLids'])) {
                throw new JamboJetValidationException('Each update must have SSR LIDs array', 400);
            }

            foreach ($update['ssrLids'] as $ssrLid) {
                if (!isset($ssrLid['ssrNestCode']) || empty($ssrLid['ssrNestCode'])) {
                    throw new JamboJetValidationException('SSR nest code is required', 400);
                }

                if (!isset($ssrLid['lid']) || $ssrLid['lid'] < 0) {
                    throw new JamboJetValidationException('SSR LID must be non-negative', 400);
                }

                // Validate SSR nest code format (typically 4 chars: MEAL, WIFI, PETC, etc.)
                if (strlen($ssrLid['ssrNestCode']) > 10) {
                    throw new JamboJetValidationException('Invalid SSR nest code format', 400);
                }
            }
        }
    }

    /**
     * Validate route class edit data
     */
    private function validateRouteClassEditData(array $data): void
    {
        if (empty($data)) {
            throw new JamboJetValidationException('Route class edit data cannot be empty', 400);
        }

        // Add specific validation based on route class structure
        if (isset($data['rank']) && $data['rank'] !== null && $data['rank'] < 0) {
            throw new JamboJetValidationException('Route class rank must be non-negative', 400);
        }
    }
}
