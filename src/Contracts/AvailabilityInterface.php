<?php

namespace SantosDave\JamboJet\Contracts;

interface AvailabilityInterface
{
    /**
     * Search for flight availability
     * POST /api/nsk/v#/availability
     */
    public function search(array $searchCriteria): array;

    /**
     * Get availability by key
     * GET /api/nsk/v#/availability/{availabilityKey}
     */
    public function getByKey(string $availabilityKey): array;

    /**
     * Get fare details
     * POST /api/nsk/v#/availability/fares
     */
    public function getFares(array $fareRequest): array;

    /**
     * Get fare rules
     * POST /api/nsk/v#/availability/farerules
     */
    public function getFareRules(string $fareRuleRequest): array;

    /**
     * Get lowest fares
     * POST /api/nsk/v#/availability/lowestFares
     */
    public function getLowestFares(array $request): array;
}
