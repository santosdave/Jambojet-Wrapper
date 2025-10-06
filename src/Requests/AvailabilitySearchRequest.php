<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Availability Search Request
 * 
 * Handles flight availability search requests for NSK API v4
 * Endpoint: POST /api/nsk/v4/availability/search
 * 
 * @package SantosDave\JamboJet\Requests
 */
class AvailabilitySearchRequest extends BaseRequest
{
    /**
     * Create new availability search request
     * 
     * @param array $passengers Required: PassengerTypeCriteria with passenger types and counts
     * @param array $criteria Required: AvailabilityByTrip[] with trip search criteria
     * @param array|null $codes Optional: AvailabilityCodeCriteria for promo codes, etc.
     * @param array|null $fareFilters Optional: AvailabilityFareCriteria for fare filtering
     * @param string|null $taxesAndFees Optional: TaxesAndFeesRollupMode (None|Taxes|TaxesAndFees)
     */
    public function __construct(
        public array $passengers,
        public array $criteria,
        public ?array $codes = null,
        public ?array $fareFilters = null,
        public ?string $taxesAndFees = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'passengers' => $this->passengers,
            'criteria' => $this->criteria,
            'codes' => $this->codes,
            'fareFilters' => $this->fareFilters,
            'taxesAndFees' => $this->taxesAndFees,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    public function validate(): void
    {
        $data = $this->toArray();

        // Validate required top-level fields
        $this->validateRequired($data, ['passengers', 'criteria']);

        // Validate passengers structure
        $this->validatePassengers($data['passengers']);

        // Validate criteria structure
        $this->validateCriteria($data['criteria']);

        // Validate optional fields
        if ($this->taxesAndFees) {
            $this->validateTaxesAndFeesMode($this->taxesAndFees);
        }
    }

    /**
     * Validate passengers structure
     * 
     * @param array $passengers Passenger criteria
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePassengers(array $passengers): void
    {
        // Validate passengers has required 'types' field
        $this->validateRequired($passengers, ['types']);

        // Validate each passenger type
        foreach ($passengers['types'] as $index => $passengerType) {
            $this->validateRequired($passengerType, ['type', 'count']);

            // Validate passenger type format
            $this->validateFormats($passengerType, ['type' => 'passenger_type']);

            // Validate count is positive integer
            if (!is_int($passengerType['count']) || $passengerType['count'] < 1) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Invalid passenger count at index {$index}. Must be positive integer.",
                    400
                );
            }
        }

        // Validate resident country if provided
        if (isset($passengers['residentCountry'])) {
            $this->validateFormats($passengers, ['residentCountry' => 'country_code']);
        }
    }

    /**
     * Validate criteria structure
     * 
     * @param array $criteria Trip criteria array
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateCriteria(array $criteria): void
    {
        if (empty($criteria)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Criteria array cannot be empty',
                400
            );
        }

        foreach ($criteria as $index => $trip) {
            // Each trip must have stations and dates
            $this->validateRequired($trip, ['stations', 'dates']);

            // Validate stations structure
            if (isset($trip['stations'])) {
                $this->validateStations($trip['stations'], $index);
            }

            // Validate dates structure
            if (isset($trip['dates'])) {
                $this->validateDates($trip['dates'], $index);
            }
        }
    }

    /**
     * Validate station criteria
     * 
     * @param array $stations Station criteria
     * @param int $tripIndex Trip index for error reporting
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateStations(array $stations, int $tripIndex): void
    {
        $this->validateRequired($stations, ['departureStation', 'arrivalStation']);

        $this->validateFormats($stations, [
            'departureStation' => 'airport_code',
            'arrivalStation' => 'airport_code'
        ]);

        if ($stations['departureStation'] === $stations['arrivalStation']) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Departure and arrival stations cannot be the same for trip {$tripIndex}",
                400
            );
        }
    }

    /**
     * Validate date criteria
     * 
     * @param array $dates Date criteria
     * @param int $tripIndex Trip index for error reporting
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateDates(array $dates, int $tripIndex): void
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
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "End date must be after begin date for trip {$tripIndex}",
                    400
                );
            }
        }

        // Validate begin date is not in the past
        $beginDate = new \DateTime($dates['beginDate']);
        $now = new \DateTime();

        if ($beginDate < $now) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Begin date cannot be in the past for trip {$tripIndex}",
                400
            );
        }
    }

    /**
     * Validate taxes and fees mode
     * 
     * @param string $mode Taxes and fees mode
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateTaxesAndFeesMode(string $mode): void
    {
        $validModes = ['None', 'Taxes', 'TaxesAndFees'];

        if (!in_array($mode, $validModes)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Invalid taxes and fees mode. Expected one of: " . implode(', ', $validModes),
                400
            );
        }
    }

    /**
     * Create simple availability search for basic use cases
     * 
     * @param string $origin Departure airport code (3-letter IATA)
     * @param string $destination Arrival airport code (3-letter IATA)
     * @param string $departureDate Departure date (Y-m-d format)
     * @param array $passengers Passenger counts ['ADT' => 1, 'CHD' => 0, 'INF' => 0]
     * @param string|null $returnDate Optional return date for round trip
     * @return self
     */
    public static function createSimple(
        string $origin,
        string $destination,
        string $departureDate,
        array $passengers = ['ADT' => 1],
        ?string $returnDate = null
    ): self {
        // Convert passengers array to NSK format
        $passengerTypes = [];
        foreach ($passengers as $type => $count) {
            if ($count > 0) {
                $passengerTypes[] = [
                    'type' => $type,
                    'count' => $count
                ];
            }
        }

        // Build criteria
        $criteria = [
            [
                'stations' => [
                    'departureStation' => strtoupper($origin),
                    'arrivalStation' => strtoupper($destination)
                ],
                'dates' => [
                    'beginDate' => $departureDate . 'T00:00:00'
                ]
            ]
        ];

        // Add return trip if provided
        if ($returnDate) {
            $criteria[] = [
                'stations' => [
                    'departureStation' => strtoupper($destination),
                    'arrivalStation' => strtoupper($origin)
                ],
                'dates' => [
                    'beginDate' => $returnDate . 'T00:00:00'
                ]
            ];
        }

        return new self(
            passengers: ['types' => $passengerTypes],
            criteria: $criteria,
            taxesAndFees: 'TaxesAndFees'
        );
    }
}
