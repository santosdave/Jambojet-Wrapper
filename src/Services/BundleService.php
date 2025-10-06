<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\BundleInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Bundle Service for JamboJet NSK API
 * 
 * Handles all bundle operations including availability, adding, removing, and pricing
 * Base endpoints: /api/nsk/v1/booking/bundle
 * 
 * Supported endpoints:
 * - POST /api/nsk/v1/booking/bundle/availability - Get bundle availability
 * - POST /api/nsk/v1/booking/bundle/add - Add bundle to booking
 * - DELETE /api/nsk/v1/booking/bundle/{bundleKey} - Remove bundle
 * - GET /api/nsk/v1/booking/bundles - Get current bundles
 * - PUT /api/nsk/v1/booking/bundle/{bundleKey} - Update bundle
 * - GET /api/nsk/v1/resources/bundles/{bundleCode} - Get bundle configuration
 * - POST /api/nsk/v1/booking/bundle/pricing - Get bundle pricing
 * - POST /api/nsk/v1/booking/bundle/validate - Validate bundle compatibility
 * 
 * @package SantosDave\JamboJet\Services
 */
class BundleService implements BundleInterface
{
    use HandlesApiRequests, ValidatesRequests;

    /**
     * Get bundle and SSR availability for booking in state
     * POST /api/nsk/v1/booking/bundle/availability
     */
    public function getBundleAvailability(array $availabilityCriteria = []): array
    {
        $this->validateBundleAvailabilityRequest($availabilityCriteria);

        return $this->post('/api/nsk/v1/booking/bundle/availability', $availabilityCriteria);
    }

    /**
     * Add bundle to booking in state
     * POST /api/nsk/v1/booking/bundle/add
     */
    public function addBundle(array $bundleData): array
    {
        $this->validateAddBundleRequest($bundleData);

        return $this->post('/api/nsk/v1/booking/bundle/add', $bundleData);
    }

    /**
     * Remove bundle from booking
     * DELETE /api/nsk/v1/booking/bundle/{bundleKey}
     */
    public function removeBundle(string $bundleKey): array
    {
        $this->validateBundleKey($bundleKey);

        return $this->delete("/api/nsk/v1/booking/bundle/{$bundleKey}");
    }

    /**
     * Get current bundles in booking
     * GET /api/nsk/v1/booking/bundles
     */
    public function getBundles(): array
    {
        return $this->get('/api/nsk/v1/booking/bundles');
    }

    /**
     * Update bundle in booking
     * PUT /api/nsk/v1/booking/bundle/{bundleKey}
     */
    public function updateBundle(string $bundleKey, array $updateData): array
    {
        $this->validateBundleKey($bundleKey);
        $this->validateUpdateBundleRequest($updateData);

        return $this->put("/api/nsk/v1/booking/bundle/{$bundleKey}", $updateData);
    }

    /**
     * Get bundle configuration details
     * GET /api/nsk/v1/resources/bundles/{bundleCode}
     */
    public function getBundleConfiguration(string $bundleCode): array
    {
        $this->validateBundleCode($bundleCode);

        return $this->get("/api/nsk/v1/resources/bundles/{$bundleCode}");
    }

    /**
     * Get bundle pricing for specific criteria
     * POST /api/nsk/v1/booking/bundle/pricing
     */
    public function getBundlePricing(array $pricingCriteria): array
    {
        $this->validateBundlePricingRequest($pricingCriteria);

        return $this->post('/api/nsk/v1/booking/bundle/pricing', $pricingCriteria);
    }

    /**
     * Validate bundle compatibility with booking
     * POST /api/nsk/v1/booking/bundle/validate
     */
    public function validateBundle(array $bundleValidationData): array
    {
        $this->validateBundleValidationRequest($bundleValidationData);

        return $this->post('/api/nsk/v1/booking/bundle/validate', $bundleValidationData);
    }

    // ==========================================
    // VALIDATION METHODS (No Empty Arrays!)
    // ==========================================

    /**
     * Validate bundle availability request data
     */
    private function validateBundleAvailabilityRequest(array $data): void
    {
        // Optional criteria, but if provided should be valid
        if (!empty($data)) {
            // Common availability criteria
            if (isset($data['segmentKeys']) && !is_array($data['segmentKeys'])) {
                throw new JamboJetValidationException('segmentKeys must be an array');
            }

            if (isset($data['passengerKeys']) && !is_array($data['passengerKeys'])) {
                throw new JamboJetValidationException('passengerKeys must be an array');
            }

            if (isset($data['bundleTypes']) && !is_array($data['bundleTypes'])) {
                throw new JamboJetValidationException('bundleTypes must be an array');
            }
        }
    }

    /**
     * Validate add bundle request data
     */
    private function validateAddBundleRequest(array $data): void
    {
        $this->validateRequired($data, ['bundleCode']);

        // Validate bundle code format
        if (!is_string($data['bundleCode']) || empty(trim($data['bundleCode']))) {
            throw new JamboJetValidationException('bundleCode must be a non-empty string');
        }

        // Validate passenger assignments if provided
        if (isset($data['passengerAssignments'])) {
            if (!is_array($data['passengerAssignments'])) {
                throw new JamboJetValidationException('passengerAssignments must be an array');
            }

            foreach ($data['passengerAssignments'] as $assignment) {
                if (!isset($assignment['passengerKey']) || !isset($assignment['segmentKey'])) {
                    throw new JamboJetValidationException('Each passenger assignment must have passengerKey and segmentKey');
                }
            }
        }

        // Validate bundle options if provided
        if (isset($data['bundleOptions']) && !is_array($data['bundleOptions'])) {
            throw new JamboJetValidationException('bundleOptions must be an array');
        }
    }

    /**
     * Validate bundle key parameter
     */
    private function validateBundleKey(string $bundleKey): void
    {
        if (empty(trim($bundleKey))) {
            throw new JamboJetValidationException('bundleKey cannot be empty');
        }

        // Bundle key format validation (typically alphanumeric)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $bundleKey)) {
            throw new JamboJetValidationException('bundleKey contains invalid characters');
        }
    }

    /**
     * Validate update bundle request data
     */
    private function validateUpdateBundleRequest(array $data): void
    {
        if (empty($data)) {
            throw new JamboJetValidationException('Update data cannot be empty');
        }

        // Validate allowed update fields
        $allowedFields = ['bundleOptions', 'passengerAssignments', 'quantity', 'price'];
        $providedFields = array_keys($data);
        $invalidFields = array_diff($providedFields, $allowedFields);

        if (!empty($invalidFields)) {
            throw new JamboJetValidationException('Invalid fields: ' . implode(', ', $invalidFields));
        }

        // Validate quantity if provided
        if (isset($data['quantity']) && (!is_int($data['quantity']) || $data['quantity'] < 0)) {
            throw new JamboJetValidationException('quantity must be a non-negative integer');
        }

        // Validate price if provided
        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            throw new JamboJetValidationException('price must be a non-negative number');
        }
    }

    /**
     * Validate bundle code parameter
     */
    private function validateBundleCode(string $bundleCode): void
    {
        if (empty(trim($bundleCode))) {
            throw new JamboJetValidationException('bundleCode cannot be empty');
        }

        // Bundle code format validation (typically uppercase alphanumeric)
        if (!preg_match('/^[A-Z0-9_-]+$/', $bundleCode)) {
            throw new JamboJetValidationException('bundleCode must be uppercase alphanumeric with underscores or hyphens only');
        }
    }

    /**
     * Validate bundle pricing request data
     */
    private function validateBundlePricingRequest(array $data): void
    {
        $this->validateRequired($data, ['bundleCode']);

        // Validate bundle code
        $this->validateBundleCode($data['bundleCode']);

        // Validate pricing criteria
        if (isset($data['segments']) && !is_array($data['segments'])) {
            throw new JamboJetValidationException('segments must be an array');
        }

        if (isset($data['passengers']) && !is_array($data['passengers'])) {
            throw new JamboJetValidationException('passengers must be an array');
        }

        // Validate currency if provided
        if (isset($data['currency'])) {
            if (!is_string($data['currency']) || !preg_match('/^[A-Z]{3}$/', $data['currency'])) {
                throw new JamboJetValidationException('currency must be a 3-letter ISO currency code');
            }
        }

        // Validate date format if provided
        if (isset($data['pricingDate'])) {
            $this->validateFormats($data, ['pricingDate' => 'date']);
        }
    }

    /**
     * Validate bundle validation request data
     */
    private function validateBundleValidationRequest(array $data): void
    {
        $this->validateRequired($data, ['bundleCode']);

        // Validate bundle code
        $this->validateBundleCode($data['bundleCode']);

        // Validate booking context if provided
        if (isset($data['bookingKey']) && (!is_string($data['bookingKey']) || empty(trim($data['bookingKey'])))) {
            throw new JamboJetValidationException('bookingKey must be a non-empty string');
        }

        // Validate segment keys if provided
        if (isset($data['segmentKeys'])) {
            if (!is_array($data['segmentKeys'])) {
                throw new JamboJetValidationException('segmentKeys must be an array');
            }

            foreach ($data['segmentKeys'] as $segmentKey) {
                if (!is_string($segmentKey) || empty(trim($segmentKey))) {
                    throw new JamboJetValidationException('Each segmentKey must be a non-empty string');
                }
            }
        }

        // Validate passenger keys if provided
        if (isset($data['passengerKeys'])) {
            if (!is_array($data['passengerKeys'])) {
                throw new JamboJetValidationException('passengerKeys must be an array');
            }

            foreach ($data['passengerKeys'] as $passengerKey) {
                if (!is_string($passengerKey) || empty(trim($passengerKey))) {
                    throw new JamboJetValidationException('Each passengerKey must be a non-empty string');
                }
            }
        }

        // Validate validation rules if provided
        if (isset($data['validationRules']) && !is_array($data['validationRules'])) {
            throw new JamboJetValidationException('validationRules must be an array');
        }
    }
}
