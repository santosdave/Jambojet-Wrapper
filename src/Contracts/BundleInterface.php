<?php

namespace SantosDave\JamboJet\Contracts;

interface BundleInterface
{
    /**
     * Get bundle and SSR availability for booking in state
     * POST /api/nsk/v1/booking/bundle/availability
     */
    public function getBundleAvailability(array $availabilityCriteria = []): array;

    /**
     * Add bundle to booking in state
     * POST /api/nsk/v1/booking/bundle/add
     */
    public function addBundle(array $bundleData): array;

    /**
     * Remove bundle from booking
     * DELETE /api/nsk/v1/booking/bundle/{bundleKey}
     */
    public function removeBundle(string $bundleKey): array;

    /**
     * Get current bundles in booking
     * GET /api/nsk/v1/booking/bundles
     */
    public function getBundles(): array;

    /**
     * Update bundle in booking
     * PUT /api/nsk/v1/booking/bundle/{bundleKey}
     */
    public function updateBundle(string $bundleKey, array $updateData): array;

    /**
     * Get bundle configuration details
     * GET /api/nsk/v1/resources/bundles/{bundleCode}
     */
    public function getBundleConfiguration(string $bundleCode): array;

    /**
     * Get bundle pricing for specific criteria
     * POST /api/nsk/v1/booking/bundle/pricing
     */
    public function getBundlePricing(array $pricingCriteria): array;

    /**
     * Validate bundle compatibility with booking
     * POST /api/nsk/v1/booking/bundle/validate
     */
    public function validateBundle(array $bundleValidationData): array;
}
