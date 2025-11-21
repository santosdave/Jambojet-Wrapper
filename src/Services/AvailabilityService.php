<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\AvailabilityInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Availability Service for JamboJet NSK API
 * 
 * Handles all flight search, availability checking, and fare operations
 * Base endpoints: /api/nsk/v{version}/availability
 * 
 * Supported endpoints:
 * - POST /api/nsk/v4/availability/search - Full availability search (LATEST)
 * - POST /api/nsk/v4/availability/search/simple - Simple search v4 (NEW)
 * - POST /api/nsk/v3/availability/search/simple - Simple search v3
 * - POST /api/nsk/v3/availability/lowfare - Low fare search (FIXED)
 * - POST /api/nsk/v3/availability/lowfare/simple - Simple low fare (FIXED)
 * - POST /api/nsk/v2/availability/search/ssr - Availability with SSR (NEW)
 * - GET /api/nsk/v1/fareRules/{fareAvailabilityKey} - Get fare rules
 * 
 * @package SantosDave\JamboJet\Services
 */
class AvailabilityService implements AvailabilityInterface
{
    use HandlesApiRequests, ValidatesRequests;

    /**
     * Search for flight availability (Latest Version - Recommended)
     * 
     * POST /api/nsk/v4/availability/search
     * Full availability search with complete control over configuration
     * 
     * @param array $searchCriteria Complete availability search request
     * @return array Availability search results
     * @throws JamboJetApiException
     */
    public function search(array $searchCriteria): array
    {
        $this->validateAvailabilityRequest($searchCriteria);

        try {
            return $this->post('api/nsk/v4/availability/search', $searchCriteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Availability search failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Simple flight availability search (FIXED - Now supports v3 and v4)
     * 
     * POST /api/nsk/v4/availability/search/simple (NEW - Recommended)
     * POST /api/nsk/v3/availability/search/simple (Legacy support)
     * Simple search with common criteria, uses default settings for the rest
     * 
     * @param array $searchCriteria Simple search criteria
     * @param int $version API version (3 or 4, default: 4)
     * @return array Availability search results
     * @throws JamboJetApiException
     */
    public function searchSimple(array $searchCriteria, int $version = 4): array
    {
        $this->validateSimpleRequest($searchCriteria);
        $this->validateApiVersion($version, [3, 4]);

        try {
            return $this->post("api/nsk/v{$version}/availability/search/simple", $searchCriteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Simple availability search failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Low fare availability search (FIXED - Now uses v3)
     * 
     * POST /api/nsk/v3/availability/lowfare (UPDATED from v2)
     * Search for lowest fares with full configuration control
     * 
     * @param array $request Low fare availability request
     * @param int $version API version (2 or 3, default: 3)
     * @return array Low fare search results
     * @throws JamboJetApiException
     */
    public function getLowestFares(array $request, int $version = 3): array
    {
        $this->validateLowFareRequest($request);
        $this->validateApiVersion($version, [2, 3]);

        try {
            return $this->post("api/nsk/v{$version}/availability/lowfare", $request);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Low fare search failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Simple low fare availability search (FIXED - Now uses v3)
     * 
     * POST /api/nsk/v3/availability/lowfare/simple (UPDATED from v2)
     * Simple low fare search with basic criteria
     * 
     * @param array $searchCriteria Simple low fare search criteria
     * @param int $version API version (2 or 3, default: 3)
     * @return array Low fare search results
     * @throws JamboJetApiException
     */
    public function searchLowFareSimple(array $searchCriteria, int $version = 3): array
    {
        $this->validateLowFareSimpleRequest($searchCriteria);
        $this->validateApiVersion($version, [2, 3]);

        try {
            return $this->post("api/nsk/v{$version}/availability/lowfare/simple", $searchCriteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Simple low fare search failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

     // =================================================================
    // AVAILABILITY WITH SSR (NEW)
    // =================================================================

    /**
     * Availability search with SSR (NEW METHOD)
     * 
     * POST /api/nsk/v2/availability/search/ssr
     * Search for availability with Special Service Requests included
     * 
     * @param array $searchRequest Availability with SSR search request
     * @return array Availability with SSR results
     * @throws JamboJetApiException
     */
    public function searchWithSsr(array $searchRequest): array
    {
        $this->validateAvailabilityWithSsrRequest($searchRequest);

        try {
            return $this->post('api/nsk/v2/availability/search/ssr', $searchRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Availability with SSR search failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // FARE RULES
    // =================================================================


    /**
     * Get fare rules for a specific fare
     * 
     * GET /api/nsk/v1/fareRules/{fareAvailabilityKey}
     * Retrieves detailed fare rules and restrictions
     * 
     * @param string $fareAvailabilityKey Fare availability key
     * @return array Fare rules details
     * @throws JamboJetApiException
     */
    public function getFareRules(string $fareAvailabilityKey): array
    {
        $this->validateFareAvailabilityKey($fareAvailabilityKey);

        try {
            return $this->get("api/nsk/v1/fareRules/{$fareAvailabilityKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare rules: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all fare rules from current booking
     * 
     * Retrieves all fare rules from current booking in state
     * 
     * @return array Collection of fare rules
     * @throws JamboJetApiException
     */
    public function getBookingFareRules(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/fareRules');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking fare rules: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fare rule by specific fare key from booking
     * 
     * GET /api/nsk/v1/booking/fareRules/fare/{fareKey}
     * Retrieves specific fare rule from current booking based on fare key
     * 
     * @param string $fareKey Unique fare key
     * @return array Fare rule data
     * @throws JamboJetApiException
     */
    public function getBookingFareRuleByFareKey(string $fareKey): array
    {
        if (empty($fareKey)) {
            throw new JamboJetValidationException('Fare key is required');
        }

        try {
            return $this->get("api/nsk/v1/booking/fareRules/fare/{$fareKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare rule by fare key: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get fare rules by journey key from booking
     * 
     * GET /api/nsk/v1/booking/fareRules/journey/{journeyKey}
     * Retrieves all fare rules from current booking for specific journey
     * 
     * @param string $journeyKey Unique journey key
     * @return array Collection of fare rules for journey
     * @throws JamboJetApiException
     */
    public function getBookingFareRulesByJourneyKey(string $journeyKey): array
    {
        if (empty($journeyKey)) {
            throw new JamboJetValidationException('Journey key is required');
        }

        try {
            return $this->get("api/nsk/v1/booking/fareRules/journey/{$journeyKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get fare rules by journey key: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Quick search - convenience wrapper for simple search
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $departureDate Departure date (YYYY-MM-DD)
     * @param array $passengers Passenger types and counts
     * @param string|null $returnDate Optional return date for round trip
     * @return array Search results
     * @throws JamboJetApiException
     */
    public function quickSearch(
        string $origin,
        string $destination,
        string $departureDate,
        array $passengers,
        ?string $returnDate = null
    ): array {
        $searchCriteria = [
            'origin' => $origin,
            'destination' => $destination,
            'beginDate' => $departureDate,
            'passengers' => $passengers,
        ];

        if ($returnDate) {
            $searchCriteria['endDate'] = $returnDate;
        }

        return $this->searchSimple($searchCriteria);
    }

    /**
     * Search with flexible dates - convenience wrapper for low fare search
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $startDate Start date for flexible search
     * @param string $endDate End date for flexible search
     * @param array $passengers Passenger types and counts
     * @return array Low fare results with date options
     * @throws JamboJetApiException
     */
    public function searchFlexibleDates(
        string $origin,
        string $destination,
        string $startDate,
        string $endDate,
        array $passengers
    ): array {
        $searchCriteria = [
            'passengers' => $passengers,
            'criteria' => [
                [
                    'originStationCodes' => [$origin],
                    'destinationStationCodes' => [$destination],
                    'beginDate' => $startDate,
                    'endDate' => $endDate
                ]
            ]
        ];

        return $this->searchLowFareSimple($searchCriteria);
    }

    /**
     * Get fare details (Legacy support method)
     * 
     * @param array $fareRequest Fare request parameters
     * @return array Fare details
     * @throws JamboJetApiException
     */
    public function getFares(array $fareRequest): array
    {
        // Redirect to appropriate search method based on request
        if (isset($fareRequest['lowFare']) && $fareRequest['lowFare']) {
            return $this->getLowestFares($fareRequest);
        }

        return $this->search($fareRequest);
    }



    /**
     * Build passenger criteria from simple array
     */
    protected function buildPassengerCriteria(array $passengers): array
    {
        $criteria = [];

        // Default passenger types
        $types = [
            'adults' => 'ADT',
            'children' => 'CHD',
            'infants' => 'INF'
        ];

        foreach ($types as $key => $code) {
            if (isset($passengers[$key]) && $passengers[$key] > 0) {
                $criteria[] = [
                    'passengerTypeCode' => $code,
                    'passengerCount' => (int) $passengers[$key]
                ];
            }
        }

        // If no passengers specified, default to 1 adult
        if (empty($criteria)) {
            $criteria[] = [
                'passengerTypeCode' => 'ADT',
                'passengerCount' => 1
            ];
        }

        return $criteria;
    }

    // =================================================================
    // VALIDATION METHODS - UPDATED AND COMPREHENSIVE
    // =================================================================

    /**
     * Validate availability search request (POST /api/nsk/v4/availability/search)
     * Based on Navitaire.DotRez.Nsk.Booking.Models.Availability.AvailabilityRequest
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    private function validateAvailabilityRequest(array $data): void
    {
        // Validate required top-level fields
        $this->validateRequired($data, ['passengers', 'criteria']);

        // Validate passengers structure (PassengerTypeCriteria)
        $this->validatePassengersCriteria($data['passengers']);

        // Validate criteria structure (AvailabilityByTrip[])
        $this->validateAvailabilityCriteria($data['criteria']);

        // Validate optional fields if present
        if (isset($data['codes'])) {
            $this->validateAvailabilityCodeCriteria($data['codes']);
        }

        if (isset($data['fareFilters'])) {
            $this->validateAvailabilityFareCriteria($data['fareFilters']);
        }

        if (isset($data['taxesAndFees'])) {
            $this->validateTaxesAndFeesRollupMode($data['taxesAndFees']);
        }
    }

    /* Validate simple availability request (POST /api/nsk/v3/availability/search/simple)
     * Based on Navitaire.DotRez.Nsk.Booking.Models.Availability.v2.AvailabilitySimpleRequestv2
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    private function validateSimpleRequest(array $data): void
    {
        // Validate required fields for simple request
        $this->validateRequired($data, ['origin', 'destination', 'beginDate', 'passengers']);

        // Validate airport codes
        $this->validateFormats($data, [
            'origin' => 'airport_code',
            'destination' => 'airport_code'
        ]);

        // Origin and destination cannot be the same
        if ($data['origin'] === $data['destination']) {
            throw new JamboJetValidationException(
                'Origin and destination cannot be the same',
                400
            );
        }

        // Validate dates
        $this->validateFormats($data, ['beginDate' => 'datetime']);

        if (isset($data['endDate'])) {
            $this->validateFormats($data, ['endDate' => 'datetime']);

            // Ensure return date is after departure date
            $beginDate = new \DateTime($data['beginDate']);
            $endDate = new \DateTime($data['endDate']);

            if ($endDate < $beginDate) {
                throw new JamboJetValidationException(
                    'Return date must be after departure date',
                    400
                );
            }
        }

        // Validate departure date is not in the past
        $beginDate = new \DateTime($data['beginDate']);
        $now = new \DateTime();

        if ($beginDate < $now) {
            throw new JamboJetValidationException(
                'Departure date cannot be in the past',
                400
            );
        }

        // Validate passengers array (PassengerSearchCriteria[])
        $this->validatePassengerSearchCriteria($data['passengers']);

        // Validate optional fields
        if (isset($data['promotionCode'])) {
            $this->validateStringLengths($data, ['promotionCode' => ['max' => 8]]);
        }

        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        if (isset($data['loyaltyFilter'])) {
            $this->validateLoyaltyFilter($data['loyaltyFilter']);
        }
    }

    /**
     * Validate low fare availability request (POST /api/nsk/v2/availability/lowfare)
     * Based on Navitaire.DotRez.Nsk.Booking.Models.Availability.LowFareAvailabilityRequest
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    private function validateLowFareRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['passengers', 'criteria']);

        // Validate passengers structure
        $this->validatePassengersCriteria($data['passengers']);

        // Validate criteria structure (LowFareAvailabilityCriteria[])
        $this->validateLowFareAvailabilityCriteria($data['criteria']);

        // Validate optional boolean flags
        if (isset($data['bypassCache'])) {
            if (!is_bool($data['bypassCache'])) {
                throw new JamboJetValidationException(
                    'bypassCache must be a boolean value',
                    400
                );
            }
        }

        if (isset($data['getAllDetails'])) {
            if (!is_bool($data['getAllDetails'])) {
                throw new JamboJetValidationException(
                    'getAllDetails must be a boolean value',
                    400
                );
            }
        }

        if (isset($data['includeTaxesAndFees'])) {
            if (!is_bool($data['includeTaxesAndFees'])) {
                throw new JamboJetValidationException(
                    'includeTaxesAndFees must be a boolean value',
                    400
                );
            }
        }

        // Validate optional complex structures
        if (isset($data['codes'])) {
            $this->validateLowFareAvailabilityCodeCriteria($data['codes']);
        }

        if (isset($data['filters'])) {
            $this->validateLowFareAvailabilityFilterCriteria($data['filters']);
        }
    }

    /**
     * Validate simple low fare request
     * 
     * @param array $data Request data
     * @throws JamboJetValidationException
     */
    private function validateLowFareSimpleRequest(array $data): void
    {
        // Similar to regular simple request but for low fare endpoint
        $this->validateSimpleRequest($data);

        // Additional validations specific to low fare simple requests
        if (isset($data['flexibleDays'])) {
            $this->validateNumericRanges($data, ['flexibleDays' => ['min' => 0, 'max' => 7]]);
        }
    }

    /**
     * Validate fare availability key
     * 
     * @param string $fareAvailabilityKey Fare availability key
     * @throws JamboJetValidationException
     */
    private function validateFareAvailabilityKey(string $fareAvailabilityKey): void
    {
        if (empty(trim($fareAvailabilityKey))) {
            throw new JamboJetValidationException(
                'Fare availability key is required',
                400
            );
        }

        // Fare availability keys are typically long alphanumeric strings
        if (strlen($fareAvailabilityKey) < 10) {
            throw new JamboJetValidationException(
                'Invalid fare availability key format',
                400
            );
        }
    }

    // =================================================================
    // HELPER VALIDATION METHODS FOR COMPLEX STRUCTURES
    // =================================================================

    /**
     * Validate passengers criteria structure
     * 
     * @param array $passengers Passengers criteria
     * @throws JamboJetValidationException
     */
    private function validatePassengersCriteria(array $passengers): void
    {
        // Validate required 'types' field
        $this->validateRequired($passengers, ['types']);

        if (!is_array($passengers['types']) || empty($passengers['types'])) {
            throw new JamboJetValidationException(
                'Passenger types must be a non-empty array',
                400
            );
        }

        // Validate each passenger type
        foreach ($passengers['types'] as $index => $passengerType) {
            $this->validateRequired($passengerType, ['type', 'count']);

            // Validate passenger type format
            $this->validateFormats($passengerType, ['type' => 'passenger_type']);

            // Validate count is positive integer
            $this->validateNumericRanges($passengerType, ['count' => ['min' => 1, 'max' => 9]]);

            if (!is_int($passengerType['count'])) {
                throw new JamboJetValidationException(
                    "Passenger count at index {$index} must be an integer",
                    400
                );
            }

            // Validate discount code if provided
            if (isset($passengerType['discountCode'])) {
                $this->validateStringLengths($passengerType, ['discountCode' => ['max' => 4]]);
            }
        }

        // Validate resident country if provided
        if (isset($passengers['residentCountry'])) {
            $this->validateFormats($passengers, ['residentCountry' => 'country_code']);
        }
    }

    /**
     * Validate passenger search criteria for simple requests
     * 
     * @param array $passengers Passenger search criteria
     * @throws JamboJetValidationException
     */
    private function validatePassengerSearchCriteria(array $passengers): void
    {
        if (!is_array($passengers) || empty($passengers)) {
            throw new JamboJetValidationException(
                'Passengers must be a non-empty array',
                400
            );
        }

        foreach ($passengers as $index => $passenger) {
            $this->validateRequired($passenger, ['type', 'count']);

            // Validate passenger type format
            $this->validateFormats($passenger, ['type' => 'passenger_type']);

            // Validate count
            $this->validateNumericRanges($passenger, ['count' => ['min' => 1, 'max' => 9]]);

            if (!is_int($passenger['count'])) {
                throw new JamboJetValidationException(
                    "Passenger count at index {$index} must be an integer",
                    400
                );
            }
        }
    }

    /**
     * Validate availability criteria array
     * 
     * @param array $criteria Availability criteria
     * @throws JamboJetValidationException
     */
    private function validateAvailabilityCriteria(array $criteria): void
    {
        if (empty($criteria)) {
            throw new JamboJetValidationException(
                'Criteria array cannot be empty',
                400
            );
        }

        foreach ($criteria as $index => $trip) {
            // Each trip must have stations and dates
            $this->validateRequired($trip, ['stations', 'dates']);

            // Validate stations structure
            if (isset($trip['stations'])) {
                $this->validateStationsCriteria($trip['stations'], $index);
            }

            // Validate dates structure
            if (isset($trip['dates'])) {
                $this->validateDatesCriteria($trip['dates'], $index);
            }

            // Validate optional fields
            if (isset($trip['lowFarePrice'])) {
                $this->validateFormats($trip, ['lowFarePrice' => 'positive_number']);
            }

            if (isset($trip['ssrCollectionsMode'])) {
                $this->validateSSRCollectionsMode($trip['ssrCollectionsMode']);
            }

            if (isset($trip['type'])) {
                $this->validateTripType($trip['type']);
            }
        }
    }

    /**
     * Validate stations criteria
     * 
     * @param array $stations Stations criteria
     * @param int $tripIndex Trip index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateStationsCriteria(array $stations, int $tripIndex): void
    {
        $this->validateRequired($stations, ['departureStation', 'arrivalStation']);

        $this->validateFormats($stations, [
            'departureStation' => 'airport_code',
            'arrivalStation' => 'airport_code'
        ]);

        if ($stations['departureStation'] === $stations['arrivalStation']) {
            throw new JamboJetValidationException(
                "Departure and arrival stations cannot be the same for trip {$tripIndex}",
                400
            );
        }
    }

    /**
     * Validate dates criteria
     * 
     * @param array $dates Dates criteria
     * @param int $tripIndex Trip index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateDatesCriteria(array $dates, int $tripIndex): void
    {
        $this->validateRequired($dates, ['beginDate']);

        $this->validateFormats($dates, ['beginDate' => 'datetime']);

        // Validate end date if provided
        if (isset($dates['endDate'])) {
            $this->validateFormats($dates, ['endDate' => 'datetime']);

            // Ensure end date is after begin date
            $beginDate = new \DateTime($dates['beginDate']);
            $endDate = new \DateTime($dates['endDate']);

            if ($endDate < $beginDate) {
                throw new JamboJetValidationException(
                    "End date must be after begin date for trip {$tripIndex}",
                    400
                );
            }
        }

        // Validate begin date is not in the past
        $beginDate = new \DateTime($dates['beginDate']);
        $now = new \DateTime();

        if ($beginDate < $now) {
            throw new JamboJetValidationException(
                "Begin date cannot be in the past for trip {$tripIndex}",
                400
            );
        }

        // Validate time intervals if provided
        if (isset($dates['beginTimeInterval'])) {
            $this->validateTimeInterval($dates['beginTimeInterval']);
        }

        if (isset($dates['endTimeInterval'])) {
            $this->validateTimeInterval($dates['endTimeInterval']);
        }

        // Validate days of week if provided
        if (isset($dates['daysOfWeek']) && is_array($dates['daysOfWeek'])) {
            $this->validateDaysOfWeek($dates['daysOfWeek']);
        }
    }

    /**
     * Validate low fare availability criteria
     * 
     * @param array $criteria Low fare criteria
     * @throws JamboJetValidationException
     */
    private function validateLowFareAvailabilityCriteria(array $criteria): void
    {
        if (empty($criteria)) {
            throw new JamboJetValidationException(
                'Low fare criteria array cannot be empty',
                400
            );
        }

        foreach ($criteria as $index => $trip) {
            // Required fields for low fare criteria
            $this->validateRequired($trip, ['origin', 'destination', 'departureDate']);

            // Validate airport codes
            $this->validateFormats($trip, [
                'origin' => 'airport_code',
                'destination' => 'airport_code'
            ]);

            // Validate departure date
            $this->validateFormats($trip, ['departureDate' => 'date']);

            // Origin and destination cannot be the same
            if ($trip['origin'] === $trip['destination']) {
                throw new JamboJetValidationException(
                    "Origin and destination cannot be the same for trip {$index}",
                    400
                );
            }

            // Validate optional fields
            if (isset($trip['returnDate'])) {
                $this->validateFormats($trip, ['returnDate' => 'date']);

                $departureDate = new \DateTime($trip['departureDate']);
                $returnDate = new \DateTime($trip['returnDate']);

                if ($returnDate < $departureDate) {
                    throw new JamboJetValidationException(
                        "Return date must be after departure date for trip {$index}",
                        400
                    );
                }
            }

            if (isset($trip['flexibleDays'])) {
                $this->validateNumericRanges($trip, ['flexibleDays' => ['min' => 0, 'max' => 7]]);
            }

            if (isset($trip['minLengthOfStay']) && isset($trip['maxLengthOfStay'])) {
                if ($trip['minLengthOfStay'] > $trip['maxLengthOfStay']) {
                    throw new JamboJetValidationException(
                        "Minimum length of stay cannot be greater than maximum for trip {$index}",
                        400
                    );
                }
            }
        }
    }

    // =================================================================
    // ADDITIONAL VALIDATION HELPERS
    // =================================================================

    /**
     * Validate availability code criteria
     */
    private function validateAvailabilityCodeCriteria(array $codes): void
    {
        if (isset($codes['promotionCode'])) {
            $this->validateStringLengths($codes, ['promotionCode' => ['max' => 8]]);
        }

        if (isset($codes['currencyCode'])) {
            $this->validateFormats($codes, ['currencyCode' => 'currency_code']);
        }
    }

    /**
     * Validate availability fare criteria
     */
    private function validateAvailabilityFareCriteria(array $fareFilters): void
    {
        // Fare filters validation would be implemented based on specific requirements
        // This is a placeholder for complex fare filtering validation
    }

    /**
     * Validate taxes and fees rollup mode
     */
    private function validateTaxesAndFeesRollupMode(string $mode): void
    {
        $validModes = ['None', 'Taxes', 'TaxesAndFees'];

        if (!in_array($mode, $validModes)) {
            throw new JamboJetValidationException(
                'Invalid taxes and fees mode. Expected one of: ' . implode(', ', $validModes),
                400
            );
        }
    }

    /**
     * Validate loyalty filter
     */
    private function validateLoyaltyFilter(string $filter): void
    {
        $validFilters = ['MonetaryOnly', 'PointsOnly', 'PointsAndMonetary', 'PreserveCurrent'];

        if (!in_array($filter, $validFilters)) {
            throw new JamboJetValidationException(
                'Invalid loyalty filter. Expected one of: ' . implode(', ', $validFilters),
                400
            );
        }
    }

    /**
     * Validate SSR collections mode
     */
    private function validateSSRCollectionsMode(string $mode): void
    {
        $validModes = ['None', 'Leg'];

        if (!in_array($mode, $validModes)) {
            throw new JamboJetValidationException(
                'Invalid SSR collections mode. Expected one of: ' . implode(', ', $validModes),
                400
            );
        }
    }

    /**
     * Validate trip type
     */
    private function validateTripType(string $type): void
    {
        $validTypes = ['OneWay', 'RoundTrip', 'MultiCity', 'OpenJaw'];

        if (!in_array($type, $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid trip type. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }
    }

    /**
     * Validate time interval format
     */
    private function validateTimeInterval(string $interval): void
    {
        // Time intervals are typically in HH:MM format
        if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $interval)) {
            throw new JamboJetValidationException(
                'Invalid time interval format. Expected HH:MM format',
                400
            );
        }
    }

    /**
     * Validate days of week array
     */
    private function validateDaysOfWeek(array $days): void
    {
        $validDays = ['None', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        foreach ($days as $day) {
            if (!in_array($day, $validDays)) {
                throw new JamboJetValidationException(
                    'Invalid day of week. Expected one of: ' . implode(', ', $validDays),
                    400
                );
            }
        }
    }

    /**
     * Validate low fare availability code criteria
     */
    private function validateLowFareAvailabilityCodeCriteria(array $codes): void
    {
        $this->validateAvailabilityCodeCriteria($codes);

        // Additional validations specific to low fare codes
        if (isset($codes['corporateCodes']) && is_array($codes['corporateCodes'])) {
            foreach ($codes['corporateCodes'] as $index => $code) {
                $this->validateStringLengths(['code' => $code], ['code' => ['max' => 20]]);
            }
        }
    }

    /**
     * Validate low fare availability filter criteria
     */
    private function validateLowFareAvailabilityFilterCriteria(array $filters): void
    {
        if (isset($filters['priceRange'])) {
            $priceRange = $filters['priceRange'];

            if (isset($priceRange['minPrice']) && isset($priceRange['maxPrice'])) {
                if ($priceRange['minPrice'] > $priceRange['maxPrice']) {
                    throw new JamboJetValidationException(
                        'Minimum price cannot be greater than maximum price',
                        400
                    );
                }
            }

            if (isset($priceRange['minPrice'])) {
                $this->validateFormats($priceRange, ['minPrice' => 'non_negative_number']);
            }

            if (isset($priceRange['maxPrice'])) {
                $this->validateFormats($priceRange, ['maxPrice' => 'positive_number']);
            }
        }

        if (isset($filters['connections'])) {
            $validConnections = ['NonStop', 'OneStop', 'TwoStop', 'Any'];
            if (!in_array($filters['connections'], $validConnections)) {
                throw new JamboJetValidationException(
                    'Invalid connection filter. Expected one of: ' . implode(', ', $validConnections),
                    400
                );
            }
        }

        if (isset($filters['carrierCodes']) && is_array($filters['carrierCodes'])) {
            foreach ($filters['carrierCodes'] as $index => $carrierCode) {
                if (!preg_match('/^[A-Z]{2,3}$/', $carrierCode)) {
                    throw new JamboJetValidationException(
                        "Invalid carrier code at index {$index}. Expected 2-3 letter airline code",
                        400
                    );
                }
            }
        }
    }

    /**
     * Get supported search types
     * 
     * @return array Available search methods and their descriptions
     */
    public function getSupportedSearchTypes(): array
    {
        return [
            'full_search' => [
                'method' => 'search',
                'description' => 'Full availability search with complete control',
                'endpoint' => '/api/nsk/v4/availability/search',
                'version' => 'v4'
            ],
            'simple_search_v4' => [
                'method' => 'searchSimple',
                'description' => 'Simple availability search v4 (recommended)',
                'endpoint' => '/api/nsk/v4/availability/search/simple',
                'version' => 'v4'
            ],
            'simple_search_v3' => [
                'method' => 'searchSimple',
                'description' => 'Simple availability search v3',
                'endpoint' => '/api/nsk/v3/availability/search/simple',
                'version' => 'v3'
            ],
            'low_fare_search_v3' => [
                'method' => 'getLowestFares',
                'description' => 'Low fare availability search v3 (updated)',
                'endpoint' => '/api/nsk/v3/availability/lowfare',
                'version' => 'v3'
            ],
            'low_fare_simple_v3' => [
                'method' => 'searchLowFareSimple',
                'description' => 'Simple low fare search v3 (updated)',
                'endpoint' => '/api/nsk/v3/availability/lowfare/simple',
                'version' => 'v3'
            ],
            'search_with_ssr' => [
                'method' => 'searchWithSsr',
                'description' => 'Availability search with SSR',
                'endpoint' => '/api/nsk/v2/availability/search/ssr',
                'version' => 'v2'
            ],
            'quick_search' => [
                'method' => 'quickSearch',
                'description' => 'Convenience method for simple searches',
                'endpoint' => 'Wrapper (uses simple search)',
                'version' => 'Wrapper'
            ],
            'flexible_dates' => [
                'method' => 'searchFlexibleDates',
                'description' => 'Search with flexible date ranges',
                'endpoint' => 'Wrapper (uses low fare simple)',
                'version' => 'Wrapper'
            ]
        ];
    }
}
