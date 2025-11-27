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
     * Get available payment methods for booking in state
     * GET /api/nsk/v2/booking/payments/available
     */
    public function getAvailablePaymentMethodsV2(?string $currencyCode = null): array
    {
        if ($currencyCode !== null) {
            $this->validateFormats(['currencyCode' => $currencyCode], ['currencyCode' => 'currency_code']);
        }

        $params = $currencyCode ? ['currencyCode' => $currencyCode] : [];

        try {
            return $this->get('api/nsk/v2/booking/payments/available', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve available payment methods: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment allocations for booking in state
     * GET /api/nsk/v1/booking/payments/allocations
     */
    public function getPaymentAllocations(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/payments/allocations');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve payment allocations: ' . $e->getMessage(),
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
     * Apply voucher payment to booking
     * 
     * POST /api/nsk/v4/booking/payments/voucher
     * Creates a new voucher payment on the booking in state
     * 
     * @param array $voucherData Voucher payment request data
     * @return array Voucher payment response
     * @throws JamboJetApiException
     */
    public function applyVoucherPayment(array $voucherData): array
    {
        $this->validateVoucherPaymentRequest($voucherData);

        try {
            return $this->post('api/nsk/v4/booking/payments/voucher', $voucherData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Voucher payment failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get voucher information
     * 
     * GET /api/nsk/v3/booking/payments/voucher
     * Gets information about a specific voucher code and validates if it exists
     * 
     * @param string $referenceCode Voucher reference code
     * @param bool $overrideRestrictions Override voucher restrictions (default: false)
     * @return array Voucher information
     * @throws JamboJetApiException
     */
    public function getVoucherInfo(string $referenceCode, bool $overrideRestrictions = false): array
    {
        if (empty(trim($referenceCode))) {
            throw new JamboJetValidationException('Voucher reference code is required', 400);
        }

        if (strlen($referenceCode) < 1 || strlen($referenceCode) > 20) {
            throw new JamboJetValidationException(
                'Voucher reference code must be between 1 and 20 characters',
                400
            );
        }

        try {
            return $this->get('api/nsk/v3/booking/payments/voucher', [
                'ReferenceCode' => $referenceCode,
                'OverrideRestrictions' => $overrideRestrictions
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get voucher information: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete/reverse voucher payment
     * 
     * DELETE /api/nsk/v2/booking/payments/voucher/{voucherPaymentReference}
     * Reverses a voucher payment on the booking in state (refunds claimed amount)
     * 
     * @param string $voucherPaymentReference Voucher payment reference
     * @return array Delete response
     * @throws JamboJetApiException
     */
    public function deleteVoucherPayment(string $voucherPaymentReference): array
    {
        if (empty(trim($voucherPaymentReference))) {
            throw new JamboJetValidationException(
                'Voucher payment reference is required',
                400
            );
        }

        try {
            return $this->delete("api/nsk/v2/booking/payments/voucher/{$voucherPaymentReference}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete voucher payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Apply passenger-specific voucher payment
     * 
     * POST /api/nsk/v1/booking/payments/voucher/passenger/{passengerKey}
     * Creates a voucher payment for a specified passenger
     * 
     * @param string $passengerKey Passenger key
     * @param array $voucherData Voucher payment request data
     * @return array Voucher payment response
     * @throws JamboJetApiException
     */
    public function applyPassengerVoucherPayment(string $passengerKey, array $voucherData): array
    {
        $this->validatePassengerKey($passengerKey);
        $this->validateVoucherPaymentRequest($voucherData);

        try {
            return $this->post(
                "api/nsk/v1/booking/payments/voucher/passenger/{$passengerKey}",
                $voucherData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Passenger voucher payment failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }



    /**
     * Get booking credit available from a past booking
     * 
     * GET /api/nsk/v2/booking/payments/bookingCredit
     * Gets credit available from a past booking (same validation as booking retrieve)
     * 
     * @param string $recordLocator Record locator
     * @param string $currencyCode Currency code
     * @param string|null $emailAddress Email address of contact (optional)
     * @param string|null $origin Origin station code (optional)
     * @param string|null $firstName First name of passenger/contact (optional)
     * @param string|null $lastName Last name of passenger/contact (optional)
     * @return array Booking credit information
     * @throws JamboJetApiException
     */
    public function getBookingCredit(
        string $recordLocator,
        string $currencyCode,
        ?string $emailAddress = null,
        ?string $origin = null,
        ?string $firstName = null,
        ?string $lastName = null
    ): array {
        $this->validateRecordLocator($recordLocator);
        $this->validateFormats(['currencyCode' => $currencyCode], ['currencyCode' => 'currency_code']);

        $params = [
            'RecordLocator' => $recordLocator,
            'CurrencyCode' => $currencyCode
        ];

        if ($emailAddress) {
            $params['EmailAddress'] = $emailAddress;
        }
        if ($origin) {
            $params['Origin'] = $origin;
        }
        if ($firstName) {
            $params['FirstName'] = $firstName;
        }
        if ($lastName) {
            $params['LastName'] = $lastName;
        }

        try {
            return $this->get('api/nsk/v2/booking/payments/bookingCredit', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get credit by reference number and type (Agent function)
     * 
     * GET /api/nsk/v2/booking/payments/credit
     * Gets credit available by reference and type - for agents only
     * 
     * @param string $referenceNumber Account reference (record locator, customer number, or org code)
     * @param string $currencyCode Currency code
     * @param int $type Credit type: 0=Customer, 1=Booking, 2=Organization
     * @return array Credit account information
     * @throws JamboJetApiException
     */
    public function getCreditByReference(string $referenceNumber, string $currencyCode, int $type): array
    {
        if (empty(trim($referenceNumber))) {
            throw new JamboJetValidationException('Reference number is required', 400);
        }

        if (strlen($referenceNumber) < 1 || strlen($referenceNumber) > 20) {
            throw new JamboJetValidationException(
                'Reference number must be between 1 and 20 characters',
                400
            );
        }

        $this->validateFormats(['currencyCode' => $currencyCode], ['currencyCode' => 'currency_code']);

        if (!in_array($type, [0, 1, 2])) {
            throw new JamboJetValidationException(
                'Invalid credit type. Expected 0 (Customer), 1 (Booking), or 2 (Organization)',
                400
            );
        }

        try {
            return $this->get('api/nsk/v2/booking/payments/credit', [
                'ReferenceNumber' => $referenceNumber,
                'CurrencyCode' => $currencyCode,
                'Type' => $type
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get credit by reference: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process credit shell refund
     * 
     * POST /api/nsk/v1/booking/payments/refunds/creditShell
     * Creates a credit shell refund for the booking in state
     * 
     * @param array $refundData Credit shell refund request
     * @return array Credit shell refund response
     * @throws JamboJetApiException
     */
    public function processCreditShellRefund(array $refundData): array
    {
        $this->validateCreditShellRefundRequest($refundData);

        try {
            return $this->post('api/nsk/v1/booking/payments/refunds/creditShell', $refundData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Credit shell refund failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// PAYMENT DELETION & BACKWARD COMPATIBILITY (4 METHODS)
// =================================================================

    /**
     * Delete payment from booking
     * 
     * DELETE /api/nsk/v1/booking/payments/{paymentKey}
     * Deletes a payment from the booking in state
     * 
     * @param string $paymentKey Payment key
     * @return array Delete response
     * @throws JamboJetApiException
     */
    public function deletePayment(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->delete("api/nsk/v1/booking/payments/{$paymentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payments for booking (v1 - legacy version)
     * 
     * GET /api/nsk/v1/booking/payments
     * Legacy endpoint for getting payments (v6 is recommended)
     * 
     * @return array Payment information
     * @throws JamboJetApiException
     */
    public function getPaymentsV1(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/payments');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payments (v1): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific payment by key (v1 - legacy version)
     * 
     * GET /api/nsk/v1/booking/payments/{paymentKey}
     * Legacy endpoint for getting payment details (v6 is recommended)
     * 
     * @param string $paymentKey Payment key
     * @return array Payment details
     * @throws JamboJetApiException
     */
    public function getPaymentByKeyV1(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->get("api/nsk/v1/booking/payments/{$paymentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment (v1): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process refund (v5 - newer version without credit shell support)
     * 
     * POST /api/nsk/v5/booking/payments/refunds
     * No longer handles Credit Shell refunds (use dedicated endpoint)
     * 
     * @param array $refundData Refund request data
     * @return array Refund response
     * @throws JamboJetApiException
     */
    public function processRefundV5(array $refundData): array
    {
        $this->validateRefundRequest($refundData);

        try {
            return $this->post('api/nsk/v5/booking/payments/refunds', $refundData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Refund processing (v5) failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    //  CRITICAL PAYMENT SECURITY & MANAGEMENT
    // =================================================================
    /**
     * Add payment to blacklist
     * 
     * POST /api/nsk/v1/booking/payments/blacklist
     * Blacklists a payment method to prevent fraud and chargebacks.
     * 
     * Blacklist Reason Codes:
     * - 0 = None
     * - 1 = ChargedBack
     * - 2 = Lost
     * - 3 = Other
     * - 4 = ProvidedByBank
     * - 5 = Stolen
     * 
     * @param array $blacklistData Blacklist entry with paymentKey, reason, dates, notes
     * @return array Blacklist creation response
     * @throws JamboJetApiException
     */
    public function addPaymentToBlacklist(array $blacklistData): array
    {
        $this->validateBlacklistRequest($blacklistData);

        try {
            return $this->post('api/nsk/v1/booking/payments/blacklist', $blacklistData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add payment to blacklist: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get blacklisted payment details
     * 
     * GET /api/nsk/v2/booking/payments/blacklist/{paymentKey}
     * Retrieves details of a specific blacklisted payment.
     * 
     * @param string $paymentKey Payment key to retrieve
     * @return array Blacklisted payment details (reason, dates, notes)
     * @throws JamboJetApiException
     */
    public function getBlacklistedPayment(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->get("api/nsk/v2/booking/payments/blacklist/{$paymentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get blacklisted payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Modify blacklisted payment
     * 
     * PATCH /api/nsk/v1/booking/payments/blacklist/{paymentKey}
     * Updates blacklist entry details (reason, dates, notes).
     * 
     * Supports partial updates using delta mapper pattern.
     * Only fields provided in patchData will be updated.
     * 
     * @param string $paymentKey Payment key to modify
     * @param array $patchData Fields to update (reason, notes, startDate, endDate)
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function modifyBlacklistedPayment(string $paymentKey, array $patchData): array
    {
        $this->validatePaymentKey($paymentKey);
        $this->validateBlacklistPatchRequest($patchData);

        try {
            return $this->patch("api/nsk/v1/booking/payments/blacklist/{$paymentKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to modify blacklisted payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove payment from blacklist
     * 
     * DELETE /api/nsk/v1/booking/payments/blacklist/{paymentKey}
     * Removes a payment from the blacklist, allowing it to be used again.
     * 
     * @param string $paymentKey Payment key to remove from blacklist
     * @return array Deletion response
     * @throws JamboJetApiException
     */
    public function removePaymentFromBlacklist(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->delete("api/nsk/v1/booking/payments/blacklist/{$paymentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove payment from blacklist: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Manually authorize a committed payment
     * 
     * PATCH /api/nsk/v2/booking/payments/{paymentKey}/authorize
     * Manual authorization for payments requiring review or approval.
     * 
     * Use Cases:
     * - Manual review of high-value transactions
     * - Offline authorization codes from payment processor
     * - Override declined transactions with approval
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $paymentKey Payment key to authorize
     * @param string $authorizationCode Authorization code from payment processor
     * @return array Authorization response
     * @throws JamboJetApiException
     */
    public function manuallyAuthorizePayment(string $paymentKey, string $authorizationCode): array
    {
        $this->validatePaymentKey($paymentKey);
        $this->validateAuthorizationCode($authorizationCode);

        try {
            return $this->patch(
                "api/nsk/v2/booking/payments/{$paymentKey}/authorize",
                ['authorizationCode' => $authorizationCode], // Body is now an array
                []
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to authorize payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Manually decline a committed payment
     * 
     * PATCH /api/nsk/v2/booking/payments/{paymentKey}/decline
     * Manually decline a payment that was committed.
     * 
     * WARNING: Use with caution. Only permissible under certain circumstances:
     * - Suspected fraud after manual review
     * - Payment processor issues requiring manual intervention
     * - Compliance or regulatory requirements
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $paymentKey Payment key to decline
     * @return array Decline response
     * @throws JamboJetApiException
     */
    public function manuallyDeclinePayment(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->patch("api/nsk/v2/booking/payments/{$paymentKey}/decline", []);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to decline payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Cancel in-process payment
     * 
     * DELETE /api/nsk/v1/booking/payments/inProcess
     * Cancels a payment currently in process (3DS, MCC, or DCC).
     * 
     * Use Cases:
     * - User abandons 3D Secure authentication
     * - Currency conversion offer declined
     * - Payment process interrupted or timed out
     * - User wants to use different payment method
     * 
     * Removes the in-process payment from session state.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @return array Cancellation response
     * @throws JamboJetApiException
     */
    public function cancelInProcessPayment(): array
    {
        try {
            return $this->delete('api/nsk/v1/booking/payments/inProcess');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to cancel in-process payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get in-process payment details
     * 
     * GET /api/nsk/v1/booking/payments/inProcess
     * Retrieves details of current in-process payment if one exists.
     * 
     * Use Cases:
     * - Check if payment is awaiting 3DS authentication
     * - Verify currency conversion status
     * - Resume interrupted payment flow
     * - Display payment status to user
     * 
     * Returns null if no in-process payment exists in session.
     * 
     * @return array In-process payment details or null
     * @throws JamboJetApiException
     */
    public function getInProcessPayment(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/payments/inProcess');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get in-process payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // =================================================================
    //  ADVANCED PAYMENT METHODS - DCC/MCC/3DS/FEES
    // =================================================================

    /**
     * Get DCC (Direct Currency Conversion) offer
     * 
     * POST /api/nsk/v1/booking/payments/{paymentMethod}/dcc
     * Gets available direct currency conversion offer for inline DCC payment.
     * 
     * DCC allows customers to see and pay in their home currency,
     * with the exchange rate locked at time of transaction.
     * 
     * Use Cases:
     * - International credit card payments
     * - Display exact amount in customer's currency
     * - Provide currency choice at checkout
     * 
     * @param string $paymentMethod Payment method code (e.g., 'MC', 'VI', 'AX')
     * @param array $dccRequest DCC request with amount, currencies, payment fields
     * @return array DCC offer with exchange rate, converted amount, and transaction key
     * @throws JamboJetApiException
     */
    public function getDccOffer(string $paymentMethod, array $dccRequest): array
    {
        if (empty(trim($paymentMethod))) {
            throw new JamboJetValidationException(
                'Payment method is required',
                400
            );
        }

        $this->validateDccRequest($dccRequest);

        try {
            return $this->post("api/nsk/v1/booking/payments/{$paymentMethod}/dcc", $dccRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get DCC offer: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process DCC payment
     * 
     * POST /api/nsk/v1/booking/payments/dcc/{dccPaymentTransactionKey}
     * Processes a DCC payment using transaction key from previous offer.
     * 
     * Process Flow:
     * 1. Get DCC offer using getDccOffer()
     * 2. Present offer to customer
     * 3. Process payment with this method if customer accepts
     * 
     * May return 202 status with 3DS authentication required.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $dccPaymentTransactionKey DCC transaction key from offer
     * @return array Payment response (may include 3DS authentication data)
     * @throws JamboJetApiException
     */
    public function processDccPayment(string $dccPaymentTransactionKey): array
    {
        $this->validateTransactionKey($dccPaymentTransactionKey);

        try {
            return $this->post("api/nsk/v1/booking/payments/dcc/{$dccPaymentTransactionKey}", []);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to process DCC payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get DCC exchange rates
     * 
     * GET /api/nsk/v1/booking/payments/dcc/rates
     * Retrieves current DCC exchange rates for currency conversion.
     * 
     * Use Cases:
     * - Display exchange rates before payment
     * - Calculate estimated amounts
     * - Compare with other conversion methods
     * 
     * @param string $fromCurrency Source currency code (3-letter ISO)
     * @param string $toCurrency Target currency code (3-letter ISO)
     * @return array Current exchange rates and markup information
     * @throws JamboJetApiException
     */
    public function getDccRates(string $fromCurrency, string $toCurrency): array
    {
        $this->validateFormats([
            'fromCurrency' => $fromCurrency,
            'toCurrency' => $toCurrency
        ], [
            'fromCurrency' => 'currency_code',
            'toCurrency' => 'currency_code'
        ]);

        try {
            return $this->get('api/nsk/v1/booking/payments/dcc/rates', [
                'from' => $fromCurrency,
                'to' => $toCurrency
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get DCC rates: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// ==================== MCC (MULTI-CURRENCY CONVERSION) ====================

    /**
     * Get MCC (Multi-Currency Conversion) availability
     * 
     * GET /api/nsk/v3/booking/payments/mcc
     * Gets available multi-currency codes for the booking.
     * 
     * Returns dictionary of available currencies with:
     * - Currency codes and names
     * - Exchange rates
     * - Processing fees
     * 
     * Affected by booking currency code.
     * 
     * @return array Available MCC currencies with rates and fees
     * @throws JamboJetApiException
     */
    public function getMccAvailability(): array
    {
        try {
            return $this->get('api/nsk/v3/booking/payments/mcc');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get MCC availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process MCC payment
     * 
     * POST /api/nsk/v6/booking/payments/mcc/{currencyCode}
     * Creates a new MCC payment in the specified currency.
     * 
     * MCC Process:
     * - Customer pays in their preferred currency
     * - Exchange rate automatically populated (normal scenario)
     * - Manual rate only for special circumstances
     * 
     * Requirements:
     * - MCC currency cannot match booking currency
     * - User-Agent and Accept headers required for 3DS
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $currencyCode Collected currency code (3-letter ISO)
     * @param array $mccData MCC payment request (payment method, fields, etc.)
     * @param string|null $termUrl 3DS term URL if 3DS required
     * @return array Payment response (may include 3DS or DCC offer)
     * @throws JamboJetApiException
     */
    public function processMccPayment(
        string $currencyCode,
        array $mccData,
        ?string $termUrl = null
    ): array {
        $this->validateFormats(['currencyCode' => $currencyCode], ['currencyCode' => 'currency_code']);
        $this->validateMccRequest($mccData);

        try {
            $endpoint = "api/nsk/v6/booking/payments/mcc/{$currencyCode}";

            if ($termUrl) {
                $endpoint .= "?termUrl=" . urlencode($termUrl);
            }

            return $this->post($endpoint, $mccData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to process MCC payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Process MCC payment with stored payment
     * 
     * POST /api/nsk/v6/booking/payments/mcc/{currencyCode}/storedPayment/{storedPaymentKey}
     * Creates MCC payment using stored payment information.
     * 
     * Use Cases:
     * - Repeat customers paying in home currency
     * - Stored cards with currency conversion
     * - Streamlined international checkout
     * 
     * Requirements:
     * - Cannot use same currency as booking
     * - May require CVV even for stored payment
     * - Exchange rate auto-populated normally
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $currencyCode Collected currency code
     * @param string $storedPaymentKey Stored payment key
     * @param array $mccData MCC payment data (may need CVV, billing address)
     * @param string|null $termUrl 3DS term URL if required
     * @return array Payment response (may include 3DS authentication)
     * @throws JamboJetApiException
     */
    public function processMccStoredPayment(
        string $currencyCode,
        string $storedPaymentKey,
        array $mccData,
        ?string $termUrl = null
    ): array {
        $this->validateFormats(['currencyCode' => $currencyCode], ['currencyCode' => 'currency_code']);

        if (empty(trim($storedPaymentKey))) {
            throw new JamboJetValidationException('Stored payment key is required', 400);
        }

        $this->validateMccStoredPaymentRequest($mccData);

        try {
            $endpoint = "api/nsk/v6/booking/payments/mcc/{$currencyCode}/storedPayment/{$storedPaymentKey}";

            if ($termUrl) {
                $endpoint .= "?termUrl=" . urlencode($termUrl);
            }

            return $this->post($endpoint, $mccData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to process MCC stored payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get MCC exchange rates
     * 
     * GET /api/nsk/v1/booking/payments/mcc/rates
     * Retrieves current MCC exchange rates for all available currencies.
     * 
     * Returns rates from booking currency to each available MCC currency.
     * 
     * @return array Dictionary of currency codes to exchange rates
     * @throws JamboJetApiException
     */
    public function getMccRates(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/payments/mcc/rates');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get MCC rates: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get MCC payment fees
     * 
     * GET /api/nsk/v2/booking/payments/mcc/fees
     * Retrieves MCC processing fees for a specific currency.
     * 
     * Fees may include:
     * - Currency conversion fee
     * - Processing fee
     * - Markup percentage
     * 
     * @param string $currencyCode Currency code to check fees for
     * @return array MCC fee breakdown
     * @throws JamboJetApiException
     */
    public function getMccFees(string $currencyCode): array
    {
        $this->validateFormats(['currencyCode' => $currencyCode], ['currencyCode' => 'currency_code']);

        try {
            return $this->get('api/nsk/v2/booking/payments/mcc/fees', [
                'currencyCode' => $currencyCode
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get MCC fees: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get MCC payment quote
     * 
     * POST /api/nsk/v1/booking/payments/mcc/quote
     * Gets a detailed quote for MCC payment showing final amount and fees.
     * 
     * Quote includes:
     * - Original amount in booking currency
     * - Converted amount in target currency
     * - Exchange rate applied
     * - All fees and charges
     * - Total amount to be charged
     * 
     * @param string $currencyCode Target currency code
     * @param float $amount Amount to convert
     * @return array Detailed MCC quote with breakdown
     * @throws JamboJetApiException
     */
    public function getMccQuote(string $currencyCode, float $amount): array
    {
        $this->validateFormats(['currencyCode' => $currencyCode], ['currencyCode' => 'currency_code']);
        $this->validateFormats(['amount' => $amount], ['amount' => 'positive_number']);

        try {
            return $this->post('api/nsk/v1/booking/payments/mcc/quote', [
                'currencyCode' => $currencyCode,
                'amount' => $amount
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get MCC quote: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// ==================== 3DS (THREE-DOMAIN SECURE) ====================

    /**
     * Initiate 3DS authentication
     * 
     * POST /api/nsk/v1/booking/payments/3ds/authenticate
     * Initiates 3D Secure authentication flow for payment.
     * 
     * 3DS Process Flow:
     * 1. Call this method with payment data
     * 2. Receive authentication URL and parameters (PaReq, MD)
     * 3. Redirect customer to issuer authentication page
     * 4. Customer completes authentication
     * 5. Issuer redirects back with PaRes
     * 6. Call complete3dsAuthentication() to finalize
     * 
     * Required for PCI DSS compliance in many regions.
     * 
     * @param array $authData 3DS authentication data (payment info, term URL)
     * @return array 3DS authentication response (URL, PaReq, MD, form HTML)
     * @throws JamboJetApiException
     */
    public function initiate3dsAuthentication(array $authData): array
    {
        $this->validate3dsRequest($authData);

        try {
            return $this->post('api/nsk/v1/booking/payments/3ds/authenticate', $authData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to initiate 3DS authentication: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Complete 3DS authentication
     * 
     * POST /api/nsk/v1/booking/payments/3ds/complete
     * Completes 3D Secure authentication after customer verification.
     * 
     * Called after customer completes authentication with their bank.
     * Issuer returns PaRes (Payment Authentication Response) to term URL.
     * 
     * Process:
     * 1. Receive PaRes and MD from issuer redirect
     * 2. Call this method to verify authentication
     * 3. If successful, payment is authorized
     * 
     * @param string $paRes Payment authentication response from issuer
     * @param string $md Merchant data passed through authentication
     * @return array Payment completion response
     * @throws JamboJetApiException
     */
    public function complete3dsAuthentication(string $paRes, string $md): array
    {
        $this->validate3dsCompletion($paRes, $md);

        try {
            return $this->post('api/nsk/v1/booking/payments/3ds/complete', [
                'paRes' => $paRes,
                'md' => $md
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to complete 3DS authentication: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get 3DS transaction status
     * 
     * GET /api/nsk/v1/booking/payments/3ds/status/{transactionKey}
     * Retrieves current status of a 3DS authentication transaction.
     * 
     * Status Values:
     * - Pending: Authentication initiated, awaiting customer
     * - Authenticated: Customer successfully authenticated
     * - Failed: Authentication failed
     * - NotEnrolled: Card not enrolled in 3DS
     * - Error: Technical error during process
     * 
     * @param string $transactionKey 3DS transaction key
     * @return array 3DS transaction status and details
     * @throws JamboJetApiException
     */
    public function get3dsStatus(string $transactionKey): array
    {
        $this->validateTransactionKey($transactionKey);

        try {
            return $this->get("api/nsk/v1/booking/payments/3ds/status/{$transactionKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get 3DS status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// ==================== PAYMENT FEES ====================

    /**
     * Get payment processing fee
     * 
     * GET /api/nsk/v1/booking/payments/fee
     * Calculates payment processing fee for a specific payment method.
     * 
     * Fee varies by:
     * - Payment method (credit card, debit, etc.)
     * - Currency
     * - Point of sale (web, mobile, agent, kiosk)
     * - Transaction amount (sometimes)
     * 
     * Use Cases:
     * - Display fees before payment
     * - Calculate total cost
     * - Compare payment method costs
     * 
     * @param string $paymentMethodCode Payment method code (e.g., 'MC', 'VI')
     * @param string $currencyCode Currency code (3-letter ISO)
     * @param string $pointOfSaleCode Point of sale code (e.g., 'WEB', 'MOB')
     * @return array Payment fee details (amount, percentage, fixed)
     * @throws JamboJetApiException
     */
    public function getPaymentFee(
        string $paymentMethodCode,
        string $currencyCode,
        string $pointOfSaleCode
    ): array {
        $this->validatePaymentFeeRequest([
            'paymentMethodCode' => $paymentMethodCode,
            'currencyCode' => $currencyCode,
            'pointOfSaleCode' => $pointOfSaleCode
        ]);

        try {
            return $this->get('api/nsk/v1/booking/payments/fee', [
                'paymentMethodCode' => $paymentMethodCode,
                'currencyCode' => $currencyCode,
                'pointOfSaleCode' => $pointOfSaleCode
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment fee: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all payment fees
     * 
     * GET /api/nsk/v2/booking/payments/fees
     * Retrieves all payment processing fees for available payment methods.
     * 
     * Returns dictionary of fees by payment method including:
     * - Fixed fees per transaction
     * - Percentage-based fees
     * - Minimum/maximum fees
     * - Currency-specific fees
     * 
     * @return array Dictionary of payment method codes to fee structures
     * @throws JamboJetApiException
     */
    public function getAllPaymentFees(): array
    {
        try {
            return $this->get('api/nsk/v2/booking/payments/fees');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get all payment fees: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Calculate payment fee
     * 
     * POST /api/nsk/v1/booking/payments/fee/calculate
     * Calculates exact fee for a payment transaction based on parameters.
     * 
     * More precise than getPaymentFee() as it considers:
     * - Exact transaction amount
     * - Current booking context
     * - Promotional discounts
     * - Fee caps and minimums
     * 
     * @param array $feeData Fee calculation parameters (method, amount, currency, etc.)
     * @return array Calculated fee breakdown with exact amounts
     * @throws JamboJetApiException
     */
    public function calculatePaymentFee(array $feeData): array
    {
        $this->validatePaymentFeeRequest($feeData);

        try {
            return $this->post('api/nsk/v1/booking/payments/fee/calculate', $feeData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to calculate payment fee: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// HELPER METHODS FOR OPERATIONS
// =================================================================

    /**
     * Process DCC payment with offer acceptance
     * 
     * Helper method that combines getting offer and processing payment.
     * Simplifies the DCC flow for common scenarios.
     * 
     * @param string $paymentMethod Payment method code
     * @param array $dccRequest DCC request data
     * @param bool $autoAccept Auto-accept offer without user interaction
     * @return array Combined offer and payment response
     * @throws JamboJetApiException
     */
    public function processDccPaymentWithOffer(
        string $paymentMethod,
        array $dccRequest,
        bool $autoAccept = false
    ): array {
        // Step 1: Get DCC offer
        $offer = $this->getDccOffer($paymentMethod, $dccRequest);

        if (!isset($offer['dccPaymentTransactionKey'])) {
            throw new JamboJetApiException(
                'DCC offer did not return transaction key',
                500
            );
        }

        // Step 2: Process payment with transaction key
        if ($autoAccept || isset($dccRequest['autoAccept'])) {
            return $this->processDccPayment($offer['dccPaymentTransactionKey']);
        }

        // Return offer for user acceptance
        return [
            'offer' => $offer,
            'transactionKey' => $offer['dccPaymentTransactionKey'],
            'acceptUrl' => "api/nsk/v1/booking/payments/dcc/{$offer['dccPaymentTransactionKey']}",
            'requiresAcceptance' => true
        ];
    }

    // =================================================================
    //  EXTENDED REFUND & CREDIT OPERATIONS
    // =================================================================

    /**
     * Reverse a payment
     * 
     * POST /api/nsk/v2/booking/payments/reversals
     * Reverses a payment on the booking (agent-only operation).
     * 
     * Differences from standard refund:
     * - Requires agent permissions
     * - Does NOT require zero/negative booking balance
     * - Can reverse even if balance is positive
     * - Used for corrections and chargebacks
     * 
     * Use Cases:
     * - Payment processing errors
     * - Duplicate payments
     * - Fraudulent transactions
     * - Manual corrections
     * 
     * NOTE: Credit shell (CS) refunds should use processCreditShellRefund() instead.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param array $reversalData Payment reversal request (paymentKey, amount, reason)
     * @return array Reversal confirmation
     * @throws JamboJetApiException
     */
    public function reversePayment(array $reversalData): array
    {
        $this->validateReversalRequest($reversalData);

        try {
            return $this->post('api/nsk/v2/booking/payments/reversals', $reversalData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to reverse payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get refund status
     * 
     * GET /api/nsk/v1/booking/payments/refunds/status
     * Retrieves status of a refund transaction.
     * 
     * Refund Status Values:
     * - Pending: Refund initiated, processing
     * - Approved: Refund approved, funds released
     * - Declined: Refund declined
     * - Processing: Being processed by payment processor
     * - Completed: Refund completed, funds returned
     * - Failed: Refund failed
     * 
     * @param string $refundKey Refund transaction key
     * @return array Refund status details with timeline
     * @throws JamboJetApiException
     */
    public function getRefundStatus(string $refundKey): array
    {
        if (empty(trim($refundKey))) {
            throw new JamboJetValidationException(
                'Refund key is required',
                400
            );
        }

        try {
            return $this->get('api/nsk/v1/booking/payments/refunds/status', [
                'refundKey' => $refundKey
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get refund status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// ==================== EXTENDED CREDIT MANAGEMENT ====================

    /**
     * Get booking credit (v3 enhanced)
     * 
     * GET /api/nsk/v3/booking/payments/bookingCredit
     * Enhanced version of booking credit retrieval with additional parameters.
     * 
     * Enhancements over v2:
     * - More detailed credit breakdown
     * - Expiration date information
     * - Credit restrictions and conditions
     * - Usage history
     * 
     * @param string $recordLocator Record locator of past booking
     * @param array $params Additional query parameters (firstName, lastName, origin, etc.)
     * @return array Enhanced booking credit information
     * @throws JamboJetApiException
     */
    public function getBookingCreditV3(string $recordLocator, array $params = []): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            $queryParams = array_merge(['recordLocator' => $recordLocator], $params);
            return $this->get('api/nsk/v3/booking/payments/bookingCredit', $queryParams);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking credit (v3): ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Apply booking credit to booking
     * 
     * POST /api/nsk/v2/booking/payments/bookingCredit
     * Applies credit from a past booking to the current booking in state.
     * 
     * Process:
     * 1. Retrieve booking to get credit available
     * 2. Load new booking into state
     * 3. Apply credit using this method
     * 
     * Credit Validation:
     * - Uses same rules as booking retrieve
     * - May require passenger names, origin, email
     * - Credit amount cannot exceed booking balance
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param array $creditData Booking credit application (recordLocator, amount, currencyCode)
     * @return array Credit application confirmation
     * @throws JamboJetApiException
     */
    public function applyBookingCredit(array $creditData): array
    {
        $this->validateBookingCreditRequest($creditData);

        try {
            return $this->post('api/nsk/v2/booking/payments/bookingCredit', $creditData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to apply booking credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove booking credit
     * 
     * DELETE /api/nsk/v2/booking/payments/bookingCredit/{creditKey}
     * Removes applied booking credit from the booking in state.
     * 
     * Use Cases:
     * - Customer changes mind
     * - Applied to wrong booking
     * - Need to use different credit
     * - Booking modifications require credit adjustment
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $creditKey Credit key to remove
     * @return array Removal confirmation
     * @throws JamboJetApiException
     */
    public function removeBookingCredit(string $creditKey): array
    {
        if (empty(trim($creditKey))) {
            throw new JamboJetValidationException(
                'Credit key is required',
                400
            );
        }

        try {
            return $this->delete("api/nsk/v2/booking/payments/bookingCredit/{$creditKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove booking credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get credit history
     * 
     * GET /api/nsk/v1/booking/payments/credit/history
     * Retrieves credit transaction history for an account.
     * 
     * History includes:
     * - Credit additions (refunds, compensations)
     * - Credit applications (payments)
     * - Credit transfers
     * - Credit expirations
     * - Credit adjustments
     * 
     * Credit Types:
     * - 0 = Customer Account
     * - 1 = Booking Credit
     * - 2 = Organization Account
     * 
     * @param string $referenceNumber Account reference (record locator, customer/org code)
     * @param int $type Credit type (0=Customer, 1=Booking, 2=Organization)
     * @return array Credit transaction history timeline
     * @throws JamboJetApiException
     */
    public function getCreditHistory(string $referenceNumber, int $type): array
    {
        if (empty(trim($referenceNumber))) {
            throw new JamboJetValidationException(
                'Reference number is required',
                400
            );
        }

        $validTypes = [0, 1, 2];
        if (!in_array($type, $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid credit type. Expected 0 (Customer), 1 (Booking), or 2 (Organization)',
                400
            );
        }

        try {
            return $this->get('api/nsk/v1/booking/payments/credit/history', [
                'referenceNumber' => $referenceNumber,
                'type' => $type
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get credit history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Transfer credit between accounts
     * 
     * POST /api/nsk/v1/booking/payments/credit/transfer
     * Transfers credit from one account to another (agent-only).
     * 
     * Transfer Rules:
     * - Requires agent permissions
     * - Cannot transfer expired credit
     * - Cannot exceed available balance
     * - May have currency conversion
     * - Requires business reason
     * 
     * Use Cases:
     * - Customer account consolidation
     * - Family/group bookings
     * - Corporate account management
     * - Customer service resolutions
     * 
     * @param string $fromAccount Source account reference
     * @param string $toAccount Destination account reference
     * @param array $transferData Transfer details (amount, currency, type, reason)
     * @return array Transfer confirmation with new balances
     * @throws JamboJetApiException
     */
    public function transferCredit(
        string $fromAccount,
        string $toAccount,
        array $transferData
    ): array {
        if (empty(trim($fromAccount))) {
            throw new JamboJetValidationException(
                'From account is required',
                400
            );
        }

        if (empty(trim($toAccount))) {
            throw new JamboJetValidationException(
                'To account is required',
                400
            );
        }

        if ($fromAccount === $toAccount) {
            throw new JamboJetValidationException(
                'Cannot transfer credit to same account',
                400
            );
        }

        $this->validateCreditTransferRequest($transferData);

        try {
            return $this->post('api/nsk/v1/booking/payments/credit/transfer', array_merge($transferData, [
                'fromAccount' => $fromAccount,
                'toAccount' => $toAccount
            ]));
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to transfer credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get credit balance
     * 
     * GET /api/nsk/v2/booking/payments/credit/balance
     * Retrieves current credit balance for an account.
     * 
     * Balance Information:
     * - Available credit amount
     * - Reserved/pending credit
     * - Expired credit
     * - Credit expiration dates
     * - Currency code
     * 
     * @param string $accountNumber Account number
     * @param string $currencyCode Currency code
     * @return array Credit balance information
     * @throws JamboJetApiException
     */
    public function getCreditBalance(string $accountNumber, string $currencyCode): array
    {
        if (empty(trim($accountNumber))) {
            throw new JamboJetValidationException(
                'Account number is required',
                400
            );
        }

        $this->validateFormats(['currencyCode' => $currencyCode], ['currencyCode' => 'currency_code']);

        try {
            return $this->get('api/nsk/v2/booking/payments/credit/balance', [
                'accountNumber' => $accountNumber,
                'currencyCode' => $currencyCode
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get credit balance: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Adjust credit balance
     * 
     * POST /api/nsk/v1/booking/payments/credit/adjustment
     * Makes manual adjustment to credit balance (agent-only).
     * 
     * Adjustment Types:
     * - Credit: Add credit to account
     * - Debit: Remove credit from account
     * - Correction: Fix incorrect balance
     * - Compensation: Customer service credit
     * - Manual: General manual adjustment
     * 
     * Requirements:
     * - Agent permissions required
     * - Must provide detailed reason
     * - Authorization code recommended
     * - Audit trail automatically created
     * 
     * Use Cases:
     * - Goodwill gestures
     * - Service recovery
     * - Accounting corrections
     * - System error corrections
     * 
     * @param string $accountNumber Account to adjust
     * @param array $adjustmentData Adjustment details (amount, type, reason, notes)
     * @return array Adjustment confirmation with new balance
     * @throws JamboJetApiException
     */
    public function adjustCredit(string $accountNumber, array $adjustmentData): array
    {
        if (empty(trim($accountNumber))) {
            throw new JamboJetValidationException(
                'Account number is required',
                400
            );
        }

        $this->validateCreditAdjustmentRequest($adjustmentData);

        try {
            return $this->post('api/nsk/v1/booking/payments/credit/adjustment', array_merge($adjustmentData, [
                'accountNumber' => $accountNumber
            ]));
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to adjust credit: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // =================================================================
    //  PAYMENT REPORTING & ANALYTICS
    // =================================================================

    /**
     * Get payment history for booking
     * 
     * GET /api/nsk/v1/booking/{recordLocator}/payments/history
     * Retrieves complete payment history for a booking.
     * 
     * History includes:
     * - All payment transactions
     * - Refunds and reversals
     * - Authorization attempts
     * - Status changes
     * - Timestamps for each event
     * 
     * Use Cases:
     * - Customer service inquiries
     * - Dispute resolution
     * - Accounting reconciliation
     * - Fraud investigation
     * 
     * @param string $recordLocator Booking record locator
     * @return array Payment history timeline with all transactions
     * @throws JamboJetApiException
     */
    public function getPaymentHistory(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v1/booking/{$recordLocator}/payments/history");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment transactions with filters
     * 
     * GET /api/nsk/v1/booking/payments/transactions
     * Retrieves payment transactions with advanced filtering.
     * 
     * Filter Options:
     * - Date range (startDate, endDate)
     * - Payment status (Pending, Approved, Declined, etc.)
     * - Payment method type
     * - Currency code
     * - Amount range (minAmount, maxAmount)
     * - Customer/organization filters
     * 
     * Use Cases:
     * - Financial reporting
     * - Payment analytics
     * - Reconciliation
     * - Trend analysis
     * 
     * @param array $filters Transaction filter criteria
     * @return array Filtered transaction list with pagination
     * @throws JamboJetApiException
     */
    public function getPaymentTransactions(array $filters = []): array
    {
        if (!empty($filters)) {
            $this->validateTransactionFilters($filters);
        }

        try {
            return $this->get('api/nsk/v1/booking/payments/transactions', $filters);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment transactions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment summary for booking
     * 
     * GET /api/nsk/v1/booking/payments/summary
     * Gets summarized payment information for a booking.
     * 
     * Summary includes:
     * - Total payments collected
     * - Total refunds issued
     * - Outstanding balance
     * - Payment breakdown by method
     * - Currency breakdown
     * - Fee totals
     * 
     * @param string $recordLocator Booking record locator
     * @return array Payment summary with totals and breakdown
     * @throws JamboJetApiException
     */
    public function getPaymentSummary(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get('api/nsk/v1/booking/payments/summary', [
                'recordLocator' => $recordLocator
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment summary: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment audit log
     * 
     * GET /api/nsk/v1/booking/payments/audit
     * Retrieves detailed audit log for a payment.
     * 
     * Audit Log includes:
     * - All modifications to payment
     * - Authorization attempts and results
     * - Status changes with timestamps
     * - User/agent who made changes
     * - System events (3DS, currency conversion)
     * - Security events (blacklist, decline)
     * 
     * Use Cases:
     * - Compliance requirements
     * - Fraud investigation
     * - Dispute resolution
     * - Internal audits
     * 
     * @param string $paymentKey Payment key
     * @return array Audit log entries with full details
     * @throws JamboJetApiException
     */
    public function getPaymentAuditLog(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->get('api/nsk/v1/booking/payments/audit', [
                'paymentKey' => $paymentKey
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment audit log: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment reconciliation data
     * 
     * GET /api/nsk/v1/booking/payments/reconciliation
     * Retrieves payment reconciliation data for accounting.
     * 
     * Reconciliation Data:
     * - All payments by date range
     * - Payment method breakdown
     * - Currency breakdown
     * - Fee totals
     * - Refund totals
     * - Net payment amounts
     * - Gateway/processor breakdown
     * 
     * Use Cases:
     * - End-of-day reconciliation
     * - Monthly/quarterly reporting
     * - Financial audits
     * - Revenue reporting
     * 
     * @param array $dateRange Date range (startDate, endDate)
     * @return array Reconciliation report with payment totals
     * @throws JamboJetApiException
     */
    public function getPaymentReconciliation(array $dateRange): array
    {
        $this->validateReconciliationDateRange($dateRange);

        try {
            return $this->get('api/nsk/v1/booking/payments/reconciliation', $dateRange);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment reconciliation: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// ==================== PAYMENT VERIFICATION & VALIDATION ====================

    /**
     * Validate payment data
     * 
     * POST /api/nsk/v1/booking/payments/validate
     * Validates payment data without processing payment.
     * 
     * Validation checks:
     * - Card number format (Luhn algorithm)
     * - Expiration date validity
     * - CVV format
     * - Billing address completeness
     * - Payment method support
     * - Currency compatibility
     * 
     * Use Cases:
     * - Pre-submit validation
     * - Form validation
     * - Payment method verification
     * - Testing payment flows
     * 
     * @param array $paymentData Payment data to validate
     * @return array Validation results with specific errors if any
     * @throws JamboJetApiException
     */
    public function validatePayment(array $paymentData): array
    {
        try {
            return $this->post('api/nsk/v1/booking/payments/validate', $paymentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to validate payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Verify payment status
     * 
     * POST /api/nsk/v1/booking/payments/verify
     * Verifies current payment status with payment processor.
     * 
     * Verification Process:
     * - Queries payment processor for current status
     * - Updates local payment record if needed
     * - Returns confirmed status
     * 
     * Use Cases:
     * - Payment status after delays
     * - Resolve payment discrepancies
     * - Confirm authorization
     * - Post-interrupt verification
     * 
     * @param string $paymentKey Payment key to verify
     * @return array Current verified payment status
     * @throws JamboJetApiException
     */
    public function verifyPaymentStatus(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->post('api/nsk/v1/booking/payments/verify', [
                'paymentKey' => $paymentKey
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to verify payment status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get detailed payment status
     * 
     * GET /api/nsk/v1/booking/payments/{paymentKey}/status
     * Retrieves detailed status information for a payment.
     * 
     * Status Details:
     * - Authorization status
     * - Transaction status
     * - Settlement status
     * - Gateway response codes
     * - Last update timestamp
     * - Next expected action
     * 
     * @param string $paymentKey Payment key
     * @return array Detailed payment status information
     * @throws JamboJetApiException
     */
    public function getPaymentStatus(string $paymentKey): array
    {
        $this->validatePaymentKey($paymentKey);

        try {
            return $this->get("api/nsk/v1/booking/payments/{$paymentKey}/status");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get payment status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Preauthorize payment
     * 
     * POST /api/nsk/v1/booking/payments/preauthorize
     * Creates a preauthorization (hold) on payment method.
     * 
     * Preauthorization Process:
     * 1. Validate payment method
     * 2. Check available funds
     * 3. Place hold (no funds captured)
     * 4. Return preauth key
     * 5. Capture later using capturePreauthorizedPayment()
     * 
     * Use Cases:
     * - Hotel-style bookings (pay at check-in)
     * - Deposit requirements
     * - Hold and capture workflows
     * - Delayed payment scenarios
     * 
     * Hold Duration:
     * - Typically 7-30 days depending on card issuer
     * - Must capture before hold expires
     * - Can capture partial amounts
     * 
     * @param array $preAuthData Preauthorization request (payment info, amount, hold duration)
     * @return array Preauthorization response with hold details
     * @throws JamboJetApiException
     */
    public function preauthorizePayment(array $preAuthData): array
    {
        $this->validatePreauthorizationRequest($preAuthData);

        try {
            return $this->post('api/nsk/v1/booking/payments/preauthorize', $preAuthData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to preauthorize payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Capture preauthorized payment
     * 
     * POST /api/nsk/v1/booking/payments/{paymentKey}/capture
     * Captures funds from a preauthorized payment.
     * 
     * Capture Process:
     * 1. Verify preauth still valid
     * 2. Validate capture amount
     * 3. Process capture
     * 4. Update payment status
     * 
     * Capture Rules:
     * - Must capture before hold expires
     * - Can capture full or partial amount
     * - Cannot exceed preauth amount
     * - Some processors allow multiple partial captures
     * 
     * Use Cases:
     * - Capture deposit after service
     * - Partial captures for split payments
     * - Final capture at checkout
     * 
     * @param string $paymentKey Payment key of preauthorization
     * @param float $amount Amount to capture (can be less than preauth)
     * @return array Capture confirmation with transaction details
     * @throws JamboJetApiException
     */
    public function capturePreauthorizedPayment(string $paymentKey, float $amount): array
    {
        $this->validatePaymentKey($paymentKey);
        $this->validateCaptureAmount($amount);

        try {
            return $this->post("api/nsk/v1/booking/payments/{$paymentKey}/capture", [
                'amount' => $amount
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to capture preauthorized payment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    

    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Generate payment analytics report
     * 
     * Helper method combining multiple reporting endpoints.
     * 
     * @param array $dateRange Date range for report
     * @param array $filters Additional filters
     * @return array Comprehensive analytics report
     * @throws JamboJetApiException
     */
    public function generatePaymentAnalyticsReport(array $dateRange, array $filters = []): array
    {
        $report = [
            'dateRange' => $dateRange,
            'generatedAt' => date('Y-m-d H:i:s')
        ];

        // Get reconciliation data
        try {
            $report['reconciliation'] = $this->getPaymentReconciliation($dateRange);
        } catch (\Exception $e) {
            $report['reconciliation'] = ['error' => $e->getMessage()];
        }

        // Get transactions
        try {
            $transactionFilters = array_merge($dateRange, $filters);
            $report['transactions'] = $this->getPaymentTransactions($transactionFilters);
        } catch (\Exception $e) {
            $report['transactions'] = ['error' => $e->getMessage()];
        }

        // Calculate summary statistics
        $report['summary'] = $this->calculatePaymentStatistics($report);

        return $report;
    }

    /**
     * Calculate payment statistics
     * 
     * Helper method to calculate payment statistics from transaction data.
     * 
     * @param array $reportData Report data with transactions
     * @return array Calculated statistics
     */
    private function calculatePaymentStatistics(array $reportData): array
    {
        $stats = [
            'totalTransactions' => 0,
            'totalAmount' => 0,
            'averageTransaction' => 0,
            'byPaymentMethod' => [],
            'byCurrency' => [],
            'byStatus' => []
        ];

        if (isset($reportData['transactions']['data'])) {
            $transactions = $reportData['transactions']['data'];
            $stats['totalTransactions'] = count($transactions);

            foreach ($transactions as $transaction) {
                $amount = $transaction['amount'] ?? 0;
                $stats['totalAmount'] += $amount;

                // By payment method
                $method = $transaction['paymentMethod'] ?? 'Unknown';
                $stats['byPaymentMethod'][$method] = ($stats['byPaymentMethod'][$method] ?? 0) + $amount;

                // By currency
                $currency = $transaction['currencyCode'] ?? 'Unknown';
                $stats['byCurrency'][$currency] = ($stats['byCurrency'][$currency] ?? 0) + $amount;

                // By status
                $status = $transaction['status'] ?? 'Unknown';
                $stats['byStatus'][$status] = ($stats['byStatus'][$status] ?? 0) + 1;
            }

            if ($stats['totalTransactions'] > 0) {
                $stats['averageTransaction'] = $stats['totalAmount'] / $stats['totalTransactions'];
            }
        }

        return $stats;
    }

    /**
     * Reverse multiple payments in bulk
     * 
     * Helper method for bulk payment reversals.
     * Useful for processing errors or fraud cleanup.
     * 
     * @param array $paymentKeys Array of payment keys to reverse
     * @param string $reason Reversal reason
     * @return array Bulk reversal results
     * @throws JamboJetApiException
     */
    public function bulkReversePayments(array $paymentKeys, string $reason): array
    {
        if (empty($paymentKeys)) {
            throw new JamboJetValidationException(
                'Payment keys array cannot be empty',
                400
            );
        }

        $results = [
            'successful' => [],
            'failed' => [],
            'summary' => [
                'total' => count($paymentKeys),
                'succeeded' => 0,
                'failed' => 0
            ]
        ];

        foreach ($paymentKeys as $paymentKey) {
            try {
                $response = $this->reversePayment([
                    'paymentKey' => $paymentKey,
                    'reasonCode' => $reason
                ]);

                $results['successful'][] = [
                    'paymentKey' => $paymentKey,
                    'response' => $response
                ];
                $results['summary']['succeeded']++;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'paymentKey' => $paymentKey,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
                $results['summary']['failed']++;
            }
        }

        return $results;
    }

    /**
     * Verify payment chain integrity
     * 
     * Helper method to verify payment->refund relationships.
     * 
     * @param string $recordLocator Booking record locator
     * @return array Chain verification results
     * @throws JamboJetApiException
     */
    public function verifyPaymentChainIntegrity(string $recordLocator): array
    {
        $history = $this->getPaymentHistory($recordLocator);

        $verification = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'summary' => [
                'totalPayments' => 0,
                'totalRefunds' => 0,
                'netAmount' => 0
            ]
        ];

        foreach ($history['data'] ?? [] as $entry) {
            if ($entry['type'] === 'Payment') {
                $verification['summary']['totalPayments'] += $entry['amount'] ?? 0;
            } elseif ($entry['type'] === 'Refund') {
                $verification['summary']['totalRefunds'] += $entry['amount'] ?? 0;
            }
        }

        $verification['summary']['netAmount'] =
            $verification['summary']['totalPayments'] -
            $verification['summary']['totalRefunds'];

        // Check for anomalies
        if ($verification['summary']['totalRefunds'] > $verification['summary']['totalPayments']) {
            $verification['warnings'][] = 'Refunds exceed payments';
        }

        return $verification;
    }

    /**
     * Export payment data for external systems
     * 
     * Helper method to format payment data for export.
     * 
     * @param array $transactions Transaction data
     * @param string $format Export format (csv, json, xml)
     * @return mixed Formatted export data
     * @throws JamboJetApiException
     */
    public function exportPaymentData(array $transactions, string $format = 'json'): mixed
    {
        $validFormats = ['json', 'csv', 'xml'];
        if (!in_array($format, $validFormats)) {
            throw new JamboJetValidationException(
                'Invalid export format. Expected: json, csv, or xml',
                400
            );
        }

        switch ($format) {
            case 'json':
                return json_encode($transactions, JSON_PRETTY_PRINT);

            case 'csv':
                return $this->convertToCsv($transactions);

            case 'xml':
                return $this->convertToXml($transactions);

            default:
                return $transactions;
        }
    }

    /**
     * Convert transactions to XML format
     * 
     * @param array $transactions Transaction data
     * @return string XML formatted data
     */
    private function convertToXml(array $transactions): string
    {
        return '';
    }

    /**
     * Convert transactions to CSV format
     * 
     * @param array $transactions Transaction data
     * @return string CSV formatted data
     */
    private function convertToCsv(array $transactions): string
    {
        if (empty($transactions)) {
            return '';
        }

        $csv = [];

        // Headers
        $headers = array_keys($transactions[0]);
        $csv[] = implode(',', $headers);

        // Data rows
        foreach ($transactions as $transaction) {
            $row = array_map(function ($value) {
                return '"' . str_replace('"', '""', $value) . '"';
            }, array_values($transaction));

            $csv[] = implode(',', $row);
        }

        return implode("\n", $csv);
    }



    /**
     * Get comprehensive credit summary
     * 
     * Helper method combining balance, history, and details.
     * 
     * @param string $accountNumber Account number
     * @param string $currencyCode Currency code
     * @param int $type Credit type
     * @return array Complete credit summary
     * @throws JamboJetApiException
     */
    public function getCompleteCreditSummary(
        string $accountNumber,
        string $currencyCode,
        int $type
    ): array {
        $summary = [
            'accountNumber' => $accountNumber,
            'currencyCode' => $currencyCode,
            'type' => $type
        ];

        // Get balance
        try {
            $summary['balance'] = $this->getCreditBalance($accountNumber, $currencyCode);
        } catch (\Exception $e) {
            $summary['balance'] = ['error' => $e->getMessage()];
        }

        // Get history
        try {
            $summary['history'] = $this->getCreditHistory($accountNumber, $type);
        } catch (\Exception $e) {
            $summary['history'] = ['error' => $e->getMessage()];
        }

        return $summary;
    }

    /**
     * Calculate credit transfer with conversion
     * 
     * Helper method to preview credit transfer with currency conversion.
     * 
     * @param string $fromCurrency Source currency
     * @param string $toCurrency Target currency
     * @param float $amount Amount to transfer
     * @return array Transfer preview with converted amount
     * @throws JamboJetApiException
     */
    public function previewCreditTransfer(
        string $fromCurrency,
        string $toCurrency,
        float $amount
    ): array {
        // If same currency, no conversion needed
        if ($fromCurrency === $toCurrency) {
            return [
                'originalAmount' => $amount,
                'originalCurrency' => $fromCurrency,
                'convertedAmount' => $amount,
                'convertedCurrency' => $toCurrency,
                'conversionRequired' => false,
                'exchangeRate' => 1.0
            ];
        }

        // Get exchange rate (using MCC rates as reference)
        try {
            $rates = $this->getMccRates();
            $rate = $rates[$toCurrency] ?? null;

            if (!$rate) {
                throw new JamboJetApiException(
                    "Exchange rate not available for {$toCurrency}",
                    404
                );
            }

            return [
                'originalAmount' => $amount,
                'originalCurrency' => $fromCurrency,
                'convertedAmount' => $amount * $rate,
                'convertedCurrency' => $toCurrency,
                'conversionRequired' => true,
                'exchangeRate' => $rate
            ];
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to preview credit transfer: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate credit can be applied
     * 
     * Helper method to check if credit can be applied before attempting.
     * 
     * @param string $recordLocator Booking with credit
     * @param float $creditAmount Credit amount to apply
     * @param float $bookingBalance Current booking balance
     * @return array Validation result with details
     */
    public function validateCreditApplication(
        string $recordLocator,
        float $creditAmount,
        float $bookingBalance
    ): array {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];

        // Check if credit exceeds balance
        if ($creditAmount > $bookingBalance) {
            $validation['warnings'][] = "Credit amount ({$creditAmount}) exceeds booking balance ({$bookingBalance})";
            $validation['suggestedAmount'] = $bookingBalance;
        }

        // Check if credit is zero or negative
        if ($creditAmount <= 0) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Credit amount must be greater than zero';
        }

        // Check record locator format
        if (!preg_match('/^[A-Z0-9]{6}$/', strtoupper($recordLocator))) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Invalid record locator format';
        }

        return $validation;
    }

    /**
     * Get MCC payment options with fees
     * 
     * Helper method combining availability and fees for all currencies.
     * 
     * @return array MCC options with rates, fees, and total costs
     * @throws JamboJetApiException
     */
    public function getMccPaymentOptions(): array
    {
        $availability = $this->getMccAvailability();
        $options = [];

        foreach ($availability['data'] ?? [] as $currencyCode => $currencyInfo) {
            try {
                $fees = $this->getMccFees($currencyCode);

                $options[$currencyCode] = array_merge($currencyInfo, [
                    'fees' => $fees,
                    'totalCost' => ($currencyInfo['rate'] ?? 1) + ($fees['amount'] ?? 0)
                ]);
            } catch (\Exception $e) {
                // Continue if fees not available for this currency
                $options[$currencyCode] = $currencyInfo;
            }
        }

        return $options;
    }

    /**
     * Compare DCC vs MCC rates
     * 
     * Helper method to compare conversion methods for customer.
     * 
     * @param string $targetCurrency Target currency code
     * @param float $amount Amount to convert
     * @return array Comparison of DCC vs MCC with costs
     * @throws JamboJetApiException
     */
    public function compareCurrencyConversionMethods(
        string $targetCurrency,
        float $amount
    ): array {
        $comparison = [
            'amount' => $amount,
            'targetCurrency' => $targetCurrency,
            'methods' => []
        ];

        // Get MCC quote
        try {
            $mccQuote = $this->getMccQuote($targetCurrency, $amount);
            $comparison['methods']['mcc'] = [
                'available' => true,
                'quote' => $mccQuote,
                'recommended' => true // MCC typically better rates
            ];
        } catch (\Exception $e) {
            $comparison['methods']['mcc'] = [
                'available' => false,
                'error' => $e->getMessage()
            ];
        }

        // Get DCC rates (simplified - would need payment method)
        try {
            $dccRates = $this->getDccRates('USD', $targetCurrency); // Assuming USD base
            $comparison['methods']['dcc'] = [
                'available' => true,
                'rates' => $dccRates
            ];
        } catch (\Exception $e) {
            $comparison['methods']['dcc'] = [
                'available' => false,
                'error' => $e->getMessage()
            ];
        }

        return $comparison;
    }

    /**
     * Get complete fee breakdown for payment
     * 
     * Helper method showing all fees for a payment.
     * 
     * @param string $paymentMethodCode Payment method
     * @param float $amount Transaction amount
     * @param string $currencyCode Currency
     * @return array Complete fee breakdown
     * @throws JamboJetApiException
     */
    public function getCompletePaymentFeeBreakdown(
        string $paymentMethodCode,
        float $amount,
        string $currencyCode
    ): array {
        // Get base fee
        $baseFee = $this->calculatePaymentFee([
            'paymentMethodCode' => $paymentMethodCode,
            'amount' => $amount,
            'currencyCode' => $currencyCode
        ]);

        return [
            'paymentMethod' => $paymentMethodCode,
            'amount' => $amount,
            'currency' => $currencyCode,
            'fees' => $baseFee,
            'total' => $amount + ($baseFee['amount'] ?? 0),
            'breakdown' => [
                'baseAmount' => $amount,
                'processingFee' => $baseFee['amount'] ?? 0,
                'totalCharge' => $amount + ($baseFee['amount'] ?? 0)
            ]
        ];
    }

    /**
     * Blacklist multiple payments in bulk
     * 
     * Helper method that blacklists multiple payment keys with same reason.
     * Not a direct API endpoint - uses multiple calls to addPaymentToBlacklist.
     * 
     * @param array $paymentKeys Array of payment keys to blacklist
     * @param int $reason Blacklist reason code (0-5)
     * @param string|null $notes Optional notes for all entries
     * @return array Results array with success/failure per payment
     * @throws JamboJetApiException
     */
    public function bulkBlacklistPayments(
        array $paymentKeys,
        int $reason = 0,
        ?string $notes = null
    ): array {
        if (empty($paymentKeys)) {
            throw new JamboJetValidationException(
                'Payment keys array cannot be empty',
                400
            );
        }

        $results = [
            'successful' => [],
            'failed' => [],
            'summary' => [
                'total' => count($paymentKeys),
                'succeeded' => 0,
                'failed' => 0
            ]
        ];

        foreach ($paymentKeys as $paymentKey) {
            try {
                $response = $this->addPaymentToBlacklist([
                    'paymentKey' => $paymentKey,
                    'reason' => $reason,
                    'notes' => $notes
                ]);

                $results['successful'][] = [
                    'paymentKey' => $paymentKey,
                    'response' => $response
                ];
                $results['summary']['succeeded']++;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'paymentKey' => $paymentKey,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
                $results['summary']['failed']++;
            }
        }

        return $results;
    }

    /**
     * Get all blacklisted payments for a booking
     * 
     * Helper method to retrieve all blacklisted payment information.
     * Useful for fraud review and payment investigation.
     * 
     * @param array $paymentKeys Array of payment keys to check
     * @return array Array of blacklist details for each payment
     * @throws JamboJetApiException
     */
    public function getAllBlacklistedPayments(array $paymentKeys): array
    {
        if (empty($paymentKeys)) {
            throw new JamboJetValidationException(
                'Payment keys array cannot be empty',
                400
            );
        }

        $blacklistedPayments = [];

        foreach ($paymentKeys as $paymentKey) {
            try {
                $details = $this->getBlacklistedPayment($paymentKey);
                $blacklistedPayments[] = array_merge(
                    ['paymentKey' => $paymentKey],
                    $details
                );
            } catch (\Exception $e) {
                // Payment might not be blacklisted - continue to next
                continue;
            }
        }

        return $blacklistedPayments;
    }

    /**
     * Check if payment is blacklisted
     * 
     * Helper method to quickly check blacklist status.
     * 
     * @param string $paymentKey Payment key to check
     * @return bool True if blacklisted, false otherwise
     */
    public function isPaymentBlacklisted(string $paymentKey): bool
    {
        try {
            $this->getBlacklistedPayment($paymentKey);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update blacklist expiration date
     * 
     * Helper method to extend or shorten blacklist period.
     * 
     * @param string $paymentKey Payment key
     * @param string $newEndDate New end date (ISO format)
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function updateBlacklistExpiration(string $paymentKey, string $newEndDate): array
    {
        return $this->modifyBlacklistedPayment($paymentKey, [
            'endDate' => $newEndDate
        ]);
    }

    /**
     * Remove blacklist expiration (permanent blacklist)
     * 
     * Helper method to make blacklist permanent by removing end date.
     * 
     * @param string $paymentKey Payment key
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function makeBlacklistPermanent(string $paymentKey): array
    {
        return $this->modifyBlacklistedPayment($paymentKey, [
            'endDate' => null
        ]);
    }

    /**
     * Check payment authorization status
     * 
     * Helper method to verify if payment needs manual authorization.
     * 
     * @param string $paymentKey Payment key
     * @return array Authorization status details
     * @throws JamboJetApiException
     */
    public function checkAuthorizationStatus(string $paymentKey): array
    {
        try {
            $payment = $this->getPaymentByKey($paymentKey);

            return [
                'paymentKey' => $paymentKey,
                'authorizationStatus' => $payment['authorizationStatus'] ?? 'Unknown',
                'requiresManualAuth' => in_array(
                    $payment['authorizationStatus'] ?? '',
                    ['Pending', 'Referral', 'ValidationFailed']
                ),
                'canBeAuthorized' => in_array(
                    $payment['authorizationStatus'] ?? '',
                    ['Pending', 'Referral', 'Declined']
                ),
                'canBeDeclined' => in_array(
                    $payment['authorizationStatus'] ?? '',
                    ['Approved', 'Pending', 'Referral']
                )
            ];
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to check authorization status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate blacklist payment request
     * 
     * @param array $data Blacklist request data
     * @throws JamboJetValidationException
     */
    private function validateBlacklistRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['paymentKey']);

        // Validate payment key format
        $this->validatePaymentKey($data['paymentKey']);

        // Validate reason code if provided
        if (isset($data['reason'])) {
            $validReasons = [0, 1, 2, 3, 4, 5]; // None, ChargedBack, Lost, Other, ProvidedByBank, Stolen
            if (!in_array($data['reason'], $validReasons)) {
                throw new JamboJetValidationException(
                    'Invalid blacklist reason code. Expected 0-5 (None, ChargedBack, Lost, Other, ProvidedByBank, Stolen)',
                    400
                );
            }
        }

        // Validate notes length if provided
        if (isset($data['notes'])) {
            $this->validateStringLengths($data, ['notes' => ['max' => 500]]);
        }

        // Validate dates if provided
        if (isset($data['startDate'])) {
            $this->validateFormats($data, ['startDate' => 'date']);
        }

        if (isset($data['endDate'])) {
            $this->validateFormats($data, ['endDate' => 'date']);

            // End date must be after start date
            if (isset($data['startDate'])) {
                $startDate = new \DateTime($data['startDate']);
                $endDate = new \DateTime($data['endDate']);

                if ($endDate <= $startDate) {
                    throw new JamboJetValidationException(
                        'Blacklist end date must be after start date',
                        400
                    );
                }
            }
        }
    }

    /**
     * Validate blacklist modification request
     * 
     * @param array $patchData Patch data
     * @throws JamboJetValidationException
     */
    private function validateBlacklistPatchRequest(array $patchData): void
    {
        if (empty($patchData)) {
            throw new JamboJetValidationException(
                'Blacklist patch data cannot be empty',
                400
            );
        }

        // Validate reason code if provided
        if (isset($patchData['reason'])) {
            $validReasons = [0, 1, 2, 3, 4, 5];
            if (!in_array($patchData['reason'], $validReasons)) {
                throw new JamboJetValidationException(
                    'Invalid blacklist reason code',
                    400
                );
            }
        }

        // Validate notes if provided
        if (isset($patchData['notes'])) {
            $this->validateStringLengths($patchData, ['notes' => ['max' => 500]]);
        }

        // Validate dates if provided
        if (isset($patchData['startDate'])) {
            $this->validateFormats($patchData, ['startDate' => 'date']);
        }

        if (isset($patchData['endDate'])) {
            $this->validateFormats($patchData, ['endDate' => 'date']);
        }
    }

    /**
     * Validate authorization code
     * 
     * @param string $authCode Authorization code
     * @throws JamboJetValidationException
     */
    private function validateAuthorizationCode(string $authCode): void
    {
        if (empty(trim($authCode))) {
            throw new JamboJetValidationException(
                'Authorization code is required',
                400
            );
        }

        // Authorization codes are typically 6-20 alphanumeric characters
        if (!preg_match('/^[A-Za-z0-9]{6,20}$/', $authCode)) {
            throw new JamboJetValidationException(
                'Invalid authorization code format. Expected 6-20 alphanumeric characters',
                400
            );
        }
    }

    // =================================================================
    //  DCC/MCC/3DS VALIDATION METHODS
    // =================================================================

    /**
     * Validate DCC (Direct Currency Conversion) request
     * 
     * @param array $data DCC request data
     * @throws JamboJetValidationException
     */
    private function validateDccRequest(array $data): void
    {
        // Validate required fields
        $requiredFields = ['amount', 'quotedCurrencyCode'];
        $this->validateRequired($data, $requiredFields);

        // Validate amount
        $this->validateFormats($data, ['amount' => 'positive_number']);

        // Validate currency codes
        $this->validateFormats($data, [
            'quotedCurrencyCode' => 'currency_code'
        ]);

        if (isset($data['collectedCurrencyCode'])) {
            $this->validateFormats($data, [
                'collectedCurrencyCode' => 'currency_code'
            ]);
        }

        // Validate payment fields if provided
        if (isset($data['paymentFields'])) {
            $this->validatePaymentFields($data['paymentFields']);
        }

        // Validate billing address if provided
        if (isset($data['billingAddress'])) {
            $this->validateBillingAddress($data['billingAddress']);
        }
    }

    /**
     * Validate MCC (Multi-Currency Conversion) request
     * 
     * @param array $data MCC request data
     * @throws JamboJetValidationException
     */
    private function validateMccRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['paymentMethodCode']);

        // Validate payment method code
        if (!preg_match('/^[A-Z0-9]{2,10}$/', $data['paymentMethodCode'])) {
            throw new JamboJetValidationException(
                'Invalid payment method code format',
                400
            );
        }

        // Validate amount if provided
        if (isset($data['amount'])) {
            $this->validateFormats($data, ['amount' => 'positive_number']);
        }

        // Validate payment fields if provided
        if (isset($data['paymentFields'])) {
            $this->validatePaymentFields($data['paymentFields']);
        }

        // Validate billing address if provided
        if (isset($data['billingAddress'])) {
            $this->validateBillingAddress($data['billingAddress']);
        }

        // Validate exchange rate if provided (manual MCC)
        if (isset($data['exchangeRate'])) {
            if (!is_numeric($data['exchangeRate']) || $data['exchangeRate'] <= 0) {
                throw new JamboJetValidationException(
                    'Exchange rate must be a positive number',
                    400
                );
            }
        }

        // Validate rate ID if provided
        if (isset($data['rateId'])) {
            if (empty(trim($data['rateId']))) {
                throw new JamboJetValidationException(
                    'Rate ID cannot be empty',
                    400
                );
            }
        }
    }

    /**
     * Validate MCC stored payment request
     * 
     * @param array $data MCC stored payment data
     * @throws JamboJetValidationException
     */
    private function validateMccStoredPaymentRequest(array $data): void
    {
        // Validate payment method code if provided
        if (isset($data['paymentMethodCode'])) {
            if (!preg_match('/^[A-Z0-9]{2,10}$/', $data['paymentMethodCode'])) {
                throw new JamboJetValidationException(
                    'Invalid payment method code format',
                    400
                );
            }
        }

        // Validate CVV if provided (often required for stored payments)
        if (isset($data['cvv'])) {
            if (!preg_match('/^[0-9]{3,4}$/', $data['cvv'])) {
                throw new JamboJetValidationException(
                    'CVV must be 3 or 4 digits',
                    400
                );
            }
        }

        // Validate billing address if provided
        if (isset($data['billingAddress'])) {
            $this->validateBillingAddress($data['billingAddress']);
        }
    }

    /**
     * Validate 3DS (Three-Domain Secure) authentication request
     * 
     * @param array $data 3DS authentication data
     * @throws JamboJetValidationException
     */
    private function validate3dsRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['termUrl']);

        // Validate term URL
        if (!filter_var($data['termUrl'], FILTER_VALIDATE_URL)) {
            throw new JamboJetValidationException(
                'Term URL must be a valid URL',
                400
            );
        }

        // Validate payment method type if provided
        if (isset($data['paymentMethodType'])) {
            $this->validatePaymentMethodType($data['paymentMethodType']);
        }

        // Validate payment fields if provided
        if (isset($data['paymentFields'])) {
            $this->validatePaymentFields($data['paymentFields']);
        }
    }

    // =================================================================
    // REFUND & CREDIT VALIDATION METHODS
    // =================================================================

    /**
     * Validate payment reversal request
     * 
     * @param array $data Reversal request data
     * @throws JamboJetValidationException
     */
    private function validateReversalRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['paymentKey']);

        // Validate payment key
        $this->validatePaymentKey($data['paymentKey']);

        // Validate amount if provided (partial reversal)
        if (isset($data['amount'])) {
            $this->validateFormats($data, ['amount' => 'positive_number']);
        }

        // Validate reason code if provided
        if (isset($data['reasonCode'])) {
            if (!preg_match('/^[A-Z0-9_]{2,20}$/', $data['reasonCode'])) {
                throw new JamboJetValidationException(
                    'Invalid reversal reason code format',
                    400
                );
            }
        }

        // Validate notes if provided
        if (isset($data['notes'])) {
            $this->validateStringLengths($data, ['notes' => ['max' => 500]]);
        }
    }

    /**
     * Validate booking credit application request
     * 
     * @param array $data Booking credit data
     * @throws JamboJetValidationException
     */
    private function validateBookingCreditRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['recordLocator']);

        // Validate record locator
        $this->validateRecordLocator($data['recordLocator']);

        // Validate amount if provided
        if (isset($data['amount'])) {
            $this->validateFormats($data, ['amount' => 'positive_number']);
        }

        // Validate currency code if provided
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate email if provided
        if (isset($data['emailAddress'])) {
            $this->validateFormats($data, ['emailAddress' => 'email']);
            $this->validateStringLengths($data, ['emailAddress' => ['max' => 266]]);
        }

        // Validate origin if provided
        if (isset($data['origin'])) {
            if (!preg_match('/^[A-Z]{3}$/', strtoupper($data['origin']))) {
                throw new JamboJetValidationException(
                    'Origin must be a 3-letter airport code',
                    400
                );
            }
        }

        // Validate passenger names if provided
        if (isset($data['firstName'])) {
            $this->validateStringLengths($data, ['firstName' => ['max' => 32]]);
        }

        if (isset($data['lastName'])) {
            $this->validateStringLengths($data, ['lastName' => ['max' => 32]]);
        }
    }

    /**
     * Validate credit transfer request
     * 
     * @param array $data Credit transfer data
     * @throws JamboJetValidationException
     */
    private function validateCreditTransferRequest(array $data): void
    {
        // Validate required fields
        $requiredFields = ['amount', 'currencyCode', 'fromAccountType', 'toAccountType'];
        $this->validateRequired($data, $requiredFields);

        // Validate amount
        $this->validateFormats($data, ['amount' => 'positive_number']);

        // Validate currency code
        $this->validateFormats($data, ['currencyCode' => 'currency_code']);

        // Validate account types
        $validTypes = [0, 1, 2]; // Customer, Booking, Organization
        if (!in_array($data['fromAccountType'], $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid from account type. Expected 0 (Customer), 1 (Booking), or 2 (Organization)',
                400
            );
        }

        if (!in_array($data['toAccountType'], $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid to account type. Expected 0 (Customer), 1 (Booking), or 2 (Organization)',
                400
            );
        }

        // Validate reason if provided
        if (isset($data['reason'])) {
            $this->validateStringLengths($data, ['reason' => ['max' => 200]]);
        }

        // Validate notes if provided
        if (isset($data['notes'])) {
            $this->validateStringLengths($data, ['notes' => ['max' => 500]]);
        }
    }

    /**
     * Validate credit adjustment request
     * 
     * @param array $data Credit adjustment data
     * @throws JamboJetValidationException
     */
    private function validateCreditAdjustmentRequest(array $data): void
    {
        // Validate required fields
        $requiredFields = ['amount', 'currencyCode', 'adjustmentType', 'reason'];
        $this->validateRequired($data, $requiredFields);

        // Validate amount (can be positive or negative for adjustments)
        if (!is_numeric($data['amount']) || $data['amount'] == 0) {
            throw new JamboJetValidationException(
                'Adjustment amount must be a non-zero number',
                400
            );
        }

        // Validate currency code
        $this->validateFormats($data, ['currencyCode' => 'currency_code']);

        // Validate adjustment type
        $validTypes = ['Credit', 'Debit', 'Correction', 'Compensation', 'Manual'];
        if (!in_array($data['adjustmentType'], $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid adjustment type. Expected: Credit, Debit, Correction, Compensation, or Manual',
                400
            );
        }

        // Validate reason
        $this->validateStringLengths($data, ['reason' => ['min' => 10, 'max' => 200]]);

        // Validate notes if provided
        if (isset($data['notes'])) {
            $this->validateStringLengths($data, ['notes' => ['max' => 500]]);
        }

        // Validate authorization code if provided
        if (isset($data['authorizationCode'])) {
            $this->validateStringLengths($data, ['authorizationCode' => ['max' => 50]]);
        }
    }

    // =================================================================
    //  REPORTING & ANALYTICS VALIDATION METHODS
    // =================================================================

    /**
     * Validate payment transaction filters
     * 
     * @param array $filters Transaction filter criteria
     * @throws JamboJetValidationException
     */
    private function validateTransactionFilters(array $filters): void
    {
        // Validate date range if provided
        if (isset($filters['startDate'])) {
            $this->validateFormats($filters, ['startDate' => 'date']);
        }

        if (isset($filters['endDate'])) {
            $this->validateFormats($filters, ['endDate' => 'date']);

            // End date must be after start date
            if (isset($filters['startDate'])) {
                $startDate = new \DateTime($filters['startDate']);
                $endDate = new \DateTime($filters['endDate']);

                if ($endDate < $startDate) {
                    throw new JamboJetValidationException(
                        'End date must be after start date',
                        400
                    );
                }
            }
        }

        // Validate status filter if provided
        if (isset($filters['status'])) {
            $validStatuses = [
                'Pending',
                'Approved',
                'Declined',
                'Voided',
                'Refunded',
                'PartiallyRefunded',
                'ChargedBack',
                'InProcess',
                'Error'
            ];

            if (!in_array($filters['status'], $validStatuses)) {
                throw new JamboJetValidationException(
                    'Invalid payment status. Expected one of: ' . implode(', ', $validStatuses),
                    400
                );
            }
        }

        // Validate payment method type filter if provided
        if (isset($filters['paymentMethodType'])) {
            $this->validatePaymentMethodType($filters['paymentMethodType']);
        }

        // Validate currency filter if provided
        if (isset($filters['currencyCode'])) {
            $this->validateFormats($filters, ['currencyCode' => 'currency_code']);
        }

        // Validate amount range if provided
        if (isset($filters['minAmount'])) {
            $this->validateFormats($filters, ['minAmount' => 'positive_number']);
        }

        if (isset($filters['maxAmount'])) {
            $this->validateFormats($filters, ['maxAmount' => 'positive_number']);

            if (isset($filters['minAmount']) && $filters['maxAmount'] < $filters['minAmount']) {
                throw new JamboJetValidationException(
                    'Maximum amount must be greater than minimum amount',
                    400
                );
            }
        }
    }

    /**
     * Validate reconciliation date range
     * 
     * @param array $dateRange Date range for reconciliation
     * @throws JamboJetValidationException
     */
    private function validateReconciliationDateRange(array $dateRange): void
    {
        // Validate required fields
        $this->validateRequired($dateRange, ['startDate', 'endDate']);

        // Validate date formats
        $this->validateFormats($dateRange, [
            'startDate' => 'date',
            'endDate' => 'date'
        ]);

        // Validate date range
        $startDate = new \DateTime($dateRange['startDate']);
        $endDate = new \DateTime($dateRange['endDate']);

        if ($endDate < $startDate) {
            throw new JamboJetValidationException(
                'End date must be after start date',
                400
            );
        }

        // Reconciliation period shouldn't exceed 1 year
        $interval = $startDate->diff($endDate);
        if ($interval->days > 365) {
            throw new JamboJetValidationException(
                'Reconciliation period cannot exceed 1 year',
                400
            );
        }
    }

    /**
     * Validate preauthorization request
     * 
     * @param array $data Preauthorization data
     * @throws JamboJetValidationException
     */
    private function validatePreauthorizationRequest(array $data): void
    {
        // Validate required fields
        $requiredFields = ['paymentMethodType', 'amount', 'currencyCode'];
        $this->validateRequired($data, $requiredFields);

        // Validate payment method type
        $this->validatePaymentMethodType($data['paymentMethodType']);

        // Validate amount
        $this->validateFormats($data, ['amount' => 'positive_number']);

        // Validate currency code
        $this->validateFormats($data, ['currencyCode' => 'currency_code']);

        // Validate payment fields
        if (isset($data['paymentFields'])) {
            $this->validatePaymentFields($data['paymentFields']);
        }

        // Validate billing address if provided
        if (isset($data['billingAddress'])) {
            $this->validateBillingAddress($data['billingAddress']);
        }

        // Validate hold duration if provided
        if (isset($data['holdDurationDays'])) {
            if (!is_int($data['holdDurationDays']) || $data['holdDurationDays'] < 1 || $data['holdDurationDays'] > 30) {
                throw new JamboJetValidationException(
                    'Hold duration must be between 1 and 30 days',
                    400
                );
            }
        }
    }

    /**
     * Validate payment capture request
     * 
     * @param float $amount Amount to capture
     * @throws JamboJetValidationException
     */
    private function validateCaptureAmount(float $amount): void
    {
        if (!is_numeric($amount) || $amount <= 0) {
            throw new JamboJetValidationException(
                'Capture amount must be a positive number',
                400
            );
        }

        // Additional validation could check against preauth amount
        // but that requires fetching the preauth details first
    }

    /**
     * Validate payment fields for credit card payments
     * 
     * @param array $paymentFields Payment fields data
     * @throws JamboJetValidationException
     */
    private function validatePaymentFields(array $paymentFields): void
    {
        // Validate account number if provided
        if (isset($paymentFields['accountNumber'])) {
            // Basic card number validation (Luhn algorithm should be used in production)
            if (!preg_match('/^[0-9]{13,19}$/', str_replace(' ', '', $paymentFields['accountNumber']))) {
                throw new JamboJetValidationException(
                    'Invalid credit card number format',
                    400
                );
            }
        }

        // Validate expiration if provided
        if (isset($paymentFields['expirationMonth'])) {
            if (
                !is_int($paymentFields['expirationMonth']) ||
                $paymentFields['expirationMonth'] < 1 ||
                $paymentFields['expirationMonth'] > 12
            ) {
                throw new JamboJetValidationException(
                    'Expiration month must be between 1 and 12',
                    400
                );
            }
        }

        if (isset($paymentFields['expirationYear'])) {
            $currentYear = (int)date('Y');
            if (
                !is_int($paymentFields['expirationYear']) ||
                $paymentFields['expirationYear'] < $currentYear ||
                $paymentFields['expirationYear'] > $currentYear + 20
            ) {
                throw new JamboJetValidationException(
                    'Invalid expiration year',
                    400
                );
            }
        }

        // Validate CVV if provided
        if (isset($paymentFields['cvv'])) {
            if (!preg_match('/^[0-9]{3,4}$/', $paymentFields['cvv'])) {
                throw new JamboJetValidationException(
                    'CVV must be 3 or 4 digits',
                    400
                );
            }
        }

        // Validate holder name if provided
        if (isset($paymentFields['holderName'])) {
            $this->validateStringLengths($paymentFields, ['holderName' => ['min' => 2, 'max' => 100]]);
        }
    }

    /**
     * Validate billing address
     * 
     * @param array $address Billing address data
     * @throws JamboJetValidationException
     */
    private function validateBillingAddress(array $address): void
    {
        // Validate address lines
        if (isset($address['lineOne'])) {
            $this->validateStringLengths($address, ['lineOne' => ['max' => 100]]);
        }

        if (isset($address['lineTwo'])) {
            $this->validateStringLengths($address, ['lineTwo' => ['max' => 100]]);
        }

        // Validate city
        if (isset($address['city'])) {
            $this->validateStringLengths($address, ['city' => ['max' => 50]]);
        }

        // Validate state/province
        if (isset($address['provinceState'])) {
            $this->validateStringLengths($address, ['provinceState' => ['max' => 50]]);
        }

        // Validate postal code
        if (isset($address['postalCode'])) {
            if (!preg_match('/^[A-Z0-9\s\-]{3,10}$/i', $address['postalCode'])) {
                throw new JamboJetValidationException(
                    'Invalid postal code format',
                    400
                );
            }
        }

        // Validate country code
        if (isset($address['countryCode'])) {
            $this->validateFormats($address, ['countryCode' => 'country_code']);
        }
    }

    /** 
     * Validate 3DS completion data
     * 
     * @param string $paRes Payment authentication response
     * @param string $md Merchant data
     * @throws JamboJetValidationException
     */
    private function validate3dsCompletion(string $paRes, string $md): void
    {
        if (empty(trim($paRes))) {
            throw new JamboJetValidationException(
                'PaRes (Payment Authentication Response) is required',
                400
            );
        }

        if (empty(trim($md))) {
            throw new JamboJetValidationException(
                'MD (Merchant Data) is required',
                400
            );
        }

        // PaRes should be base64 encoded
        if (!preg_match('/^[A-Za-z0-9+\/=]+$/', $paRes)) {
            throw new JamboJetValidationException(
                'PaRes must be base64 encoded',
                400
            );
        }
    }

    /**
     * Validate DCC transaction key
     * 
     * @param string $transactionKey DCC/MCC/3DS transaction key
     * @throws JamboJetValidationException
     */
    private function validateTransactionKey(string $transactionKey): void
    {
        if (empty(trim($transactionKey))) {
            throw new JamboJetValidationException(
                'Transaction key is required',
                400
            );
        }

        // Transaction keys are typically base64 encoded or alphanumeric with special chars
        if (strlen($transactionKey) < 10) {
            throw new JamboJetValidationException(
                'Transaction key appears to be invalid (too short)',
                400
            );
        }
    }

    /**
     * Validate payment fee calculation request
     * 
     * @param array $data Fee calculation data
     * @throws JamboJetValidationException
     */
    private function validatePaymentFeeRequest(array $data): void
    {
        // Validate payment method code
        if (isset($data['paymentMethodCode'])) {
            if (!preg_match('/^[A-Z0-9]{2,10}$/', $data['paymentMethodCode'])) {
                throw new JamboJetValidationException(
                    'Invalid payment method code format',
                    400
                );
            }
        }

        // Validate currency code
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate amount if provided
        if (isset($data['amount'])) {
            $this->validateFormats($data, ['amount' => 'positive_number']);
        }

        // Validate point of sale code if provided
        if (isset($data['pointOfSaleCode'])) {
            if (!preg_match('/^[A-Z0-9]{2,10}$/', $data['pointOfSaleCode'])) {
                throw new JamboJetValidationException(
                    'Invalid point of sale code format',
                    400
                );
            }
        }
    }


    /**
     * Validate voucher payment request
     * 
     * @param array $data Voucher payment data
     * @throws JamboJetValidationException
     */
    private function validateVoucherPaymentRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['voucherCode', 'amount', 'currencyCode']);

        // Validate voucher code
        if (strlen($data['voucherCode']) < 1 || strlen($data['voucherCode']) > 20) {
            throw new JamboJetValidationException(
                'Voucher code must be between 1 and 20 characters',
                400
            );
        }

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new JamboJetValidationException(
                'Voucher payment amount must be a positive number',
                400
            );
        }

        // Validate currency code
        $this->validateFormats($data, ['currencyCode' => 'currency_code']);

        // Validate password if provided
        if (isset($data['password']) && strlen($data['password']) > 50) {
            throw new JamboJetValidationException(
                'Voucher password cannot exceed 50 characters',
                400
            );
        }
    }

    /**
     * Validate credit shell refund request
     * 
     * @param array $data Credit shell refund data
     * @throws JamboJetValidationException
     */
    private function validateCreditShellRefundRequest(array $data): void
    {
        // Validate required fields
        $this->validateRequired($data, ['amount', 'paymentMethodCode', 'accountTransactionCode', 'accountType']);

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new JamboJetValidationException(
                'Refund amount must be a positive number',
                400
            );
        }

        // Validate payment method code
        if (strlen($data['paymentMethodCode']) < 1 || strlen($data['paymentMethodCode']) > 2) {
            throw new JamboJetValidationException(
                'Payment method code must be 1-2 characters',
                400
            );
        }

        // Validate account type (0 = CustomerNumber, 1 = ReservationCredit)
        if (!in_array($data['accountType'], [0, 1])) {
            throw new JamboJetValidationException(
                'Invalid account type. Expected 0 (CustomerNumber) or 1 (ReservationCredit)',
                400
            );
        }

        // Validate currency code if provided
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }
    }

    /**
     * Validate passenger key
     * 
     * @param string $passengerKey Passenger key
     * @throws JamboJetValidationException
     */
    private function validatePassengerKey(string $passengerKey): void
    {
        if (empty(trim($passengerKey))) {
            throw new JamboJetValidationException(
                'Passenger key cannot be empty',
                400
            );
        }

        if (strlen($passengerKey) < 5) {
            throw new JamboJetValidationException(
                'Invalid passenger key format',
                400
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
