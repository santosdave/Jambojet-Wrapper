<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Add-ons Sell Request
 * 
 * Handles add-on service requests for NSK API v1/v2
 * Endpoints: POST /api/nsk/v1/addOns/* (seats, baggage, meals, etc.)
 * 
 * @package SantosDave\JamboJet\Requests
 */
class AddOnsSellRequest extends BaseRequest
{
    /**
     * Create new add-ons sell request
     * 
     * @param string $addOnType Required: Type of add-on (seats, bags, meals, insurance, etc.)
     * @param array $items Required: Array of add-on items to sell
     * @param string|null $passengerKey Optional: Specific passenger key for passenger-specific add-ons
     * @param string|null $journeyKey Optional: Specific journey key for journey-specific add-ons
     * @param string|null $segmentKey Optional: Specific segment key for segment-specific add-ons
     * @param array|null $paymentInfo Optional: Payment information if payment required
     * @param bool $validateOnly Optional: Validate request without committing (default: false)
     */
    public function __construct(
        public string $addOnType,
        public array $items,
        public ?string $passengerKey = null,
        public ?string $journeyKey = null,
        public ?string $segmentKey = null,
        public ?array $paymentInfo = null,
        public bool $validateOnly = false
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'addOnType' => $this->addOnType,
            'items' => $this->items,
            'passengerKey' => $this->passengerKey,
            'journeyKey' => $this->journeyKey,
            'segmentKey' => $this->segmentKey,
            'paymentInfo' => $this->paymentInfo,
            'validateOnly' => $this->validateOnly,
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
        $this->validateRequired($data, ['addOnType', 'items']);

        // Validate add-on type
        $this->validateAddOnType($this->addOnType);

        // Validate items based on add-on type
        $this->validateItems($this->addOnType, $this->items);

        // Validate payment info if provided
        if ($this->paymentInfo) {
            $this->validatePaymentInfo($this->paymentInfo);
        }
    }

    /**
     * Validate add-on type
     * 
     * @param string $addOnType Add-on type
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateAddOnType(string $addOnType): void
    {
        $validTypes = [
            'seats',
            'bags',
            'meals',
            'insurance',
            'loungeAccess',
            'merchandise',
            'petTransport',
            'serviceCharges',
            'specialServiceRequests',
            'activities',
            'hotels',
            'cars'
        ];

        if (!in_array($addOnType, $validTypes)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid add-on type. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }
    }

    /**
     * Validate items based on add-on type
     * 
     * @param string $addOnType Add-on type
     * @param array $items Items array
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateItems(string $addOnType, array $items): void
    {
        if (empty($items)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Items array cannot be empty',
                400
            );
        }

        foreach ($items as $index => $item) {
            switch ($addOnType) {
                case 'seats':
                    $this->validateSeatItem($item, $index);
                    break;
                case 'bags':
                    $this->validateBaggageItem($item, $index);
                    break;
                case 'meals':
                    $this->validateMealItem($item, $index);
                    break;
                case 'insurance':
                    $this->validateInsuranceItem($item, $index);
                    break;
                case 'specialServiceRequests':
                    $this->validateSSRItem($item, $index);
                    break;
                case 'serviceCharges':
                    $this->validateServiceChargeItem($item, $index);
                    break;
                default:
                    $this->validateGenericItem($item, $index);
                    break;
            }
        }
    }

    /**
     * Validate seat assignment item
     * 
     * @param array $item Seat item
     * @param int $index Item index
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateSeatItem(array $item, int $index): void
    {
        $this->validateRequired($item, ['seatNumber', 'segmentKey', 'passengerKey']);

        // Validate seat number format (e.g., "12A", "05F")
        if (!preg_match('/^\d{1,3}[A-Z]$/', $item['seatNumber'])) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Invalid seat number format at item {$index}. Expected format: 12A",
                400
            );
        }

        // Validate keys are not empty
        if (empty(trim($item['segmentKey'])) || empty(trim($item['passengerKey']))) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Segment key and passenger key cannot be empty at item {$index}",
                400
            );
        }
    }

    /**
     * Validate baggage item
     * 
     * @param array $item Baggage item
     * @param int $index Item index
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateBaggageItem(array $item, int $index): void
    {
        $this->validateRequired($item, ['baggageType', 'weight', 'passengerKey']);

        // Validate baggage type
        $validBaggageTypes = ['Checked', 'CarryOn', 'Personal', 'Excess'];
        if (!in_array($item['baggageType'], $validBaggageTypes)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Invalid baggage type at item {$index}. Expected one of: " . implode(', ', $validBaggageTypes),
                400
            );
        }

        // Validate weight
        if (!is_numeric($item['weight']) || $item['weight'] <= 0) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Invalid weight at item {$index}. Must be a positive number",
                400
            );
        }

        // Validate weight limit (reasonable airline limits)
        if ($item['weight'] > 50) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Weight exceeds maximum limit of 50kg at item {$index}",
                400
            );
        }
    }

    /**
     * Validate meal item
     * 
     * @param array $item Meal item
     * @param int $index Item index
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateMealItem(array $item, int $index): void
    {
        $this->validateRequired($item, ['mealCode', 'segmentKey', 'passengerKey']);

        // Validate meal code format (typically 4-letter codes like VGML, KSML)
        if (!preg_match('/^[A-Z]{4}$/', $item['mealCode'])) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Invalid meal code format at item {$index}. Expected 4-letter code like VGML",
                400
            );
        }
    }

    /**
     * Validate insurance item
     * 
     * @param array $item Insurance item
     * @param int $index Item index
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateInsuranceItem(array $item, int $index): void
    {
        $this->validateRequired($item, ['insuranceType', 'coverage', 'passengerKey']);

        // Validate coverage amount
        if (isset($item['coverageAmount'])) {
            if (!is_numeric($item['coverageAmount']) || $item['coverageAmount'] <= 0) {
                throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                    "Invalid coverage amount at item {$index}. Must be a positive number",
                    400
                );
            }
        }
    }

    /**
     * Validate Special Service Request (SSR) item
     * 
     * @param array $item SSR item
     * @param int $index Item index
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateSSRItem(array $item, int $index): void
    {
        $this->validateRequired($item, ['ssrCode']);

        // Validate SSR code format (typically 4-letter codes)
        if (!preg_match('/^[A-Z]{4}$/', $item['ssrCode'])) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Invalid SSR code format at item {$index}. Expected 4-letter code",
                400
            );
        }

        // Validate free text if provided
        if (isset($item['freeText']) && strlen($item['freeText']) > 200) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "SSR free text exceeds 200 characters at item {$index}",
                400
            );
        }
    }

    /**
     * Validate service charge item
     * 
     * @param array $item Service charge item
     * @param int $index Item index
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateServiceChargeItem(array $item, int $index): void
    {
        $this->validateRequired($item, ['chargeCode', 'amount']);

        // Validate charge amount
        if (!is_numeric($item['amount']) || $item['amount'] <= 0) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Invalid charge amount at item {$index}. Must be a positive number",
                400
            );
        }
    }

    /**
     * Validate generic add-on item
     * 
     * @param array $item Generic item
     * @param int $index Item index
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateGenericItem(array $item, int $index): void
    {
        // At minimum, require a product key or code
        $hasIdentifier = isset($item['productKey']) || isset($item['code']) || isset($item['itemCode']);

        if (!$hasIdentifier) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                "Item {$index} must have at least one identifier (productKey, code, or itemCode)",
                400
            );
        }
    }

    /**
     * Validate payment information
     * 
     * @param array $paymentInfo Payment information
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePaymentInfo(array $paymentInfo): void
    {
        $this->validateRequired($paymentInfo, ['amount', 'currencyCode']);

        // Validate amount
        if (!is_numeric($paymentInfo['amount']) || $paymentInfo['amount'] <= 0) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Payment amount must be a positive number',
                400
            );
        }

        // Validate currency code
        $this->validateFormats($paymentInfo, ['currencyCode' => 'currency_code']);
    }

    /**
     * Create seat assignment request
     * 
     * @param array $seatAssignments Array of seat assignments
     * @return self
     */
    public static function createSeatAssignment(array $seatAssignments): self
    {
        return new self(
            addOnType: 'seats',
            items: $seatAssignments
        );
    }

    /**
     * Create baggage purchase request
     * 
     * @param array $baggageItems Array of baggage items
     * @param array|null $paymentInfo Optional payment information
     * @return self
     */
    public static function createBaggagePurchase(array $baggageItems, ?array $paymentInfo = null): self
    {
        return new self(
            addOnType: 'bags',
            items: $baggageItems,
            paymentInfo: $paymentInfo
        );
    }

    /**
     * Create meal selection request
     * 
     * @param array $mealSelections Array of meal selections
     * @return self
     */
    public static function createMealSelection(array $mealSelections): self
    {
        return new self(
            addOnType: 'meals',
            items: $mealSelections
        );
    }

    /**
     * Create insurance purchase request
     * 
     * @param array $insuranceItems Array of insurance items
     * @param array $paymentInfo Payment information
     * @return self
     */
    public static function createInsurancePurchase(array $insuranceItems, array $paymentInfo): self
    {
        return new self(
            addOnType: 'insurance',
            items: $insuranceItems,
            paymentInfo: $paymentInfo
        );
    }

    /**
     * Create SSR request
     * 
     * @param array $ssrRequests Array of special service requests
     * @return self
     */
    public static function createSSRRequest(array $ssrRequests): self
    {
        return new self(
            addOnType: 'specialServiceRequests',
            items: $ssrRequests
        );
    }
}
