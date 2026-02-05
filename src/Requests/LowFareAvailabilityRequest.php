<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Low Fare Availability Request
 * 
 * Handles low fare availability search requests for NSK API v2
 * Endpoint: POST /api/nsk/v2/availability/lowfare
 * 
 * @package SantosDave\JamboJet\Requests
 */
class LowFareAvailabilityRequest extends BaseRequest
{
    /**
     * Create new low fare availability request
     * 
     * @param array $passengers Required: PassengerTypeCriteria with passenger types and counts
     * @param array $criteria Required: LowFareAvailabilityCriteria array for trip search
     * @param bool $bypassCache Optional: Bypass low fare cache (default: false)
     * @param bool $getAllDetails Optional: Return all caching data (default: false)
     * @param bool $includeTaxesAndFees Optional: Include taxes and fees (default: true)
     * @param array|null $codes Optional: LowFareAvailabilityCodeCriteria
     * @param array|null $filters Optional: LowFareAvailabilityFilterCriteria
     */
    public function __construct(
        public array $passengers,
        public array $criteria,
        public bool $bypassCache = false,
        public bool $getAllDetails = false,
        public bool $includeTaxesAndFees = true,
        public ?array $codes = null,
        public ?array $filters = null
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
            'bypassCache' => $this->bypassCache,
            'getAllDetails' => $this->getAllDetails,
            'includeTaxesAndFees' => $this->includeTaxesAndFees,
            'codes' => $this->codes,
            'filters' => $this->filters,
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
        $this->validateRequired($data, ['passengers', 'criteria']);

        // Validate passengers structure (same as regular availability)
        $this->validatePassengers($this->passengers);

        // Validate criteria structure for low fare search
        $this->validateCriteria($this->criteria);

        // Validate filters if provided
        if ($this->filters) {
            $this->validateFilters($this->filters);
        }

        // Validate codes if provided
        if ($this->codes) {
            $this->validateCodes($this->codes);
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
     * Validate criteria structure for low fare search
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
            // Each trip must have origin, destination, and departure date
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
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Origin and destination cannot be the same for trip {$index}",
                    400
                );
            }

            // Validate departure date is not in the past
            $departureDate = new \DateTime($trip['departureDate']);
            $now = new \DateTime();
            $now->setTime(0, 0, 0);

            if ($departureDate < $now) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Departure date cannot be in the past for trip {$index}",
                    400
                );
            }

            // Validate return date if provided
            if (isset($trip['returnDate'])) {
                $this->validateFormats($trip, ['returnDate' => 'date']);

                $returnDate = new \DateTime($trip['returnDate']);
                if ($returnDate < $departureDate) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "Return date must be after departure date for trip {$index}",
                        400
                    );
                }
            }

            // Validate flexible days if provided
            if (isset($trip['flexibleDays'])) {
                if (!is_int($trip['flexibleDays']) || $trip['flexibleDays'] < 0 || $trip['flexibleDays'] > 7) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "Flexible days must be between 0 and 7 for trip {$index}",
                        400
                    );
                }
            }

            // Validate length of stay limits if provided
            if (isset($trip['minLengthOfStay']) && isset($trip['maxLengthOfStay'])) {
                if ($trip['minLengthOfStay'] > $trip['maxLengthOfStay']) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "Minimum length of stay cannot be greater than maximum for trip {$index}",
                        400
                    );
                }
            }
        }
    }

    /**
     * Validate filters configuration
     * 
     * @param array $filters Filters configuration
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateFilters(array $filters): void
    {
        // Validate price range if provided
        if (isset($filters['priceRange'])) {
            $priceRange = $filters['priceRange'];

            if (isset($priceRange['minPrice']) && isset($priceRange['maxPrice'])) {
                if ($priceRange['minPrice'] > $priceRange['maxPrice']) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        'Minimum price cannot be greater than maximum price',
                        400
                    );
                }
            }

            // Validate price values are positive
            foreach (['minPrice', 'maxPrice'] as $priceField) {
                if (isset($priceRange[$priceField])) {
                    if (!is_numeric($priceRange[$priceField]) || $priceRange[$priceField] < 0) {
                        throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                            "{$priceField} must be a non-negative number",
                            400
                        );
                    }
                }
            }
        }

        // Validate carrier codes if provided
        if (isset($filters['carrierCodes']) && is_array($filters['carrierCodes'])) {
            foreach ($filters['carrierCodes'] as $index => $carrierCode) {
                if (!preg_match('/^[A-Z]{2,3}$/', $carrierCode)) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "Invalid carrier code at index {$index}. Expected 2-3 letter airline code",
                        400
                    );
                }
            }
        }

        // Validate connection filters
        if (isset($filters['connections'])) {
            $validConnectionTypes = ['NonStop', 'OneStop', 'TwoStop', 'Any'];
            if (!in_array($filters['connections'], $validConnectionTypes)) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Invalid connection filter. Expected one of: ' . implode(', ', $validConnectionTypes),
                    400
                );
            }
        }
    }

    /**
     * Validate codes configuration
     * 
     * @param array $codes Codes configuration
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateCodes(array $codes): void
    {
        // Validate promotion code if provided
        if (isset($codes['promotionCode'])) {
            if (strlen($codes['promotionCode']) > 8) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    'Promotion code cannot exceed 8 characters',
                    400
                );
            }
        }

        // Validate currency code if provided
        if (isset($codes['currencyCode'])) {
            $this->validateFormats($codes, ['currencyCode' => 'currency_code']);
        }

        // Validate corporate codes if provided
        if (isset($codes['corporateCodes']) && is_array($codes['corporateCodes'])) {
            foreach ($codes['corporateCodes'] as $index => $corporateCode) {
                if (strlen($corporateCode) > 20) {
                    throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                        "Corporate code at index {$index} cannot exceed 20 characters",
                        400
                    );
                }
            }
        }
    }

    /**
     * Create simple low fare search
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $departureDate Departure date (Y-m-d format)
     * @param int $adultCount Number of adults (default: 1)
     * @param string|null $returnDate Optional return date for round trip
     * @param int $flexibleDays Optional flexible days (default: 0)
     * @return self
     */
    public static function createSimple(
        string $origin,
        string $destination,
        string $departureDate,
        int $adultCount = 1,
        ?string $returnDate = null,
        int $flexibleDays = 0
    ): self {
        $passengers = [
            'types' => [
                ['type' => 'ADT', 'count' => $adultCount]
            ]
        ];

        $trip = [
            'origin' => strtoupper($origin),
            'destination' => strtoupper($destination),
            'departureDate' => $departureDate,
            'flexibleDays' => $flexibleDays
        ];

        if ($returnDate) {
            $trip['returnDate'] = $returnDate;
        }

        return new self(
            passengers: $passengers,
            criteria: [$trip],
            includeTaxesAndFees: true
        );
    }

    /**
     * Create flexible date search
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $departureDate Base departure date
     * @param int $flexibleDays Number of flexible days (±)
     * @param array $passengers Passenger types and counts
     * @param string|null $returnDate Optional return date
     * @return self
     */
    public static function createFlexible(
        string $origin,
        string $destination,
        string $departureDate,
        int $flexibleDays,
        array $passengers = [['type' => 'ADT', 'count' => 1]],
        ?string $returnDate = null
    ): self {
        $trip = [
            'origin' => strtoupper($origin),
            'destination' => strtoupper($destination),
            'departureDate' => $departureDate,
            'flexibleDays' => $flexibleDays
        ];

        if ($returnDate) {
            $trip['returnDate'] = $returnDate;
        }

        return new self(
            passengers: ['types' => $passengers],
            criteria: [$trip],
            includeTaxesAndFees: true
        );
    }

    /**
     * Create price range search
     * 
     * @param string $origin Origin airport code
     * @param string $destination Destination airport code
     * @param string $departureDate Departure date
     * @param float $maxPrice Maximum price limit
     * @param array $passengers Passenger types and counts
     * @param string|null $currencyCode Currency code
     * @return self
     */
    public static function createPriceRange(
        string $origin,
        string $destination,
        string $departureDate,
        float $maxPrice,
        array $passengers = [['type' => 'ADT', 'count' => 1]],
        ?string $currencyCode = null
    ): self {
        $filters = [
            'priceRange' => [
                'maxPrice' => $maxPrice
            ]
        ];

        $codes = null;
        if ($currencyCode) {
            $codes = ['currencyCode' => $currencyCode];
        }

        return new self(
            passengers: ['types' => $passengers],
            criteria: [[
                'origin' => strtoupper($origin),
                'destination' => strtoupper($destination),
                'departureDate' => $departureDate
            ]],
            filters: $filters,
            codes: $codes,
            includeTaxesAndFees: true
        );
    }
}