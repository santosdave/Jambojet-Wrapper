<?php

namespace SantosDave\JamboJet\Contracts;

interface AvailabilityInterface
{
    /**
     * Full availability search
     * POST /api/nsk/v4/availability/search
     * 
     * @param array $searchCriteria Complete availability search request
     * @return array Availability search results
     */
    public function search(array $searchCriteria): array;

    /**
     * Simple availability search with version support
     * POST /api/nsk/v4/availability/search/simple (default)
     * POST /api/nsk/v3/availability/search/simple (legacy)
     * 
     * @param array $searchCriteria Simple search criteria
     * @param int $version API version (3 or 4, default: 4)
     * @return array Availability search results
     */
    public function searchSimple(array $searchCriteria, int $version = 4): array;

    /**
     * Low fare availability search
     * POST /api/nsk/v3/availability/lowfare (default)
     * POST /api/nsk/v2/availability/lowfare (legacy)
     * 
     * @param array $request Low fare availability request
     * @param int $version API version (2 or 3, default: 3)
     * @return array Low fare search results
     */
    public function getLowestFares(array $request, int $version = 3): array;

    /**
     * Simple low fare availability search
     * POST /api/nsk/v3/availability/lowfare/simple (default)
     * POST /api/nsk/v2/availability/lowfare/simple (legacy)
     * 
     * @param array $searchCriteria Simple low fare search criteria
     * @param int $version API version (2 or 3, default: 3)
     * @return array Low fare search results
     */
    public function searchLowFareSimple(array $searchCriteria, int $version = 3): array;

    /**
     * Availability search with SSR (NEW)
     * POST /api/nsk/v2/availability/search/ssr
     * 
     * @param array $searchRequest Availability with SSR search request
     * @return array Availability with SSR results
     */
    public function searchWithSsr(array $searchRequest): array;


    /**
     * Get fare rules
     * GET /api/nsk/v1/fareRules/{fareAvailabilityKey}
     * 
     * @param string $fareAvailabilityKey Fare availability key
     * @return array Fare rules details
     */
    public function getFareRules(string $fareAvailabilityKey): array;

    /**
     * Quick search convenience method
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $departureDate Departure date
     * @param array $passengers Passenger types and counts
     * @param string|null $returnDate Optional return date
     * @return array Search results
     */
    public function quickSearch(
        string $origin,
        string $destination,
        string $departureDate,
        array $passengers,
        ?string $returnDate = null
    ): array;

    /**
     * Search with flexible dates
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $startDate Start date
     * @param string $endDate End date
     * @param array $passengers Passenger types and counts
     * @return array Low fare results with date options
     */
    public function searchFlexibleDates(
        string $origin,
        string $destination,
        string $startDate,
        string $endDate,
        array $passengers
    ): array;

    /**
     * Get supported search types
     * 
     * @return array Available search methods
     */
    public function getSupportedSearchTypes(): array;
}