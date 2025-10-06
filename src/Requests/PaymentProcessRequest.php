<?php

namespace SantosDave\JamboJet\Requests;

/**
 * Payment Process Request
 * 
 * Handles payment processing requests for NSK API
 * Endpoint: POST /api/nsk/v{1-6}/booking/payments
 * 
 * @package SantosDave\JamboJet\Requests
 */
class PaymentProcessRequest extends BaseRequest
{
    /**
     * Create new payment processing request
     * 
     * @param string $paymentMethodType Required: Payment method type (CreditCard, Cash, Voucher, etc.)
     * @param array $paymentDetails Required: Payment method specific details
     * @param float $amount Required: Payment amount
     * @param string $currencyCode Required: Currency code (3-letter ISO)
     * @param array|null $billingAddress Optional: Billing address information
     * @param string|null $parentPaymentKey Optional: Parent payment key for child payments
     * @param bool $isDeposit Optional: Whether this is a deposit payment (default: false)
     * @param array|null $installments Optional: Installment configuration
     * @param array|null $loyaltyProgram Optional: Loyalty program redemption details
     * @param string|null $externalPaymentReference Optional: External payment reference
     */
    public function __construct(
        public string $paymentMethodType,
        public array $paymentDetails,
        public float $amount,
        public string $currencyCode,
        public ?array $billingAddress = null,
        public ?string $parentPaymentKey = null,
        public bool $isDeposit = false,
        public ?array $installments = null,
        public ?array $loyaltyProgram = null,
        public ?string $externalPaymentReference = null
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array Request data
     */
    public function toArray(): array
    {
        return $this->filterNulls([
            'paymentMethodType' => $this->paymentMethodType,
            'paymentDetails' => $this->paymentDetails,
            'amount' => $this->amount,
            'currencyCode' => $this->currencyCode,
            'billingAddress' => $this->billingAddress,
            'parentPaymentKey' => $this->parentPaymentKey,
            'isDeposit' => $this->isDeposit,
            'installments' => $this->installments,
            'loyaltyProgram' => $this->loyaltyProgram,
            'externalPaymentReference' => $this->externalPaymentReference,
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
        $this->validateRequired($data, ['paymentMethodType', 'paymentDetails', 'amount', 'currencyCode']);

        // Validate amount
        if ($this->amount <= 0) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Payment amount must be greater than zero',
                400
            );
        }

        // Validate currency code
        $this->validateFormats($data, ['currencyCode' => 'currency_code']);

        // Validate payment method type
        $this->validatePaymentMethodType($this->paymentMethodType);

        // Validate payment details based on method type
        $this->validatePaymentDetails($this->paymentMethodType, $this->paymentDetails);

        // Validate billing address if provided
        if ($this->billingAddress) {
            $this->validateBillingAddress($this->billingAddress);
        }

        // Validate installments if provided
        if ($this->installments) {
            $this->validateInstallments($this->installments);
        }

        // Validate loyalty program if provided
        if ($this->loyaltyProgram) {
            $this->validateLoyaltyProgram($this->loyaltyProgram);
        }
    }

    /**
     * Validate payment method type
     * 
     * @param string $paymentMethodType Payment method type
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePaymentMethodType(string $paymentMethodType): void
    {
        $validTypes = [
            'CreditCard',
            'DebitCard',
            'Cash',
            'Voucher',
            'Loyalty',
            'BankTransfer',
            'PayPal',
            'MobileMoney',
            'Agency',
            'GiftCard'
        ];

        if (!in_array($paymentMethodType, $validTypes)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid payment method type. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }
    }

    /**
     * Validate payment details based on payment method
     * 
     * @param string $paymentMethodType Payment method type
     * @param array $paymentDetails Payment details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validatePaymentDetails(string $paymentMethodType, array $paymentDetails): void
    {
        switch ($paymentMethodType) {
            case 'CreditCard':
            case 'DebitCard':
                $this->validateCreditCardDetails($paymentDetails);
                break;
            case 'Voucher':
                $this->validateVoucherDetails($paymentDetails);
                break;
            case 'Loyalty':
                $this->validateLoyaltyPaymentDetails($paymentDetails);
                break;
            case 'MobileMoney':
                $this->validateMobileMoneyDetails($paymentDetails);
                break;
            case 'BankTransfer':
                $this->validateBankTransferDetails($paymentDetails);
                break;
        }
    }

    /**
     * Validate credit card payment details
     * 
     * @param array $details Credit card details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateCreditCardDetails(array $details): void
    {
        $this->validateRequired($details, ['cardNumber', 'expiryMonth', 'expiryYear', 'cvv', 'cardHolderName']);

        // Validate card number (basic length check)
        $cardNumber = preg_replace('/\s+/', '', $details['cardNumber']);
        if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid card number format',
                400
            );
        }

        // Validate expiry month
        if (!is_numeric($details['expiryMonth']) || $details['expiryMonth'] < 1 || $details['expiryMonth'] > 12) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid expiry month. Must be between 1 and 12',
                400
            );
        }

        // Validate expiry year
        $currentYear = (int) date('Y');
        if (!is_numeric($details['expiryYear']) || $details['expiryYear'] < $currentYear) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid expiry year. Cannot be in the past',
                400
            );
        }

        // Validate CVV
        if (!preg_match('/^\d{3,4}$/', $details['cvv'])) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid CVV format. Must be 3 or 4 digits',
                400
            );
        }

        // Validate cardholder name
        if (strlen($details['cardHolderName']) < 2) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Cardholder name must be at least 2 characters',
                400
            );
        }
    }

    /**
     * Validate voucher payment details
     * 
     * @param array $details Voucher details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateVoucherDetails(array $details): void
    {
        $this->validateRequired($details, ['voucherNumber']);

        if (empty(trim($details['voucherNumber']))) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Voucher number cannot be empty',
                400
            );
        }
    }

    /**
     * Validate loyalty payment details
     * 
     * @param array $details Loyalty payment details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateLoyaltyPaymentDetails(array $details): void
    {
        $this->validateRequired($details, ['programCode', 'membershipNumber', 'pointsToRedeem']);

        if (!is_numeric($details['pointsToRedeem']) || $details['pointsToRedeem'] <= 0) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Points to redeem must be a positive number',
                400
            );
        }
    }

    /**
     * Validate mobile money payment details
     * 
     * @param array $details Mobile money details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateMobileMoneyDetails(array $details): void
    {
        $this->validateRequired($details, ['provider', 'phoneNumber']);

        if (!preg_match('/^\+?[\d\s\-\(\)]+$/', $details['phoneNumber'])) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Invalid phone number format for mobile money',
                400
            );
        }
    }

    /**
     * Validate bank transfer payment details
     * 
     * @param array $details Bank transfer details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateBankTransferDetails(array $details): void
    {
        $this->validateRequired($details, ['bankCode', 'accountNumber']);
    }

    /**
     * Validate billing address
     * 
     * @param array $address Billing address
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateBillingAddress(array $address): void
    {
        if (isset($address['countryCode'])) {
            $this->validateFormats($address, ['countryCode' => 'country_code']);
        }

        if (isset($address['email'])) {
            $this->validateFormats($address, ['email' => 'email']);
        }
    }

    /**
     * Validate installments configuration
     * 
     * @param array $installments Installments configuration
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateInstallments(array $installments): void
    {
        $this->validateRequired($installments, ['numberOfInstallments']);

        if (!is_numeric($installments['numberOfInstallments']) || $installments['numberOfInstallments'] < 2) {
            throw new \SantosDave\JamboJet\Exceptions\JamboJetValidationException(
                'Number of installments must be at least 2',
                400
            );
        }
    }

    /**
     * Validate loyalty program redemption
     * 
     * @param array $loyaltyProgram Loyalty program details
     * @throws \SantosDave\JamboJet\Exceptions\JamboJetValidationException
     */
    private function validateLoyaltyProgram(array $loyaltyProgram): void
    {
        $this->validateRequired($loyaltyProgram, ['programCode', 'membershipNumber']);
    }

    /**
     * Create credit card payment request
     * 
     * @param float $amount Payment amount
     * @param string $currencyCode Currency code
     * @param array $cardDetails Credit card details
     * @param array|null $billingAddress Optional billing address
     * @return self
     */
    public static function createCreditCardPayment(
        float $amount,
        string $currencyCode,
        array $cardDetails,
        ?array $billingAddress = null
    ): self {
        return new self(
            paymentMethodType: 'CreditCard',
            paymentDetails: $cardDetails,
            amount: $amount,
            currencyCode: $currencyCode,
            billingAddress: $billingAddress
        );
    }

    /**
     * Create mobile money payment request
     * 
     * @param float $amount Payment amount
     * @param string $currencyCode Currency code
     * @param string $provider Mobile money provider
     * @param string $phoneNumber Phone number
     * @return self
     */
    public static function createMobileMoneyPayment(
        float $amount,
        string $currencyCode,
        string $provider,
        string $phoneNumber
    ): self {
        return new self(
            paymentMethodType: 'MobileMoney',
            paymentDetails: [
                'provider' => $provider,
                'phoneNumber' => $phoneNumber
            ],
            amount: $amount,
            currencyCode: $currencyCode
        );
    }

    /**
     * Create voucher payment request
     * 
     * @param float $amount Payment amount
     * @param string $currencyCode Currency code
     * @param string $voucherNumber Voucher number
     * @return self
     */
    public static function createVoucherPayment(
        float $amount,
        string $currencyCode,
        string $voucherNumber
    ): self {
        return new self(
            paymentMethodType: 'Voucher',
            paymentDetails: ['voucherNumber' => $voucherNumber],
            amount: $amount,
            currencyCode: $currencyCode
        );
    }
}
