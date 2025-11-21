<?php

namespace SantosDave\JamboJet\Requests;

use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Voucher Issuance Request
 * 
 * Create vouchers based on request data
 * Endpoint: POST /api/nsk/v2/voucherIssuance
 * 
 * @package SantosDave\JamboJet\Requests
 */
class VoucherIssuanceRequest extends BaseRequest
{
    use ValidatesRequests;

    /**
     * Create new voucher issuance request
     * 
     * @param string $configurationCode Voucher configuration code (max 6 chars)
     * @param string $issuanceReasonCode Voucher issuance reason code (max 4 chars)
     * @param float $amount Amount of currency
     * @param string|null $note Voucher issuance note (max 256 chars)
     * @param array|null $market Market details (origin, destination, departureDate, etc.)
     * @param string|null $expiration Voucher expiration date (ISO format)
     * @param string|null $currencyCode Currency code (3 chars)
     * @param string|null $recordLocator Record locator (max 12 chars)
     * @param string|null $firstName Passenger's first name (max 64 chars)
     * @param string|null $lastName Passenger's last name (max 64 chars)
     * @param string|null $customerNumber Customer number (max 20 chars)
     * @param array|null $passengers Array of passenger data
     */
    public function __construct(
        public string $configurationCode,
        public string $issuanceReasonCode,
        public float $amount = 0.0,
        public ?string $note = null,
        public ?array $market = null,
        public ?string $expiration = null,
        public ?string $currencyCode = null,
        public ?string $recordLocator = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $customerNumber = null,
        public ?array $passengers = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'configurationCode' => $this->configurationCode,
            'issuanceReasonCode' => $this->issuanceReasonCode,
            'amount' => $this->amount,
            'note' => $this->note,
            'market' => $this->market,
            'expiration' => $this->expiration,
            'currencyCode' => $this->currencyCode,
            'recordLocator' => $this->recordLocator,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'customerNumber' => $this->customerNumber,
            'passengers' => $this->passengers,
        ]);
    }

    /**
     * Validate request data
     * 
     * @throws JamboJetValidationException
     */
    public function validate(): void
    {
        $data = $this->toArray();

        // Validate required fields
        $this->validateRequired($data, ['configurationCode', 'issuanceReasonCode']);

        // Validate string lengths
        $this->validateStringLengths($data, [
            'configurationCode' => ['max' => 6],
            'issuanceReasonCode' => ['max' => 4],
            'note' => ['max' => 256],
            'currencyCode' => ['min' => 3, 'max' => 3],
            'recordLocator' => ['max' => 12],
            'firstName' => ['max' => 64],
            'lastName' => ['max' => 64],
            'customerNumber' => ['max' => 20],
        ]);

        // Validate amount is not negative
        if ($this->amount < 0) {
            throw new JamboJetValidationException(
                'Amount cannot be negative',
                400
            );
        }

        // Validate expiration date format if provided
        if ($this->expiration && !$this->isValidDateFormat($this->expiration)) {
            throw new JamboJetValidationException(
                'Expiration date must be in valid ISO 8601 format',
                400
            );
        }

        // Validate market structure if provided
        if ($this->market) {
            $this->validateMarket($this->market);
        }
    }

    /**
     * Validate market data structure
     * 
     * @param array $market Market data
     * @throws JamboJetValidationException
     */
    private function validateMarket(array $market): void
    {
        // If any market field is provided, validate the structure
        if (isset($market['origin']) && strlen($market['origin']) !== 3) {
            throw new JamboJetValidationException(
                'Market origin must be exactly 3 characters',
                400
            );
        }

        if (isset($market['destination']) && strlen($market['destination']) !== 3) {
            throw new JamboJetValidationException(
                'Market destination must be exactly 3 characters',
                400
            );
        }

        if (isset($market['carrierCode']) && (strlen($market['carrierCode']) < 2 || strlen($market['carrierCode']) > 3)) {
            throw new JamboJetValidationException(
                'Market carrier code must be 2-3 characters',
                400
            );
        }
    }

    /**
     * Check if date is in valid format
     * 
     * @param string $date Date string
     * @return bool
     */
    private function isValidDateFormat(string $date): bool
    {
        $patterns = [
            '/^\d{4}-\d{2}-\d{2}$/',
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $date)) {
                return true;
            }
        }

        return false;
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
            configurationCode: $data['configurationCode'],
            issuanceReasonCode: $data['issuanceReasonCode'],
            amount: $data['amount'] ?? 0.0,
            note: $data['note'] ?? null,
            market: $data['market'] ?? null,
            expiration: $data['expiration'] ?? null,
            currencyCode: $data['currencyCode'] ?? null,
            recordLocator: $data['recordLocator'] ?? null,
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            customerNumber: $data['customerNumber'] ?? null,
            passengers: $data['passengers'] ?? null
        );
    }

    /**
     * Set market information
     * 
     * @param string $origin Origin station
     * @param string $destination Destination station
     * @param string $departureDate Departure date
     * @param string|null $carrierCode Carrier code
     * @param int|null $flightNumber Flight number
     * @return $this
     */
    public function withMarket(
        string $origin,
        string $destination,
        string $departureDate,
        ?string $carrierCode = null,
        ?int $flightNumber = null
    ): static {
        $this->market = $this->filterNulls([
            'origin' => strtoupper($origin),
            'destination' => strtoupper($destination),
            'departureDate' => $departureDate,
            'carrierCode' => $carrierCode ? strtoupper($carrierCode) : null,
            'flightNumber' => $flightNumber,
        ]);
        return $this;
    }

    /**
     * Set expiration date
     * 
     * @param string $expiration Expiration date
     * @return $this
     */
    public function withExpiration(string $expiration): static
    {
        $this->expiration = $expiration;
        return $this;
    }

    /**
     * Set record locator
     * 
     * @param string $recordLocator Record locator
     * @return $this
     */
    public function withRecordLocator(string $recordLocator): static
    {
        $this->recordLocator = strtoupper($recordLocator);
        return $this;
    }

    /**
     * Set passenger name
     * 
     * @param string $firstName First name
     * @param string $lastName Last name
     * @return $this
     */
    public function withPassengerName(string $firstName, string $lastName): static
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Set customer number
     * 
     * @param string $customerNumber Customer number
     * @return $this
     */
    public function withCustomerNumber(string $customerNumber): static
    {
        $this->customerNumber = $customerNumber;
        return $this;
    }

    /**
     * Add multiple passengers
     * 
     * @param array $passengers Array of passenger data
     * @return $this
     */
    public function withPassengers(array $passengers): static
    {
        $this->passengers = $passengers;
        return $this;
    }
}
