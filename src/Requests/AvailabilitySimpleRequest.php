<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Simple Availability Search Request
 * 
 * Handles simple flight availability search requests for NSK API v3
 * Endpoint: POST /api/nsk/v3/availability/search/simple
 * 
 * @package SantosDave\JamboJet\Requests
 */
class AvailabilitySimpleRequest extends BaseRequest
{
    /**
     * Create new simple availability search request
     * 
     * @param string $origin Required: Origin airport code (3-letter IATA)
     * @param string $destination Required: Destination airport code (3-letter IATA) 
     * @param string $beginDate Required: Departure date (ISO 8601 format)
     * @param array $passengers Required: Passenger types and counts
     * @param string|null $endDate Optional: Return date for round trip (ISO 8601 format)
     * @param string|null $promotionCode Optional: Promotion code to apply
     * @param string|null $currencyCode Optional: Currency code for pricing (3-letter ISO)
     * @param string|null $loyaltyFilter Optional: Loyalty fare filter
     * @param bool $searchOriginMacs Optional: Search origin MAC stations
     * @param bool $searchDestinationMacs Optional: Search destination MAC stations
     */
    public function __construct(
        public string $origin,
        public string $destination,
        public string $beginDate,
        public array $passengers,
        public ?string $endDate = null,
        public ?string $promotionCode = null,
        public ?string $currencyCode = null,
        public ?string $loyaltyFilter = null,
        public bool $searchOriginMacs = false,
        public bool $searchDestinationMacs = false
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'origin' => $this->origin,
            'destination' => $this->destination,
            'beginDate' => $this->beginDate,
            'passengers' => $this->passengers,
            'endDate' => $this->endDate,
            'promotionCode' => $this->promotionCode,
            'currencyCode' => $this->currencyCode,
            'loyaltyFilter' => $this->loyaltyFilter,
            'searchOriginMacs' => $this->searchOriginMacs,
            'searchDestinationMacs' => $this->searchDestinationMacs,
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

        // Validate required fields
        $this->validateRequired($data, ['origin', 'destination', 'beginDate', 'passengers']);

        // Validate airport codes
        $this->validateFormats($data, [
            'origin' => 'airport_code',
            'destination' => 'airport_code'
        ]);

        // Origin and destination cannot be the same
        if ($this->origin === $this->destination) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Origin and destination cannot be the same',
                400
            );
        }

        // Validate dates
        $this->validateFormats($data, ['beginDate' => 'datetime']);

        if ($this->endDate) {
            $this->validateFormats($data, ['endDate' => 'datetime']);

            // Ensure return date is after departure date
            $beginDate = new \DateTime($this->beginDate);
            $endDate = new \DateTime($this->endDate);

            if ($endDate < $beginDate) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Return date must be after departure date',
                    400
                );
            }
        }

        // Validate departure date is not in the past
        $beginDate = new \DateTime($this->beginDate);
        $now = new \DateTime();

        if ($beginDate < $now) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Departure date cannot be in the past',
                400
            );
        }

        // Validate passengers
        $this->validatePassengers($this->passengers);

        // Validate optional fields
        if ($this->currencyCode) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        if ($this->promotionCode) {
            if (strlen($this->promotionCode) > 8) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Promotion code cannot exceed 8 characters',
                    400
                );
            }
        }

        if ($this->loyaltyFilter) {
            $this->validateLoyaltyFilter($this->loyaltyFilter);
        }
    }

    /**
     * Validate passengers array
     * 
     * @param array $passengers Passenger data
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePassengers(array $passengers): void
    {
        if (empty($passengers)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'At least one passenger is required',
                400
            );
        }

        foreach ($passengers as $index => $passenger) {
            $this->validateRequired($passenger, ['type', 'count']);

            // Validate passenger type format
            $this->validateFormats($passenger, ['type' => 'passenger_type']);

            // Validate count
            if (!is_int($passenger['count']) || $passenger['count'] < 1) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Invalid passenger count at index {$index}. Must be positive integer.",
                    400
                );
            }

            // Validate discount code if provided
            if (isset($passenger['discountCode'])) {
                if (strlen($passenger['discountCode']) > 4) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "Discount code at passenger index {$index} cannot exceed 4 characters",
                        400
                    );
                }
            }
        }
    }

    /**
     * Validate loyalty filter value
     * 
     * @param string $filter Loyalty filter
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateLoyaltyFilter(string $filter): void
    {
        $validFilters = ['MonetaryOnly', 'PointsOnly', 'PointsAndMonetary', 'PreserveCurrent'];

        if (!in_array($filter, $validFilters)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid loyalty filter. Expected one of: ' . implode(', ', $validFilters),
                400
            );
        }
    }

    /**
     * Create simple one-way search
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $departureDate Departure date (Y-m-d format)
     * @param int $adultCount Number of adults (default: 1)
     * @param int $childCount Number of children (default: 0)
     * @param int $infantCount Number of infants (default: 0)
     * @param string|null $promotionCode Optional promotion code
     * @return self
     */
    public static function createOneWay(
        string $origin,
        string $destination,
        string $departureDate,
        int $adultCount = 1,
        int $childCount = 0,
        int $infantCount = 0,
        ?string $promotionCode = null
    ): self {
        $passengers = [];

        if ($adultCount > 0) {
            $passengers[] = ['type' => 'ADT', 'count' => $adultCount];
        }
        if ($childCount > 0) {
            $passengers[] = ['type' => 'CHD', 'count' => $childCount];
        }
        if ($infantCount > 0) {
            $passengers[] = ['type' => 'INF', 'count' => $infantCount];
        }

        return new self(
            origin: strtoupper($origin),
            destination: strtoupper($destination),
            beginDate: $departureDate . 'T00:00:00',
            passengers: $passengers,
            promotionCode: $promotionCode
        );
    }

    /**
     * Create simple round-trip search
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $departureDate Departure date (Y-m-d format)
     * @param string $returnDate Return date (Y-m-d format)
     * @param int $adultCount Number of adults (default: 1)
     * @param int $childCount Number of children (default: 0)
     * @param int $infantCount Number of infants (default: 0)
     * @param string|null $promotionCode Optional promotion code
     * @return self
     */
    public static function createRoundTrip(
        string $origin,
        string $destination,
        string $departureDate,
        string $returnDate,
        int $adultCount = 1,
        int $childCount = 0,
        int $infantCount = 0,
        ?string $promotionCode = null
    ): self {
        $passengers = [];

        if ($adultCount > 0) {
            $passengers[] = ['type' => 'ADT', 'count' => $adultCount];
        }
        if ($childCount > 0) {
            $passengers[] = ['type' => 'CHD', 'count' => $childCount];
        }
        if ($infantCount > 0) {
            $passengers[] = ['type' => 'INF', 'count' => $infantCount];
        }

        return new self(
            origin: strtoupper($origin),
            destination: strtoupper($destination),
            beginDate: $departureDate . 'T00:00:00',
            passengers: $passengers,
            endDate: $returnDate . 'T00:00:00',
            promotionCode: $promotionCode
        );
    }
}
