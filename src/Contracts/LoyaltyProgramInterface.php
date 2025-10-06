<?php

namespace SantosDave\JamboJet\Contracts;

interface LoyaltyProgramInterface
{
    /**
     * Get loyalty programs for booking in state
     * GET /api/nsk/v1/booking/loyaltyPrograms
     */
    public function getLoyaltyPrograms(): array;

    /**
     * Add loyalty program to booking in state
     * POST /api/nsk/v1/booking/loyaltyPrograms
     */
    public function addLoyaltyProgram(array $loyaltyProgramData): array;

    /**
     * Update loyalty program in booking
     * PUT /api/nsk/v1/booking/loyaltyPrograms/{loyaltyProgramKey}
     */
    public function updateLoyaltyProgram(string $loyaltyProgramKey, array $updateData): array;

    /**
     * Remove loyalty program from booking
     * DELETE /api/nsk/v1/booking/loyaltyPrograms/{loyaltyProgramKey}
     */
    public function removeLoyaltyProgram(string $loyaltyProgramKey): array;

    /**
     * Get available loyalty program types
     * GET /api/nsk/v1/resources/loyaltyPrograms
     */
    public function getAvailableLoyaltyPrograms(): array;

    /**
     * Get loyalty program balance/status
     * GET /api/nsk/v1/user/loyaltyPrograms/{programCode}/balance
     */
    public function getLoyaltyProgramBalance(string $programCode, string $membershipNumber): array;

    /**
     * Validate loyalty program membership
     * POST /api/nsk/v1/loyaltyPrograms/validate
     */
    public function validateMembership(array $membershipData): array;

    /**
     * Get loyalty program benefits for flight
     * GET /api/nsk/v1/loyaltyPrograms/{programCode}/benefits
     */
    public function getProgramBenefits(string $programCode, array $criteria = []): array;
}
