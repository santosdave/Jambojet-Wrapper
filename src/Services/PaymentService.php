<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\PaymentInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Payment Service for JamboJet NSK API
 * 
 * Handles all payment processing, refunds, and credit operations for the NSK booking system.
 * NSK uses a "stateful" approach where payments are applied to bookings in session state.
 * 
 * Supported Payment Types:
 * - External Account (Credit Cards, etc.)
 * - PrePaid (Vouchers)
 * - Customer Account (Customer Credits)
 * - Agency Account (Organization Credits) 
 * - Loyalty Points
 * - Stored Payments
 * 
 * Supported endpoints:
 * - POST /api/nsk/v6/booking/payments - Create payment (latest)
 * - POST /api/nsk/v4/booking/payments/refunds - Process refunds (latest)
 * - GET/POST /api/nsk/v3/booking/payments/customerCredit - Customer credit operations
 * - GET/POST /api/nsk/v3/booking/payments/organizationCredit - Organization credit
 * - POST /api/nsk/v6/booking/payments/storedPayment/{key} - Stored payment usage
 * - GET /api/nsk/v1/booking/payments/refunds - Available refund methods
 * 
 * @package SantosDave\JamboJet\Services
 */
class PaymentService implements PaymentInterface
{
    use HandlesApiRequests, ValidatesRequests;

    /**
     * Use a stored payment method.
     *
     * @param string $storedPaymentKey
     * @param array $paymentData
     * @return array
     */
    public function useStoredPayment(string $storedPaymentKey, array $paymentData): array
    {
        return $this->processStoredPayment($storedPaymentKey, $paymentData);
    }

    /**
     * Get all stored payments for the current user/session.
     *
     * @return array
     */
    public function getStoredPayments(): array
    {
        try {
            return $this->get('api/nsk/v6/booking/payments/storedPayments');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get stored payments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create a customer credit refund.
     *
     * @param array $refundData
     * @return array
     */
    public function createCustomerCreditRefund(array $refundData): array
    {
        return $this->processCustomerCreditRefund($refundData);
    }

    /**
     * Create an organization credit refund.
     *
     * @param array $refundData
     * @return array
     */
    public function createOrganizationCreditRefund(array $refundData): array
    {
        return $this->processOrganizationCreditRefund($refundData);
    }

    /**
     * Get available payment method types.
     *
     * @return array
     */
    public function getPaymentMethodTypes(): array
    {
        // Example: return a static list or fetch from API if available
        return [
            'ExternalAccount',
            'CustomerAccount',
            'AgencyAccount',
            'Voucher',
            'Loyalty',
            'Cash',
            'PrePaid',
            'MobileMoney'
        ];
    }

    /**
     * Verify a payment.
     *
     * @param array $paymentData
     * @return array
     */
    public function verifyPayment(array $paymentData): array
    {
        if (empty($paymentData['paymentKey'])) {
            throw new JamboJetValidationException('paymentKey is required for verification');
        }
        return $this->verifyStatus($paymentData['paymentKey']);
    }

    /**
     * Process payment for booking in state
     * 
     * POST /api/nsk/v6/booking/payments
     * Process various types of payments including credit cards, mobile money, etc.
     * Requires a booking to be loaded in session state
     * 
     * @param array $paymentData Payment processing data
     * @return array Payment processing response
     * @throws JamboJetApiException
     */
    public function processPayment(array $paymentData): array
    {
        $this->validatePaymentProcessRequest($paymentData);

        try {
            // Note: No recordLocator needed - NSK uses stateful session
            return $this->post("api/nsk/v6/booking/payments", $paymentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Payment processing failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Process payment using stored payment method
     * 
     * POST /api/nsk/v6/booking/payments/storedPayment/{storedPaymentKey}
     * Uses previously stored payment information (account number, expiration)
     * 
     * @param string $storedPaymentKey Stored payment key
     * @param array $paymentData Additional payment data if required
     * @param string|null $termUrl 3D Secure term URL if required
     * @return array Payment processing response
     * @throws JamboJetApiException
     */
    public function processStoredPayment(string $storedPaymentKey, array $paymentData = [], ?string $termUrl = null): array
    {
        if (empty($storedPaymentKey)) {
            throw new JamboJetValidationException('Stored payment key is required');
        }

        try {
            $endpoint = "api/nsk/v6/booking/payments/storedPayment/{$storedPaymentKey}";

            if ($termUrl) {
                $endpoint .= "?termUrl=" . urlencode($termUrl);
            }

            $response = $this->post($endpoint, $paymentData);

            // Handle 3D Secure if required
            if (isset($response['meta']['status_code']) && $response['meta']['status_code'] === 202) {
                $response['requires_3ds'] = true;
                $response['message'] = '3D Secure authentication required.';
            }

            return $response;
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Stored payment processing failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payments for booking in state
     * 
     * GET /api/nsk/v6/booking/payments
     * Retrieves all payments for the booking currently in session state
     * 
     * @return array Payment information
     * @throws JamboJetApiException
     */
    public function getPayments(): array
    {
        try {
            return $this->get('api/nsk/v6/booking/payments');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific payment by key for booking in state
     * 
     * GET /api/nsk/v6/booking/payments/{paymentKey}
     * Retrieves specific payment details for booking in session
     * 
     * @param string $paymentKey Payment key
     * @return array Payment details
     * @throws JamboJetApiException
     */
    public function getPaymentByKey(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->get("api/nsk/v6/booking/payments/{$paymentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process refund for booking in state
     * 
     * POST /api/nsk/v4/booking/payments/refunds
     * Creates a refund for the booking currently in session state
     * 
     * @param array $refundData Refund processing data
     * @return array Refund processing response
     * @throws JamboJetApiException
     */
    public function processRefund(array $refundData): array
    {
        $this->validateRefundRequest($refundData);

        try {
            return $this->post('api/nsk/v4/booking/payments/refunds', $refundData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Refund processing failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process customer credit refund
     * 
     * POST /api/nsk/v3/booking/payments/refunds/customerCredit
     * Creates customer credit for specified customer (requires agent token)
     * 
     * @param array $customerCreditData Customer credit refund request
     * @return array Customer credit response
     * @throws JamboJetApiException
     */
    public function processCustomerCreditRefund(array $customerCreditData): array
    {
        try {
            return $this->post('api/nsk/v3/booking/payments/refunds/customerCredit', $customerCreditData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Customer credit refund failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process organization credit refund
     * 
     * POST /api/nsk/v2/booking/payments/refunds/organizationCredit
     * Creates organization account refund for payment from booking in state
     * 
     * @param array $organizationRefundData Organization refund request
     * @return array Organization refund response
     * @throws JamboJetApiException
     */
    public function processOrganizationCreditRefund(array $organizationRefundData): array
    {
        try {
            return $this->post('api/nsk/v2/booking/payments/refunds/organizationCredit', $organizationRefundData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Organization credit refund failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get available refund methods for booking in state
     * 
     * GET /api/nsk/v1/booking/payments/refunds
     * Gets available refund methods for the booking in session
     * 
     * @return array Available refund methods
     * @throws JamboJetApiException
     */
    public function getAvailableRefundMethods(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/payments/refunds');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get refund methods: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get refund information for a booking
     * 
     * GET /api/nsk/v6/booking/refunds
     * Retrieve all refunds for a booking
     * 
     * @param string $recordLocator Booking record locator
     * @return array Refund information
     * @throws JamboJetApiException
     */
    public function getRefunds(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get('api/nsk/v6/booking/refunds', [
                'recordLocator' => $recordLocator
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve refunds: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Apply customer credit to booking in state
     * 
     * POST /api/nsk/v3/booking/payments/customerCredit
     * Applies customer credit to the booking in session
     * 
     * @param array $creditData Customer credit data
     * @return array Credit application response
     * @throws JamboJetApiException
     */
    public function applyCustomerCredit(array $creditData): array
    {
        $this->validateCreditRequest($creditData);

        try {
            return $this->post('api/nsk/v3/booking/payments/customerCredit', $creditData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to apply customer credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Get available customer credit
     * 
     * GET /api/nsk/v2/booking/payments/customerCredit
     * Gets credit available for logged in user on booking in state
     * 
     * @param string|null $currencyCode Currency code filter
     * @return array Available customer credit
     * @throws JamboJetApiException
     */
    public function getAvailableCustomerCredit(?string $currencyCode = null): array
    {
        try {
            $endpoint = 'api/nsk/v2/booking/payments/customerCredit';

            if ($currencyCode) {
                $endpoint .= '?CurrencyCode=' . urlencode($currencyCode);
            }

            return $this->get($endpoint);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get customer credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Apply organization credit to booking in state
     * 
     * POST /api/nsk/v3/booking/payments/organizationCredit
     * Applies organization credit to the booking in session
     * 
     * @param array $creditData Organization credit data
     * @return array Credit application response
     * @throws JamboJetApiException
     */
    public function applyOrganizationCredit(array $creditData): array
    {
        $this->validateCreditRequest($creditData);

        try {
            return $this->post('api/nsk/v3/booking/payments/organizationCredit', $creditData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to apply organization credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get available organization credit
     * 
     * GET /api/nsk/v2/booking/payments/organizationCredit
     * Gets credit available for logged in user's organization
     * 
     * @param string|null $currencyCode Currency code filter
     * @return array Available organization credit
     * @throws JamboJetApiException
     */
    public function getAvailableOrganizationCredit(?string $currencyCode = null): array
    {
        try {
            $endpoint = 'api/nsk/v2/booking/payments/organizationCredit';

            if ($currencyCode) {
                $endpoint .= '?CurrencyCode=' . urlencode($currencyCode);
            }

            return $this->get($endpoint);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get available organization credit
     * 
     * GET /api/nsk/v3/booking/payments/organizationCredit
     * Gets available organization credit
     * 
     * @return array Available organization credit
     * @throws JamboJetApiException
     */
    public function getOrganizationCredit(): array
    {
        try {
            return $this->get('api/nsk/v3/booking/payments/organizationCredit');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get organization credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payments for a completed booking (stateless)
     * 
     * GET /api/nsk/v1/bookings/{recordLocator}/payments
     * Retrieves payments for a completed booking without needing session state
     * 
     * @param string $recordLocator Booking record locator
     * @return array Payment information
     * @throws JamboJetApiException
     */
    public function getBookingPayments(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}/payments");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking payments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment details by payment key (stateless)
     * 
     * GET /api/nsk/v1/payments/{paymentKey}
     * Retrieves payment information without session state
     * 
     * @param string $paymentKey Payment key
     * @return array Payment details
     * @throws JamboJetApiException
     */
    public function getPaymentDetails(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->get("api/nsk/v1/payments/{$paymentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Apply any type of credit (agent function)
     * 
     * POST /api/nsk/v3/booking/payments/credit
     * For agents - can apply credit for any user or type
     * 
     * @param array $creditRequest Apply credit account request
     * @return array Credit application response
     * @throws JamboJetApiException
     */
    public function applyCredit(array $creditRequest): array
    {
        try {
            return $this->post('api/nsk/v3/booking/payments/credit', $creditRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to apply credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Verify payment status
     * 
     * GET /api/nsk/v6/payments/{paymentKey}/status
     * Check the current status of a payment transaction
     * 
     * @param string $paymentKey Payment identifier key
     * @return array Payment status information
     * @throws JamboJetApiException
     */
    public function verifyStatus(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->get("api/nsk/v6/payments/{$paymentKey}/status");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to verify payment status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process external payment
     * 
     * POST /api/nsk/v6/booking/payments/external
     * Process payments through external payment gateways
     * 
     * @param string $recordLocator Booking record locator
     * @param array $externalPaymentData External payment data
     * @return array External payment response
     * @throws JamboJetApiException
     */
    public function processExternalPayment(string $recordLocator, array $externalPaymentData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateExternalPaymentRequest($externalPaymentData);

        try {
            return $this->post("api/nsk/v6/booking/payments/external", array_merge($externalPaymentData, [
                'recordLocator' => $recordLocator
            ]));
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'External payment processing failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get available payment methods
     * 
     * GET /api/nsk/v6/booking/payments/methods
     * Retrieve available payment methods for the current booking context
     * 
     * @param string $recordLocator Booking record locator
     * @param array $criteria Optional criteria for filtering methods
     * @return array Available payment methods
     * @throws JamboJetApiException
     */
    public function getAvailablePaymentMethods(string $recordLocator, array $criteria = []): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validatePaymentMethodsCriteria($criteria);

        try {
            return $this->get('api/nsk/v6/booking/payments/methods', array_merge($criteria, [
                'recordLocator' => $recordLocator
            ]));
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve payment methods: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Quick credit card payment
     * 
     * Convenience method for simple credit card payments
     * 
     * @param float $amount Payment amount
     * @param string $currencyCode Currency code
     * @param array $cardData Credit card data
     * @return array Payment response
     * @throws JamboJetApiException
     */
    public function quickCreditCardPayment(float $amount, string $currencyCode, array $cardData): array
    {
        $this->validateRequired($cardData, ['accountNumber', 'expirationDate', 'accountHolderName']);

        $paymentData = [
            'amount' => $amount,
            'currencyCode' => $currencyCode,
            'paymentFields' => [
                'ACCTNO' => $cardData['accountNumber'],
                'EXPDATE' => $cardData['expirationDate'],
                'CC::AccountHolderName' => $cardData['accountHolderName']
            ]
        ];

        // Add CVV if provided
        if (isset($cardData['verificationCode'])) {
            $paymentData['paymentFields']['CC::VerificationCode'] = $cardData['verificationCode'];
        }

        // Add billing address if provided
        if (isset($cardData['billingAddress'])) {
            foreach ($cardData['billingAddress'] as $key => $value) {
                $paymentData['paymentFields']["CC::{$key}"] = $value;
            }
        }

        return $this->processPayment($paymentData);
    }

    /**
     * Get available customer credit
     * 
     * GET /api/nsk/v3/booking/payments/customerCredit
     * Gets available customer credit for current user
     * 
     * @return array Available customer credit
     * @throws JamboJetApiException
     */
    public function getCustomerCredit(): array
    {
        try {
            return $this->get('api/nsk/v3/booking/payments/customerCredit');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get customer credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process installment payment setup
     * 
     * POST /api/nsk/v6/booking/payments/installments
     * Set up installment payment plans
     * 
     * @param string $recordLocator Booking record locator
     * @param array $installmentData Installment setup data
     * @return array Installment setup response
     * @throws JamboJetApiException
     */
    public function setupInstallments(string $recordLocator, array $installmentData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateInstallmentRequest($installmentData);

        try {
            return $this->post("api/nsk/v6/booking/payments/installments", array_merge($installmentData, [
                'recordLocator' => $recordLocator
            ]));
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Installment setup failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Validate payment request
     */
    protected function validatePaymentRequest(array $paymentData): void
    {
        $this->validateRequired($paymentData, ['amount', 'currencyCode']);

        if (!is_numeric($paymentData['amount']) || $paymentData['amount'] <= 0) {
            throw new JamboJetValidationException('Amount must be a positive number');
        }

        if (strlen($paymentData['currencyCode']) !== 3) {
            throw new JamboJetValidationException('Currency code must be 3 characters');
        }
    }


    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND UPDATED
    // =================================================================

    /**
     * Validate payment processing request
     * 
     * @param array $data Payment data to validate
     * @throws JamboJetValidationException
     */
    private function validatePaymentProcessRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['paymentMethodType', 'amount', 'currencyCode']);

        // Validate payment method type
        $allowedPaymentMethods = [
            'ExternalAccount',
            'CustomerAccount',
            'AgencyAccount',
            'Voucher',
            'Loyalty',
            'Cash',
            'PrePaid',
            'MobileMoney'
        ];

        if (!in_array($data['paymentMethodType'], $allowedPaymentMethods)) {
            throw new JamboJetValidationException(
                'Invalid payment method type. Expected one of: ' . implode(', ', $allowedPaymentMethods),
                400
            );
        }

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new JamboJetValidationException(
                'Payment amount must be a positive number',
                400
            );
        }

        // Validate currency code
        $this->validateFormats($data, ['currencyCode' => 'currency_code']);

        // Validate payment method specific details
        $this->validatePaymentMethodDetails($data['paymentMethodType'], $data);
    }

    /**
     * Validate refund request
     * 
     * @param array $data Refund data to validate
     * @throws JamboJetValidationException
     */
    private function validateRefundRequest(array $data): void
    {
        // Validate required fields for refund
        $this->validateRequired($data, ['amount', 'currencyCode']);

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new JamboJetValidationException(
                'Refund amount must be a positive number',
                400
            );
        }

        // Validate currency code
        $this->validateFormats($data, ['currencyCode' => 'currency_code']);

        // Validate refund method if provided
        if (isset($data['refundMethod'])) {
            $allowedRefundMethods = ['OriginalPaymentMethod', 'CustomerCredit', 'OrganizationCredit'];
            if (!in_array($data['refundMethod'], $allowedRefundMethods)) {
                throw new JamboJetValidationException(
                    'Invalid refund method. Expected one of: ' . implode(', ', $allowedRefundMethods),
                    400
                );
            }
        }
    }

    /**
     * Validate external payment request
     * 
     * @throws JamboJetValidationException
     */
    private function validateExternalPaymentRequest(array $data): void
    {
        // Validate required fields for external payment
        $this->validateRequired($data, ['paymentGateway', 'externalTransactionId', 'amount', 'currencyCode']);

        // Validate payment gateway
        $validGateways = ['PayPal', 'Stripe', 'M-Pesa', 'Airtel', 'MTN', 'Equity', 'KCB', 'Flutterwave'];
        if (!in_array($data['paymentGateway'], $validGateways)) {
            throw new JamboJetValidationException(
                'Invalid payment gateway. Expected one of: ' . implode(', ', $validGateways),
                400
            );
        }

        // Validate external transaction ID
        $this->validateStringLengths($data, ['externalTransactionId' => ['min' => 5, 'max' => 100]]);

        // Validate amount and currency
        $this->validateFormats($data, [
            'amount' => 'positive_number',
            'currencyCode' => 'currency_code'
        ]);

        // Validate payment status if provided
        if (isset($data['paymentStatus'])) {
            $validStatuses = ['Pending', 'Completed', 'Failed', 'Cancelled', 'Authorized'];
            if (!in_array($data['paymentStatus'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid payment status. Expected one of: ' . implode(', ', $validStatuses),
                    400
                );
            }
        }

        // Validate gateway response data if provided
        if (isset($data['gatewayResponse'])) {
            $this->validateGatewayResponseData($data['gatewayResponse']);
        }

        // Validate customer information from gateway
        if (isset($data['customerInfo'])) {
            $this->validateExternalCustomerInfo($data['customerInfo']);
        }
    }

    /**
     * Validate installment request
     * 
     * @param array $data Installment data
     * @throws JamboJetValidationException
     */
    private function validateInstallmentRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['numberOfInstallments', 'firstInstallmentAmount']);

        // Validate number of installments
        $this->validateNumericRanges($data, ['numberOfInstallments' => ['min' => 2, 'max' => 24]]);

        if (!is_int($data['numberOfInstallments'])) {
            throw new JamboJetValidationException(
                'numberOfInstallments must be an integer',
                400
            );
        }

        // Validate first installment amount
        $this->validateFormats($data, ['firstInstallmentAmount' => 'positive_number']);

        // Validate installment frequency if provided
        if (isset($data['frequency'])) {
            $validFrequencies = ['Weekly', 'BiWeekly', 'Monthly', 'Quarterly'];
            if (!in_array($data['frequency'], $validFrequencies)) {
                throw new JamboJetValidationException(
                    'Invalid installment frequency. Expected one of: ' . implode(', ', $validFrequencies),
                    400
                );
            }
        }

        // Validate start date if provided
        if (isset($data['startDate'])) {
            $this->validateFormats($data, ['startDate' => 'date']);

            // Start date cannot be in the past
            $startDate = new \DateTime($data['startDate']);
            $now = new \DateTime();

            if ($startDate < $now) {
                throw new JamboJetValidationException(
                    'Installment start date cannot be in the past',
                    400
                );
            }
        }

        // Validate auto-payment configuration if provided
        if (isset($data['autoPayment'])) {
            $this->validateAutoPaymentConfiguration($data['autoPayment']);
        }
    }

    /**
     * Validate payment methods criteria
     * 
     * @param array $criteria Criteria for filtering payment methods
     * @throws JamboJetValidationException
     */
    private function validatePaymentMethodsCriteria(array $criteria): void
    {
        // Validate currency filter if provided
        if (isset($criteria['currencyCode'])) {
            $this->validateFormats($criteria, ['currencyCode' => 'currency_code']);
        }

        // Validate amount filter if provided
        if (isset($criteria['amount'])) {
            $this->validateFormats($criteria, ['amount' => 'positive_number']);
        }

        // Validate customer type filter if provided
        if (isset($criteria['customerType'])) {
            $validTypes = ['Individual', 'Business', 'Agent', 'Corporate'];
            if (!in_array($criteria['customerType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid customer type filter. Expected one of: ' . implode(', ', $validTypes),
                    400
                );
            }
        }

        // Validate country filter if provided
        if (isset($criteria['countryCode'])) {
            $this->validateFormats($criteria, ['countryCode' => 'country_code']);
        }
    }

    // =================================================================
    // HELPER VALIDATION METHODS FOR COMPLEX STRUCTURES
    // =================================================================

    /**
     * Validate payment method type
     * 
     * @param string $paymentMethodType Payment method type
     * @throws JamboJetValidationException
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
            'GiftCard',
            'CustomerCredit',
            'Installment',
            'BNPL',
            'Cryptocurrency'
        ];

        if (!in_array($paymentMethodType, $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid payment method type. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }
    }

    /**
     * Validate payment method specific details
     * 
     * @param string $paymentMethodType Payment method type
     * @param array $data Payment data
     * @throws JamboJetValidationException
     */
    private function validatePaymentMethodDetails(string $paymentMethodType, array $data): void
    {
        switch ($paymentMethodType) {
            case 'ExternalAccount':
                // Credit card validation
                if (!isset($data['paymentFields'])) {
                    throw new JamboJetValidationException('paymentFields required for ExternalAccount');
                }

                $requiredFields = ['AccountNumber', 'ExpirationMonth', 'ExpirationYear', 'HolderName'];
                foreach ($requiredFields as $field) {
                    if (!isset($data['paymentFields'][$field]) || empty($data['paymentFields'][$field])) {
                        throw new JamboJetValidationException("Missing required payment field: {$field}");
                    }
                }
                break;

            case 'MobileMoney':
                if (!isset($data['paymentDetails'])) {
                    throw new JamboJetValidationException('paymentDetails required for MobileMoney');
                }

                $requiredMobileFields = ['provider', 'phoneNumber'];
                foreach ($requiredMobileFields as $field) {
                    if (!isset($data['paymentDetails'][$field]) || empty($data['paymentDetails'][$field])) {
                        throw new JamboJetValidationException("Missing required mobile money field: {$field}");
                    }
                }
                break;

            case 'Voucher':
                if (!isset($data['paymentDetails']['voucherNumber']) || empty($data['paymentDetails']['voucherNumber'])) {
                    throw new JamboJetValidationException('Voucher number is required for Voucher payments');
                }
                break;

            case 'Loyalty':
                if (!isset($data['loyaltyProgram'])) {
                    throw new JamboJetValidationException('loyaltyProgram details required for Loyalty payments');
                }
                break;
        }
    }

    /**
     * Validate credit card details
     * 
     * @param array $details Credit card details
     * @throws JamboJetValidationException
     */
    private function validateCreditCardDetails(array $details): void
    {
        $this->validateRequired($details, ['cardNumber', 'expiryMonth', 'expiryYear', 'cvv', 'cardHolderName']);

        // Validate card number (remove spaces and validate length)
        $cardNumber = preg_replace('/\s+/', '', $details['cardNumber']);
        if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
            throw new JamboJetValidationException(
                'Invalid card number format. Must be 13-19 digits',
                400
            );
        }

        // Basic Luhn algorithm check
        if (!$this->validateLuhnChecksum($cardNumber)) {
            throw new JamboJetValidationException(
                'Invalid card number. Failed checksum validation',
                400
            );
        }

        // Validate expiry month
        $this->validateNumericRanges($details, ['expiryMonth' => ['min' => 1, 'max' => 12]]);

        // Validate expiry year
        $currentYear = (int) date('Y');
        $this->validateNumericRanges($details, ['expiryYear' => ['min' => $currentYear, 'max' => $currentYear + 20]]);

        // Check if card is not expired
        $currentMonth = (int) date('m');
        if ($details['expiryYear'] == $currentYear && $details['expiryMonth'] < $currentMonth) {
            throw new JamboJetValidationException(
                'Credit card has expired',
                400
            );
        }

        // Validate CVV
        if (!preg_match('/^\d{3,4}$/', $details['cvv'])) {
            throw new JamboJetValidationException(
                'Invalid CVV format. Must be 3 or 4 digits',
                400
            );
        }

        // Validate cardholder name
        $this->validateStringLengths($details, ['cardHolderName' => ['min' => 2, 'max' => 50]]);

        // Validate card type if provided
        if (isset($details['cardType'])) {
            $validCardTypes = ['Visa', 'MasterCard', 'Amex', 'Discover', 'JCB', 'DinersClub'];
            if (!in_array($details['cardType'], $validCardTypes)) {
                throw new JamboJetValidationException(
                    'Invalid card type. Expected one of: ' . implode(', ', $validCardTypes),
                    400
                );
            }
        }
    }

    /**
     * Validate mobile money details
     * 
     * @param array $details Mobile money details
     * @throws JamboJetValidationException
     */
    private function validateMobileMoneyDetails(array $details): void
    {
        $this->validateRequired($details, ['provider', 'phoneNumber']);

        // Validate provider
        $validProviders = ['M-Pesa', 'Airtel Money', 'MTN Mobile Money', 'T-Kash', 'Equitel'];
        if (!in_array($details['provider'], $validProviders)) {
            throw new JamboJetValidationException(
                'Invalid mobile money provider. Expected one of: ' . implode(', ', $validProviders),
                400
            );
        }

        // Validate phone number format (Kenya format)
        if (!preg_match('/^(\+254|0)[17]\d{8}$/', $details['phoneNumber'])) {
            throw new JamboJetValidationException(
                'Invalid mobile money phone number format. Expected Kenyan format',
                400
            );
        }

        // Validate account name if provided
        if (isset($details['accountName'])) {
            $this->validateStringLengths($details, ['accountName' => ['min' => 2, 'max' => 50]]);
        }
    }

    /**
     * Validate bank transfer details
     * 
     * @param array $details Bank transfer details
     * @throws JamboJetValidationException
     */
    private function validateBankTransferDetails(array $details): void
    {
        $this->validateRequired($details, ['bankCode', 'accountNumber', 'accountName']);

        // Validate bank code
        $this->validateStringLengths($details, ['bankCode' => ['min' => 2, 'max' => 20]]);

        // Validate account number
        $this->validateStringLengths($details, ['accountNumber' => ['min' => 5, 'max' => 30]]);

        // Validate account name
        $this->validateStringLengths($details, ['accountName' => ['min' => 2, 'max' => 100]]);

        // Validate branch code if provided
        if (isset($details['branchCode'])) {
            $this->validateStringLengths($details, ['branchCode' => ['max' => 20]]);
        }

        // Validate IBAN if provided
        if (isset($details['iban'])) {
            if (!preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{4,30}$/', $details['iban'])) {
                throw new JamboJetValidationException(
                    'Invalid IBAN format',
                    400
                );
            }
        }

        // Validate SWIFT code if provided
        if (isset($details['swiftCode'])) {
            if (!preg_match('/^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/', $details['swiftCode'])) {
                throw new JamboJetValidationException(
                    'Invalid SWIFT code format',
                    400
                );
            }
        }
    }

    /**
     * Validate PayPal details
     * 
     * @param array $details PayPal details
     * @throws JamboJetValidationException
     */
    private function validatePayPalDetails(array $details): void
    {
        // Validate PayPal email or PayPal ID
        if (!isset($details['paypalEmail']) && !isset($details['paypalId'])) {
            throw new JamboJetValidationException(
                'PayPal email or PayPal ID is required',
                400
            );
        }

        if (isset($details['paypalEmail'])) {
            $this->validateFormats($details, ['paypalEmail' => 'email']);
        }

        if (isset($details['paypalId'])) {
            $this->validateStringLengths($details, ['paypalId' => ['min' => 5, 'max' => 50]]);
        }

        // Validate return URLs if provided
        if (isset($details['returnUrl'])) {
            if (!filter_var($details['returnUrl'], FILTER_VALIDATE_URL)) {
                throw new JamboJetValidationException(
                    'Invalid PayPal return URL format',
                    400
                );
            }
        }

        if (isset($details['cancelUrl'])) {
            if (!filter_var($details['cancelUrl'], FILTER_VALIDATE_URL)) {
                throw new JamboJetValidationException(
                    'Invalid PayPal cancel URL format',
                    400
                );
            }
        }
    }

    /**
     * Validate voucher details
     * 
     * @param array $details Voucher details
     * @throws JamboJetValidationException
     */
    private function validateVoucherDetails(array $details): void
    {
        $this->validateRequired($details, ['voucherNumber']);

        $this->validateStringLengths($details, ['voucherNumber' => ['min' => 5, 'max' => 50]]);

        // Validate voucher PIN if provided
        if (isset($details['voucherPin'])) {
            $this->validateStringLengths($details, ['voucherPin' => ['min' => 3, 'max' => 20]]);
        }

        // Validate voucher type if provided
        if (isset($details['voucherType'])) {
            $validTypes = ['EMD', 'TravelVoucher', 'CreditVoucher', 'RefundVoucher', 'PromoVoucher'];
            if (!in_array($details['voucherType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid voucher type. Expected one of: ' . implode(', ', $validTypes),
                    400
                );
            }
        }
    }

    /**
     * Validate loyalty payment details
     * 
     * @param array $details Loyalty payment details
     * @throws JamboJetValidationException
     */
    private function validateLoyaltyPaymentDetails(array $details): void
    {
        $this->validateRequired($details, ['programCode', 'membershipNumber', 'pointsToRedeem']);

        $this->validateStringLengths($details, [
            'programCode' => ['max' => 10],
            'membershipNumber' => ['max' => 50]
        ]);

        $this->validateFormats($details, ['pointsToRedeem' => 'positive_number']);

        if (!is_int($details['pointsToRedeem'])) {
            throw new JamboJetValidationException(
                'Points to redeem must be an integer',
                400
            );
        }

        // Validate PIN if provided
        if (isset($details['loyaltyPin'])) {
            $this->validateStringLengths($details, ['loyaltyPin' => ['min' => 4, 'max' => 10]]);
        }
    }

    /**
     * Validate gift card details
     * 
     * @param array $details Gift card details
     * @throws JamboJetValidationException
     */
    private function validateGiftCardDetails(array $details): void
    {
        $this->validateRequired($details, ['giftCardNumber']);

        $this->validateStringLengths($details, ['giftCardNumber' => ['min' => 10, 'max' => 30]]);

        // Validate security code if provided
        if (isset($details['securityCode'])) {
            if (!preg_match('/^\d{3,8}$/', $details['securityCode'])) {
                throw new JamboJetValidationException(
                    'Invalid gift card security code format',
                    400
                );
            }
        }
    }

    /**
     * Validate customer credit details
     * 
     * @param array $details Customer credit details
     * @throws JamboJetValidationException
     */
    private function validateCustomerCreditDetails(array $details): void
    {
        $this->validateRequired($details, ['creditAmount']);

        $this->validateFormats($details, ['creditAmount' => 'positive_number']);

        // Validate credit type if provided
        if (isset($details['creditType'])) {
            $validTypes = ['Refund', 'Compensation', 'Promotional', 'Loyalty', 'Voucher'];
            if (!in_array($details['creditType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid customer credit type. Expected one of: ' . implode(', ', $validTypes),
                    400
                );
            }
        }

        // Validate credit reference if provided
        if (isset($details['creditReference'])) {
            $this->validateStringLengths($details, ['creditReference' => ['max' => 50]]);
        }
    }

    /**
     * Validate billing address
     * 
     * @param array $address Billing address
     * @throws JamboJetValidationException
     */
    private function validateBillingAddress(array $address): void
    {
        // Validate required address fields
        $this->validateRequired($address, ['lineOne', 'city', 'countryCode']);

        // Validate country code
        $this->validateFormats($address, ['countryCode' => 'country_code']);

        // Validate address field lengths
        $this->validateStringLengths($address, [
            'lineOne' => ['max' => 100],
            'lineTwo' => ['max' => 100],
            'city' => ['max' => 50],
            'postalCode' => ['max' => 20],
            'provinceState' => ['max' => 50]
        ]);

        // Validate email if provided
        if (isset($address['email'])) {
            $this->validateFormats($address, ['email' => 'email']);
        }

        // Validate phone if provided
        if (isset($address['phone'])) {
            $this->validateFormats($address, ['phone' => 'phone']);
        }
    }

    /**
     * Validate installment configuration
     * 
     * @param array $config Installment configuration
     * @throws JamboJetValidationException
     */
    private function validateInstallmentConfiguration(array $config): void
    {
        $this->validateRequired($config, ['numberOfInstallments']);

        $this->validateNumericRanges($config, ['numberOfInstallments' => ['min' => 2, 'max' => 24]]);

        if (isset($config['installmentAmount'])) {
            $this->validateFormats($config, ['installmentAmount' => 'positive_number']);
        }

        if (isset($config['frequency'])) {
            $validFrequencies = ['Weekly', 'BiWeekly', 'Monthly', 'Quarterly'];
            if (!in_array($config['frequency'], $validFrequencies)) {
                throw new JamboJetValidationException(
                    'Invalid installment frequency. Expected one of: ' . implode(', ', $validFrequencies),
                    400
                );
            }
        }
    }

    /**
     * Validate fraud prevention data
     * 
     * @param array $data Fraud prevention data
     * @throws JamboJetValidationException
     */
    private function validateFraudPreventionData(array $data): void
    {
        // Validate device fingerprint if provided
        if (isset($data['deviceFingerprint'])) {
            $this->validateStringLengths($data, ['deviceFingerprint' => ['max' => 500]]);
        }

        // Validate IP address if provided
        if (isset($data['ipAddress'])) {
            if (!filter_var($data['ipAddress'], FILTER_VALIDATE_IP)) {
                throw new JamboJetValidationException(
                    'Invalid IP address format',
                    400
                );
            }
        }

        // Validate user agent if provided
        if (isset($data['userAgent'])) {
            $this->validateStringLengths($data, ['userAgent' => ['max' => 500]]);
        }

        // Validate session ID if provided
        if (isset($data['sessionId'])) {
            $this->validateStringLengths($data, ['sessionId' => ['max' => 100]]);
        }
    }

    /**
     * Validate 3D Secure data
     * 
     * @param array $data 3D Secure data
     * @throws JamboJetValidationException
     */
    private function validateThreeDSecureData(array $data): void
    {
        // Validate authentication status
        if (isset($data['authenticationStatus'])) {
            $validStatuses = ['Y', 'N', 'A', 'U', 'R'];
            if (!in_array($data['authenticationStatus'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid 3D Secure authentication status. Expected one of: ' . implode(', ', $validStatuses),
                    400
                );
            }
        }

        // Validate transaction ID
        if (isset($data['transactionId'])) {
            $this->validateStringLengths($data, ['transactionId' => ['max' => 100]]);
        }

        // Validate cavv (Cardholder Authentication Verification Value)
        if (isset($data['cavv'])) {
            $this->validateStringLengths($data, ['cavv' => ['max' => 50]]);
        }

        // Validate ECI (Electronic Commerce Indicator)
        if (isset($data['eci'])) {
            if (!preg_match('/^[0-9]{2}$/', $data['eci'])) {
                throw new JamboJetValidationException(
                    'Invalid ECI format. Must be 2 digits',
                    400
                );
            }
        }
    }

    /**
     * Validate Luhn checksum for credit card numbers
     * 
     * @param string $cardNumber Card number
     * @return bool Valid checksum
     */
    private function validateLuhnChecksum(string $cardNumber): bool
    {
        $sum = 0;
        $alternate = false;

        for ($i = strlen($cardNumber) - 1; $i >= 0; $i--) {
            $digit = (int) $cardNumber[$i];

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return ($sum % 10) === 0;
    }

    // Additional validation methods for refund and external payment components...

    /**
     * Validate refund account details
     */
    private function validateRefundAccountDetails(array $account): void
    {
        if (isset($account['accountType'])) {
            $validTypes = ['Checking', 'Savings', 'CreditCard', 'PayPal'];
            if (!in_array($account['accountType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid refund account type',
                    400
                );
            }
        }

        if (isset($account['accountNumber'])) {
            $this->validateStringLengths($account, ['accountNumber' => ['min' => 5, 'max' => 30]]);
        }
    }

    /**
     * Validate credit request
     * 
     * @param array $data Credit data to validate
     * @throws JamboJetValidationException
     */
    private function validateCreditRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['amount', 'currencyCode']);

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new JamboJetValidationException(
                'Credit amount must be a positive number',
                400
            );
        }

        // Validate currency code
        $this->validateFormats($data, ['currencyCode' => 'currency_code']);
    }

    /**
     * Validate refund segments
     */
    private function validateRefundSegments(array $segments): void
    {
        foreach ($segments as $index => $segment) {
            if (isset($segment['segmentKey']) && empty(trim($segment['segmentKey']))) {
                throw new JamboJetValidationException(
                    "Refund segment {$index} key cannot be empty",
                    400
                );
            }
        }
    }

    /**
     * Validate gateway response data
     */
    private function validateGatewayResponseData(array $response): void
    {
        if (isset($response['transactionStatus'])) {
            $validStatuses = ['Success', 'Failed', 'Pending', 'Cancelled'];
            if (!in_array($response['transactionStatus'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid gateway transaction status',
                    400
                );
            }
        }
    }

    /**
     * Validate external customer info
     */
    private function validateExternalCustomerInfo(array $customerInfo): void
    {
        if (isset($customerInfo['email'])) {
            $this->validateFormats($customerInfo, ['email' => 'email']);
        }

        if (isset($customerInfo['phone'])) {
            $this->validateFormats($customerInfo, ['phone' => 'phone']);
        }
    }

    /**
     * Validate auto-payment configuration
     */
    private function validateAutoPaymentConfiguration(array $config): void
    {
        if (isset($config['enabled']) && !is_bool($config['enabled'])) {
            throw new JamboJetValidationException(
                'Auto-payment enabled flag must be a boolean',
                400
            );
        }

        if (isset($config['paymentMethodKey'])) {
            $this->validateStringLengths($config, ['paymentMethodKey' => ['min' => 5, 'max' => 100]]);
        }
    }

    /**
     * Validate payment key
     * 
     * @param string $paymentKey Payment key to validate
     * @throws JamboJetValidationException
     */
    private function validatePaymentKey(string $paymentKey): void
    {
        if (empty(trim($paymentKey))) {
            throw new JamboJetValidationException(
                'Payment key is required',
                400
            );
        }

        // Payment keys are typically UUIDs or alphanumeric strings
        if (!preg_match('/^[A-Za-z0-9\-]{1,50}$/', $paymentKey)) {
            throw new JamboJetValidationException(
                'Invalid payment key format',
                400
            );
        }
    }

    /**
     * Validate record locator
     * 
     * @param string $recordLocator Record locator to validate
     * @throws JamboJetValidationException
     */
    private function validateRecordLocator(string $recordLocator): void
    {
        if (empty(trim($recordLocator))) {
            throw new JamboJetValidationException(
                'Record locator is required',
                400
            );
        }

        // Record locators are typically 6-character alphanumeric strings
        if (!preg_match('/^[A-Z0-9]{6}$/', strtoupper($recordLocator))) {
            throw new JamboJetValidationException(
                'Invalid record locator format. Expected 6-character alphanumeric string',
                400
            );
        }
    }

    /**
     * Validate customer key
     * 
     * @param string $customerKey Customer key
     * @throws JamboJetValidationException
     */
    private function validateCustomerKey(string $customerKey): void
    {
        if (empty(trim($customerKey))) {
            throw new JamboJetValidationException(
                'Customer key cannot be empty',
                400
            );
        }

        if (strlen($customerKey) < 5) {
            throw new JamboJetValidationException(
                'Invalid customer key format',
                400
            );
        }
    }

    /**
     * Get supported payment methods and operations
     * 
     * @return array Available payment methods and operations
     */
    public function getSupportedOperations(): array
    {
        return [
            'payment_processing' => [
                'standard_payment' => [
                    'method' => 'processPayment',
                    'description' => 'Process credit card or external payment',
                    'endpoint' => '/api/nsk/v6/booking/payments (POST)',
                    'supports_3ds' => true
                ],
                'stored_payment' => [
                    'method' => 'processStoredPayment',
                    'description' => 'Use stored payment method',
                    'endpoint' => '/api/nsk/v6/booking/payments/storedPayment/{key} (POST)',
                    'supports_3ds' => true
                ],
                'quick_credit_card' => [
                    'method' => 'quickCreditCardPayment',
                    'description' => 'Simplified credit card payment',
                    'endpoint' => 'Wrapper method',
                ]
            ],
            'credit_operations' => [
                'customer_credit' => [
                    'apply' => 'applyCustomerCredit',
                    'get_available' => 'getAvailableCustomerCredit',
                    'description' => 'Customer credit operations'
                ],
                'organization_credit' => [
                    'apply' => 'applyOrganizationCredit',
                    'get_available' => 'getAvailableOrganizationCredit',
                    'description' => 'Organization credit operations'
                ],
                'agent_credit' => [
                    'apply' => 'applyCredit',
                    'description' => 'Agent credit operations (any type)'
                ]
            ],
            'refund_operations' => [
                'standard_refund' => [
                    'method' => 'processRefund',
                    'description' => 'Standard refund processing',
                    'endpoint' => '/api/nsk/v4/booking/payments/refunds (POST)'
                ],
                'customer_credit_refund' => [
                    'method' => 'processCustomerCreditRefund',
                    'description' => 'Refund to customer credit',
                    'endpoint' => '/api/nsk/v3/booking/payments/refunds/customerCredit (POST)'
                ],
                'organization_credit_refund' => [
                    'method' => 'processOrganizationCreditRefund',
                    'description' => 'Refund to organization credit',
                    'endpoint' => '/api/nsk/v2/booking/payments/refunds/organizationCredit (POST)'
                ]
            ],
            'information_retrieval' => [
                'get_payments' => [
                    'method' => 'getPayments',
                    'description' => 'Get all payments for booking',
                    'stateless' => true
                ],
                'get_payment_by_key' => [
                    'method' => 'getPaymentByKey',
                    'description' => 'Get specific payment by key',
                    'stateless' => true
                ],
                'get_refunds' => [
                    'method' => 'getRefunds',
                    'description' => 'Get all refunds for booking',
                    'stateless' => true
                ],
                'get_refund_methods' => [
                    'method' => 'getAvailableRefundMethods',
                    'description' => 'Get available refund methods',
                    'stateful' => true
                ]
            ]
        ];
    }
}