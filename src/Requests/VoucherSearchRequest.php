<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Voucher Search Request
 * 
 * Search for vouchers with complex criteria
 * Endpoint: GET /api/nsk/v2/vouchers
 * 
 * @package SantosDave\JamboJet\Requests
 */
class VoucherSearchRequest extends BaseRequest
{
    use ValidatesRequests;

    // Sort criteria constants
    public const SORT_CREATED_DATE_ASC = 0;
    public const SORT_NAME = 1;
    public const SORT_CREATED_DATE_DESC = 2;

    /**
     * Create new voucher search request
     * 
     * @param string|null $voucherIssuanceKey Voucher issuance key
     * @param array|null $market Market information (origin, destination, etc.)
     * @param array|null $agent Agent information (name, domain)
     * @param string|null $beginDate Begin date
     * @param string|null $endDate End date
     * @param int|null $pageSize Page size (10-5000)
     * @param string|null $lastPageKey Last page key for pagination
     * @param int|null $sortCriteria Sort criteria (0=CreatedDateAsc, 1=Name, 2=CreatedDateDesc)
     * @param string|null $recordLocator Record locator (max 12 chars)
     * @param array|null $customerName Customer name (firstName, lastName)
     * @param string|null $customerNumber Customer number (max 20 chars)
     * @param bool $activeOnly Return only active vouchers
     * @param string|null $cultureCode Culture code (max 17 chars)
     */
    public function __construct(
        public ?string $voucherIssuanceKey = null,
        public ?array $market = null,
        public ?array $agent = null,
        public ?string $beginDate = null,
        public ?string $endDate = null,
        public ?int $pageSize = null,
        public ?string $lastPageKey = null,
        public ?int $sortCriteria = null,
        public ?string $recordLocator = null,
        public ?array $customerName = null,
        public ?string $customerNumber = null,
        public bool $activeOnly = false,
        public ?string $cultureCode = null
    ) {}

    /**
     * Convert to query parameters array
     * 
     * @return array Query parameters
     */
    public function toArray(): array
    {
        $params = [];

        if ($this->voucherIssuanceKey) {
            $params['VoucherIssuanceKey'] = $this->voucherIssuanceKey;
        }

        // Market parameters
        if ($this->market) {
            if (isset($this->market['destination'])) {
                $params['Market.Destination'] = $this->market['destination'];
            }
            if (isset($this->market['origin'])) {
                $params['Market.Origin'] = $this->market['origin'];
            }
            if (isset($this->market['departureDate'])) {
                $params['Market.DepartureDate'] = $this->market['departureDate'];
            }
            if (isset($this->market['identifier'])) {
                $params['Market.Identifier'] = $this->market['identifier'];
            }
            if (isset($this->market['carrierCode'])) {
                $params['Market.CarrierCode'] = $this->market['carrierCode'];
            }
        }

        // Agent parameters
        if ($this->agent) {
            if (isset($this->agent['name'])) {
                $params['Agent.Name'] = $this->agent['name'];
            }
            if (isset($this->agent['domain'])) {
                $params['Agent.Domain'] = $this->agent['domain'];
            }
        }

        // Date parameters
        if ($this->beginDate) {
            $params['BeginDate'] = $this->beginDate;
        }
        if ($this->endDate) {
            $params['EndDate'] = $this->endDate;
        }

        // Pagination parameters
        if ($this->pageSize) {
            $params['PageSize'] = $this->pageSize;
        }
        if ($this->lastPageKey) {
            $params['LastPageKey'] = $this->lastPageKey;
        }
        if ($this->sortCriteria !== null) {
            $params['SortCriteria'] = $this->sortCriteria;
        }

        // Customer parameters
        if ($this->recordLocator) {
            $params['RecordLocator'] = $this->recordLocator;
        }
        if ($this->customerName) {
            if (isset($this->customerName['firstName'])) {
                $params['CustomerName.FirstName'] = $this->customerName['firstName'];
            }
            if (isset($this->customerName['lastName'])) {
                $params['CustomerName.LastName'] = $this->customerName['lastName'];
            }
        }
        if ($this->customerNumber) {
            $params['CustomerNumber'] = $this->customerNumber;
        }

        $params['ActiveOnly'] = $this->activeOnly;

        if ($this->cultureCode) {
            $params['CultureCode'] = $this->cultureCode;
        }

        return $params;
    }

    /**
     * Validate request data
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        // Validate page size range
        if ($this->pageSize !== null && ($this->pageSize < 10 || $this->pageSize > 5000)) {
            throw new JamboJetValidationException(
                'Page size must be between 10 and 5000',
                400
            );
        }

        // Validate sort criteria
        if ($this->sortCriteria !== null) {
            $validSortCriteria = [
                self::SORT_CREATED_DATE_ASC,
                self::SORT_NAME,
                self::SORT_CREATED_DATE_DESC
            ];
            if (!in_array($this->sortCriteria, $validSortCriteria, true)) {
                throw new JamboJetValidationException(
                    'Invalid sort criteria. Must be 0 (CreatedDateAsc), 1 (Name), or 2 (CreatedDateDesc)',
                    400
                );
            }
        }

        // Validate date requirements
        if ($this->beginDate || $this->endDate) {
            $this->validateDateRequirements();
        }

        // Validate market structure
        if ($this->market) {
            $this->validateMarketStructure();
        }

        // Validate agent structure
        if ($this->agent) {
            $this->validateAgentStructure();
        }

        // Validate customer name requirements
        if ($this->customerName) {
            if (isset($this->customerName['firstName']) && !isset($this->customerName['lastName'])) {
                throw new JamboJetValidationException(
                    'Customer last name is required when first name is provided',
                    400
                );
            }
        }

        // Validate string lengths
        if ($this->recordLocator && strlen($this->recordLocator) > 12) {
            throw new JamboJetValidationException(
                'Record locator cannot exceed 12 characters',
                400
            );
        }

        if ($this->customerNumber && strlen($this->customerNumber) > 20) {
            throw new JamboJetValidationException(
                'Customer number cannot exceed 20 characters',
                400
            );
        }

        if ($this->cultureCode && strlen($this->cultureCode) > 17) {
            throw new JamboJetValidationException(
                'Culture code cannot exceed 17 characters',
                400
            );
        }
    }

    /**
     * Validate date requirements
     * 
     * @throws JamboJetValidationException
     */
    private function validateDateRequirements(): void
    {
        // If dates are provided, certain fields are required
        $hasRequiredField =
            ($this->customerName && isset($this->customerName['lastName'])) ||
            $this->customerNumber ||
            ($this->agent && (isset($this->agent['name']) || isset($this->agent['domain'])));

        if (!$hasRequiredField) {
            throw new JamboJetValidationException(
                'When date is provided, one of the following is required: CustomerName.LastName, CustomerNumber, Agent.Name, or Agent.Domain',
                400
            );
        }

        // If only end date is given, begin date is required
        if ($this->endDate && !$this->beginDate) {
            throw new JamboJetValidationException(
                'Begin date is required when end date is provided',
                400
            );
        }
    }

    /**
     * Validate market structure
     * 
     * @throws JamboJetValidationException
     */
    private function validateMarketStructure(): void
    {
        $marketFields = ['origin', 'destination', 'departureDate', 'identifier', 'carrierCode'];
        $providedFields = array_intersect($marketFields, array_keys($this->market));

        if (!empty($providedFields)) {
            // If any market field is provided, all are required
            foreach ($marketFields as $field) {
                if (!isset($this->market[$field])) {
                    throw new JamboJetValidationException(
                        "All market fields are required when any market field is provided. Missing: {$field}",
                        400
                    );
                }
            }

            // Validate station codes
            if (strlen($this->market['origin']) !== 3) {
                throw new JamboJetValidationException(
                    'Market origin must be exactly 3 characters',
                    400
                );
            }

            if (strlen($this->market['destination']) !== 3) {
                throw new JamboJetValidationException(
                    'Market destination must be exactly 3 characters',
                    400
                );
            }
        }
    }

    /**
     * Validate agent structure
     * 
     * @throws JamboJetValidationException
     */
    private function validateAgentStructure(): void
    {
        $hasName = isset($this->agent['name']);
        $hasDomain = isset($this->agent['domain']);

        if (($hasName && !$hasDomain) || (!$hasName && $hasDomain)) {
            throw new JamboJetValidationException(
                'Both agent name and domain are required when either is provided',
                400
            );
        }
    }

    /**
     * Create from array
     * 
     * @param array $data Request data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        return new static(
            voucherIssuanceKey: $data['voucherIssuanceKey'] ?? null,
            market: $data['market'] ?? null,
            agent: $data['agent'] ?? null,
            beginDate: $data['beginDate'] ?? null,
            endDate: $data['endDate'] ?? null,
            pageSize: $data['pageSize'] ?? null,
            lastPageKey: $data['lastPageKey'] ?? null,
            sortCriteria: $data['sortCriteria'] ?? null,
            recordLocator: $data['recordLocator'] ?? null,
            customerName: $data['customerName'] ?? null,
            customerNumber: $data['customerNumber'] ?? null,
            activeOnly: $data['activeOnly'] ?? false,
            cultureCode: $data['cultureCode'] ?? null
        );
    }

    /**
     * Set market criteria
     * 
     * @param string $origin Origin station
     * @param string $destination Destination station
     * @param string $departureDate Departure date
     * @param string $identifier Flight identifier
     * @param string $carrierCode Carrier code
     * @return $this
     */
    public function withMarket(
        string $origin,
        string $destination,
        string $departureDate,
        string $identifier,
        string $carrierCode
    ): static {
        $this->market = [
            'origin' => strtoupper($origin),
            'destination' => strtoupper($destination),
            'departureDate' => $departureDate,
            'identifier' => $identifier,
            'carrierCode' => strtoupper($carrierCode),
        ];
        return $this;
    }

    /**
     * Set agent criteria
     * 
     * @param string $name Agent name
     * @param string $domain Agent domain
     * @return $this
     */
    public function withAgent(string $name, string $domain): static
    {
        $this->agent = [
            'name' => $name,
            'domain' => $domain,
        ];
        return $this;
    }

    /**
     * Set date range
     * 
     * @param string $beginDate Begin date
     * @param string|null $endDate End date
     * @return $this
     */
    public function withDateRange(string $beginDate, ?string $endDate = null): static
    {
        $this->beginDate = $beginDate;
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Set pagination
     * 
     * @param int $pageSize Page size (10-5000)
     * @param string|null $lastPageKey Last page key
     * @return $this
     */
    public function withPagination(int $pageSize, ?string $lastPageKey = null): static
    {
        $this->pageSize = $pageSize;
        $this->lastPageKey = $lastPageKey;
        return $this;
    }

    /**
     * Set sort criteria
     * 
     * @param int $sortCriteria Sort criteria
     * @return $this
     */
    public function withSortCriteria(int $sortCriteria): static
    {
        $this->sortCriteria = $sortCriteria;
        return $this;
    }

    /**
     * Set customer name
     * 
     * @param string $firstName First name
     * @param string $lastName Last name
     * @return $this
     */
    public function withCustomerName(string $firstName, string $lastName): static
    {
        $this->customerName = [
            'firstName' => $firstName,
            'lastName' => $lastName,
        ];
        return $this;
    }
}
