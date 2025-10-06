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
}
