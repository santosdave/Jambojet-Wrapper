<?php

namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;

interface PaymentInterface
{
    // =================================================================
    // STATEFUL PAYMENT OPERATIONS (Work on booking in session state)
    // =================================================================

    /**
     * Process payment for booking in state
     * POST /api/nsk/v6/booking/payments
     * Requires a booking to be loaded in session state
     */
    public function processPayment(array $paymentData): array;

    /**
     * Get payments for booking in state
     * GET /api/nsk/v6/booking/payments
     * Requires a booking to be loaded in session state
     */
    public function getPayments(): array;

    /**
     * Get specific payment by key for booking in state
     * GET /api/nsk/v6/booking/payments/{paymentKey}
     * Requires a booking to be loaded in session state
     */
    public function getPaymentByKey(string $paymentKey): array;

    /**
     * Process refund for booking in state
     * POST /api/nsk/v4/booking/payments/refunds
     * Requires a booking to be loaded in session state
     */
    public function processRefund(array $refundData): array;

    /**
     * Get available refund methods for booking in state
     * GET /api/nsk/v1/booking/payments/refunds
     * Requires a booking to be loaded in session state
     */
    public function getAvailableRefundMethods(): array;

    // =================================================================
    // CREDIT OPERATIONS (Session-based)
    // =================================================================

    /**
     * Apply customer credit to booking in state
     * POST /api/nsk/v3/booking/payments/customerCredit
     */
    public function applyCustomerCredit(array $creditData): array;

    /**
     * Get available customer credit
     * GET /api/nsk/v3/booking/payments/customerCredit
     */
    public function getCustomerCredit(): array;

    /**
     * Apply organization credit to booking in state
     * POST /api/nsk/v3/booking/payments/organizationCredit
     */
    public function applyOrganizationCredit(array $creditData): array;

    /**
     * Get available organization credit
     * GET /api/nsk/v3/booking/payments/organizationCredit
     */
    public function getOrganizationCredit(): array;

    // =================================================================
    // STORED PAYMENT OPERATIONS
    // =================================================================

    /**
     * Use stored payment for booking in state
     * POST /api/nsk/v6/booking/payments/storedPayment/{storedPaymentKey}
     */
    public function useStoredPayment(string $storedPaymentKey, array $paymentData): array;

    /**
     * Get stored payments for current user
     * GET /api/nsk/v1/booking/payments/storedPayments
     */
    public function getStoredPayments(): array;

    // =================================================================
    // STATELESS OPERATIONS (For completed bookings)
    // =================================================================

    /**
     * Get payments for a completed booking
     * GET /api/nsk/v1/bookings/{recordLocator}/payments
     * Stateless operation - doesn't require booking in session
     */
    public function getBookingPayments(string $recordLocator): array;

    /**
     * Get payment details by payment key (stateless)
     * GET /api/nsk/v1/payments/{paymentKey}
     * Stateless operation for retrieving payment information
     */
    public function getPaymentDetails(string $paymentKey): array;

    // =================================================================
    // REFUND OPERATIONS (Advanced)
    // =================================================================

    /**
     * Create customer credit refund
     * POST /api/nsk/v3/booking/payments/refunds/customerCredit
     */
    public function createCustomerCreditRefund(array $refundData): array;

    /**
     * Create organization credit refund
     * POST /api/nsk/v2/booking/payments/refunds/organizationCredit
     */
    public function createOrganizationCreditRefund(array $refundData): array;

    // =================================================================
    // HELPER METHODS
    // =================================================================

    /**
     * Get payment method types
     * Returns available payment methods for the current context
     */
    public function getPaymentMethodTypes(): array;

    /**
     * Verify payment can be processed
     * Validates payment data without processing
     */
    public function verifyPayment(array $paymentData): array;


    /**
     * Apply voucher payment to booking
     * POST /api/nsk/v4/booking/payments/voucher
     */
    public function applyVoucherPayment(array $voucherData): array;

    /**
     * Get voucher information
     * GET /api/nsk/v3/booking/payments/voucher
     */
    public function getVoucherInfo(string $referenceCode, bool $overrideRestrictions = false): array;

    /**
     * Delete/reverse voucher payment
     * DELETE /api/nsk/v2/booking/payments/voucher/{voucherPaymentReference}
     */
    public function deleteVoucherPayment(string $voucherPaymentReference): array;

    /**
     * Apply passenger-specific voucher payment
     * POST /api/nsk/v1/booking/payments/voucher/passenger/{passengerKey}
     */
    public function applyPassengerVoucherPayment(string $passengerKey, array $voucherData): array;

    /**
     * Get credit by reference (Agent function)
     * GET /api/nsk/v2/booking/payments/credit
     */
    public function getCreditByReference(string $referenceNumber, string $currencyCode, int $type): array;

    /**
     * Process credit shell refund
     * POST /api/nsk/v1/booking/payments/refunds/creditShell
     */
    public function processCreditShellRefund(array $refundData): array;

// =================================================================
// PAYMENT MANAGEMENT
// =================================================================

    /**
     * Delete payment from booking
     * DELETE /api/nsk/v1/booking/payments/{paymentKey}
     */
    public function deletePayment(string $paymentKey): array;


    /**
     * Get payments (v1 legacy)
     * GET /api/nsk/v1/booking/payments
     */
    public function getPaymentsV1(): array;

    /**
     * Get payment by key (v1 legacy)
     * GET /api/nsk/v1/booking/payments/{paymentKey}
     */
    public function getPaymentByKeyV1(string $paymentKey): array;

    /**
     * Process refund v5 (without credit shell support)
     * POST /api/nsk/v5/booking/payments/refunds
     */
    public function processRefundV5(array $refundData): array;

    /**
     * Get payment allocations for booking in state
     * GET /api/nsk/v1/booking/payments/allocations
     * 
     * Shows how payments are distributed across booking charges
     * 
     * @return array Payment allocation details
     * @throws JamboJetApiException
     */
    public function getPaymentAllocations(): array;

    /**
     * Get credit available from a past booking
     * GET /api/nsk/v2/booking/payments/bookingCredit
     * 
     * @param string $recordLocator Record locator of the past booking
     * @param string $currencyCode Currency code for the credit
     * @param string|null $emailAddress Email address (optional)
     * @param string|null $origin Origin station code (optional, 3 chars)
     * @param string|null $firstName First name (optional)
     * @param string|null $lastName Last name (optional)
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
    ): array;

    /**
     * Add payment to blacklist
     * POST /api/nsk/v1/booking/payments/blacklist
     * 
     * Blacklists a payment method to prevent fraud and chargebacks.
     * Requires payment key and blacklist reason code.
     * 
     * @param array $blacklistData Blacklist entry data with reason, dates, notes
     * @return array Blacklist creation response
     * @throws JamboJetApiException
     */
    public function addPaymentToBlacklist(array $blacklistData): array;

    /**
     * Get blacklisted payment details
     * GET /api/nsk/v2/booking/payments/blacklist/{paymentKey}
     * 
     * Retrieves details of a specific blacklisted payment including
     * reason code, start/end dates, and notes.
     * 
     * @param string $paymentKey Payment key to retrieve
     * @return array Blacklisted payment details
     * @throws JamboJetApiException
     */
    public function getBlacklistedPayment(string $paymentKey): array;

    /**
     * Modify blacklisted payment
     * PATCH /api/nsk/v1/booking/payments/blacklist/{paymentKey}
     * 
     * Updates blacklist entry details such as reason, dates, or notes.
     * Uses delta mapper for partial updates.
     * 
     * @param string $paymentKey Payment key to modify
     * @param array $patchData Fields to update
     * @return array Update response
     * @throws JamboJetApiException
     */
    public function modifyBlacklistedPayment(string $paymentKey, array $patchData): array;

    /**
     * Remove payment from blacklist
     * DELETE /api/nsk/v1/booking/payments/blacklist/{paymentKey}
     * 
     * Removes a payment from the blacklist, allowing it to be used again.
     * 
     * @param string $paymentKey Payment key to remove from blacklist
     * @return array Deletion response
     * @throws JamboJetApiException
     */
    public function removePaymentFromBlacklist(string $paymentKey): array;

    /**
     * Manually authorize a committed payment
     * PATCH /api/nsk/v2/booking/payments/{paymentKey}/authorize
     * 
     * Manual authorization with authorization code for payments
     * that require manual review or approval.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $paymentKey Payment key to authorize
     * @param string $authorizationCode Authorization code from payment processor
     * @return array Authorization response
     * @throws JamboJetApiException
     */
    public function manuallyAuthorizePayment(string $paymentKey, string $authorizationCode): array;

    /**
     * Manually decline a committed payment
     * PATCH /api/nsk/v2/booking/payments/{paymentKey}/decline
     * 
     * Used with caution - manually decline a payment that was committed.
     * Only permissible under certain circumstances.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $paymentKey Payment key to decline
     * @return array Decline response
     * @throws JamboJetApiException
     */
    public function manuallyDeclinePayment(string $paymentKey): array;

    /**
     * Cancel in-process payment
     * DELETE /api/nsk/v1/booking/payments/inProcess
     * 
     * Cancels a payment that is currently in process (3DS, MCC, or DCC).
     * Removes the in-process payment from session state.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @return array Cancellation response
     * @throws JamboJetApiException
     */
    public function cancelInProcessPayment(): array;

    /**
     * Get in-process payment details
     * GET /api/nsk/v1/booking/payments/inProcess
     * 
     * Retrieves details of the current in-process payment if one exists
     * in session state.
     * 
     * @return array In-process payment details or null
     * @throws JamboJetApiException
     */
    public function getInProcessPayment(): array;

    /**
     * Get DCC (Direct Currency Conversion) offer
     * POST /api/nsk/v1/booking/payments/{paymentMethod}/dcc
     * 
     * Gets available direct currency conversion offer for inline DCC payment.
     * Allows customer to pay in their local currency.
     * 
     * @param string $paymentMethod Payment method code
     * @param array $dccRequest DCC request with amount, currency details
     * @return array DCC offer with exchange rate and converted amount
     * @throws JamboJetApiException
     */
    public function getDccOffer(string $paymentMethod, array $dccRequest): array;

    /**
     * Process DCC payment
     * POST /api/nsk/v1/booking/payments/dcc/{dccPaymentTransactionKey}
     * 
     * Processes a DCC payment using the transaction key from a previous offer.
     * May return 3DS response requiring authentication.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $dccPaymentTransactionKey DCC transaction key
     * @return array Payment processing response (may include 3DS data)
     * @throws JamboJetApiException
     */
    public function processDccPayment(string $dccPaymentTransactionKey): array;

    /**
     * Get DCC exchange rates
     * GET /api/nsk/v1/booking/payments/dcc/rates
     * 
     * Retrieves current DCC exchange rates for currency conversion.
     * 
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @return array Current exchange rates
     * @throws JamboJetApiException
     */
    public function getDccRates(string $fromCurrency, string $toCurrency): array;

// ==================== MCC (Multi-Currency Conversion) ====================

    /**
     * Get MCC (Multi-Currency Conversion) availability
     * GET /api/nsk/v3/booking/payments/mcc
     * 
     * Gets available multi-currency codes for the booking.
     * Affected by booking currency code.
     * 
     * @return array Available MCC currencies with rates
     * @throws JamboJetApiException
     */
    public function getMccAvailability(): array;

    /**
     * Process MCC payment
     * POST /api/nsk/v6/booking/payments/mcc/{currencyCode}
     * 
     * Creates a new MCC payment in the specified currency.
     * Exchange rate is auto-populated for normal MCC scenarios.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $currencyCode Collected currency code
     * @param array $mccData MCC payment request data
     * @param string|null $termUrl 3DS term URL if required
     * @return array Payment processing response
     * @throws JamboJetApiException
     */
    public function processMccPayment(string $currencyCode, array $mccData, ?string $termUrl = null): array;

    /**
     * Process MCC payment with stored payment
     * POST /api/nsk/v6/booking/payments/mcc/{currencyCode}/storedPayment/{storedPaymentKey}
     * 
     * Creates MCC payment using stored payment information.
     * Cannot use same currency as booking currency.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $currencyCode Collected currency code
     * @param string $storedPaymentKey Stored payment key
     * @param array $mccData MCC payment data
     * @param string|null $termUrl 3DS term URL if required
     * @return array Payment processing response
     * @throws JamboJetApiException
     */
    public function processMccStoredPayment(
        string $currencyCode,
        string $storedPaymentKey,
        array $mccData,
        ?string $termUrl = null
    ): array;

    /**
     * Get MCC exchange rates
     * GET /api/nsk/v1/booking/payments/mcc/rates
     * 
     * Retrieves current MCC exchange rates for all available currencies.
     * 
     * @return array MCC exchange rates dictionary
     * @throws JamboJetApiException
     */
    public function getMccRates(): array;

    /**
     * Get MCC payment fees
     * GET /api/nsk/v2/booking/payments/mcc/fees
     * 
     * Retrieves MCC processing fees for a specific currency.
     * 
     * @param string $currencyCode Currency code to check fees for
     * @return array MCC fee information
     * @throws JamboJetApiException
     */
    public function getMccFees(string $currencyCode): array;

    /**
     * Get MCC payment quote
     * POST /api/nsk/v1/booking/payments/mcc/quote
     * 
     * Gets a quote for MCC payment showing final amount and fees.
     * 
     * @param string $currencyCode Target currency code
     * @param float $amount Amount to convert
     * @return array MCC quote with breakdown
     * @throws JamboJetApiException
     */
    public function getMccQuote(string $currencyCode, float $amount): array;

// ==================== 3DS (Three-Domain Secure) ====================

    /**
     * Initiate 3DS authentication
     * POST /api/nsk/v1/booking/payments/3ds/authenticate
     * 
     * Initiates 3D Secure authentication flow for payment.
     * Returns authentication URL and parameters.
     * 
     * @param array $authData 3DS authentication data with payment info
     * @return array 3DS authentication response (URL, PaReq, MD)
     * @throws JamboJetApiException
     */
    public function initiate3dsAuthentication(array $authData): array;

    /**
     * Complete 3DS authentication
     * POST /api/nsk/v1/booking/payments/3ds/complete
     * 
     * Completes the 3D Secure authentication after customer verification.
     * 
     * @param string $paRes Payment authentication response from issuer
     * @param string $md Merchant data passed through authentication
     * @return array Payment completion response
     * @throws JamboJetApiException
     */
    public function complete3dsAuthentication(string $paRes, string $md): array;

    /**
     * Get 3DS transaction status
     * GET /api/nsk/v1/booking/payments/3ds/status/{transactionKey}
     * 
     * Retrieves current status of a 3DS authentication transaction.
     * 
     * @param string $transactionKey 3DS transaction key
     * @return array 3DS transaction status
     * @throws JamboJetApiException
     */
    public function get3dsStatus(string $transactionKey): array;


    /**
     * Get payment processing fee
     * GET /api/nsk/v1/booking/payments/fee
     * 
     * Calculates payment processing fee for a specific payment method.
     * Fee varies by payment method, currency, and point of sale.
     * 
     * @param string $paymentMethodCode Payment method code
     * @param string $currencyCode Currency code
     * @param string $pointOfSaleCode Point of sale code
     * @return array Payment fee details
     * @throws JamboJetApiException
     */
    public function getPaymentFee(
        string $paymentMethodCode,
        string $currencyCode,
        string $pointOfSaleCode
    ): array;

    /**
     * Get all payment fees
     * GET /api/nsk/v2/booking/payments/fees
     * 
     * Retrieves all payment processing fees for available payment methods.
     * 
     * @return array Dictionary of payment fees by method
     * @throws JamboJetApiException
     */
    public function getAllPaymentFees(): array;

    /**
     * Calculate payment fee
     * POST /api/nsk/v1/booking/payments/fee/calculate
     * 
     * Calculates exact fee for a payment transaction based on parameters.
     * 
     * @param array $feeData Fee calculation parameters
     * @return array Calculated fee breakdown
     * @throws JamboJetApiException
     */
    public function calculatePaymentFee(array $feeData): array;

    /**
     * Reverse a payment
     * POST /api/nsk/v2/booking/payments/reversals
     * 
     * Reverses a payment on the booking (agent-only operation).
     * Does not require zero/negative balance like standard refunds.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param array $reversalData Payment reversal request
     * @return array Reversal response
     * @throws JamboJetApiException
     */
    public function reversePayment(array $reversalData): array;

    /**
     * Get refund status
     * GET /api/nsk/v1/booking/payments/refunds/status
     * 
     * Retrieves status of a refund transaction.
     * 
     * @param string $refundKey Refund transaction key
     * @return array Refund status details
     * @throws JamboJetApiException
     */
    public function getRefundStatus(string $refundKey): array;

    /**
     * Get booking credit (v3)
     * GET /api/nsk/v3/booking/payments/bookingCredit
     * 
     * Enhanced version of booking credit retrieval with additional parameters.
     * 
     * @param string $recordLocator Record locator
     * @param array $params Additional query parameters
     * @return array Booking credit information
     * @throws JamboJetApiException
     */
    public function getBookingCreditV3(string $recordLocator, array $params = []): array;

    /**
     * Apply booking credit to booking
     * POST /api/nsk/v2/booking/payments/bookingCredit
     * 
     * Applies credit from a past booking to the current booking in state.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param array $creditData Booking credit application data
     * @return array Credit application response
     * @throws JamboJetApiException
     */
    public function applyBookingCredit(array $creditData): array;

    /**
     * Remove booking credit
     * DELETE /api/nsk/v2/booking/payments/bookingCredit/{creditKey}
     * 
     * Removes applied booking credit from the booking in state.
     * 
     * IMPORTANT: Cannot be called concurrently with same session token.
     * 
     * @param string $creditKey Credit key to remove
     * @return array Removal response
     * @throws JamboJetApiException
     */
    public function removeBookingCredit(string $creditKey): array;

    /**
     * Get credit history
     * GET /api/nsk/v1/booking/payments/credit/history
     * 
     * Retrieves credit transaction history for an account.
     * 
     * @param string $referenceNumber Account reference (record locator, customer/org code)
     * @param int $type Credit type (0=Customer, 1=Booking, 2=Organization)
     * @return array Credit transaction history
     * @throws JamboJetApiException
     */
    public function getCreditHistory(string $referenceNumber, int $type): array;

    /**
     * Transfer credit between accounts
     * POST /api/nsk/v1/booking/payments/credit/transfer
     * 
     * Transfers credit from one account to another (agent-only).
     * 
     * @param string $fromAccount Source account reference
     * @param string $toAccount Destination account reference
     * @param array $transferData Transfer details (amount, currency, type)
     * @return array Transfer confirmation
     * @throws JamboJetApiException
     */
    public function transferCredit(
        string $fromAccount,
        string $toAccount,
        array $transferData
    ): array;

    /**
     * Get credit balance
     * GET /api/nsk/v2/booking/payments/credit/balance
     * 
     * Retrieves current credit balance for an account.
     * 
     * @param string $accountNumber Account number
     * @param string $currencyCode Currency code
     * @return array Credit balance information
     * @throws JamboJetApiException
     */
    public function getCreditBalance(string $accountNumber, string $currencyCode): array;

    /**
     * Adjust credit balance
     * POST /api/nsk/v1/booking/payments/credit/adjustment
     * 
     * Makes manual adjustment to credit balance (agent-only).
     * Used for corrections, compensations, or manual entries.
     * 
     * @param string $accountNumber Account to adjust
     * @param array $adjustmentData Adjustment details (amount, reason, notes)
     * @return array Adjustment confirmation
     * @throws JamboJetApiException
     */
    public function adjustCredit(string $accountNumber, array $adjustmentData): array;

    /**
     * Get payment history for booking
     * GET /api/nsk/v1/booking/{recordLocator}/payments/history
     * 
     * Retrieves complete payment history including all transactions,
     * refunds, and adjustments for a booking.
     * 
     * @param string $recordLocator Booking record locator
     * @return array Payment history timeline
     * @throws JamboJetApiException
     */
    public function getPaymentHistory(string $recordLocator): array;

    /**
     * Get payment transactions with filters
     * GET /api/nsk/v1/booking/payments/transactions
     * 
     * Retrieves payment transactions with filtering capabilities
     * (date range, status, type, etc.).
     * 
     * @param array $filters Transaction filter criteria
     * @return array Filtered transaction list
     * @throws JamboJetApiException
     */
    public function getPaymentTransactions(array $filters = []): array;

    /**
     * Get payment summary for booking
     * GET /api/nsk/v1/booking/payments/summary
     * 
     * Gets summarized payment information including totals,
     * outstanding balance, and payment breakdown.
     * 
     * @param string $recordLocator Booking record locator
     * @return array Payment summary
     * @throws JamboJetApiException
     */
    public function getPaymentSummary(string $recordLocator): array;

    /**
     * Get payment audit log
     * GET /api/nsk/v1/booking/payments/audit
     * 
     * Retrieves detailed audit log for a payment including
     * all modifications, authorizations, and status changes.
     * 
     * @param string $paymentKey Payment key
     * @return array Audit log entries
     * @throws JamboJetApiException
     */
    public function getPaymentAuditLog(string $paymentKey): array;

    /**
     * Get payment reconciliation data
     * GET /api/nsk/v1/booking/payments/reconciliation
     * 
     * Retrieves payment reconciliation data for accounting purposes.
     * Used for end-of-day/period financial reconciliation.
     * 
     * @param array $dateRange Date range for reconciliation
     * @return array Reconciliation report data
     * @throws JamboJetApiException
     */
    public function getPaymentReconciliation(array $dateRange): array;

    /**
     * Validate payment data
     * POST /api/nsk/v1/booking/payments/validate
     * 
     * Validates payment data without processing the payment.
     * Checks card numbers, expiration, billing addresses, etc.
     * 
     * @param array $paymentData Payment data to validate
     * @return array Validation results with any errors
     * @throws JamboJetApiException
     */
    public function validatePayment(array $paymentData): array;

    /**
     * Verify payment status
     * POST /api/nsk/v1/booking/payments/verify
     * 
     * Verifies current payment status with payment processor.
     * Used to confirm payment state after delays or issues.
     * 
     * @param string $paymentKey Payment key to verify
     * @return array Current verified status
     * @throws JamboJetApiException
     */
    public function verifyPaymentStatus(string $paymentKey): array;

    /**
     * Get detailed payment status
     * GET /api/nsk/v1/booking/payments/{paymentKey}/status
     * 
     * Retrieves detailed status information for a payment
     * including authorization status, transaction details, etc.
     * 
     * @param string $paymentKey Payment key
     * @return array Detailed payment status
     * @throws JamboJetApiException
     */
    public function getPaymentStatus(string $paymentKey): array;

    /**
     * Preauthorize payment
     * POST /api/nsk/v1/booking/payments/preauthorize
     * 
     * Creates a preauthorization (hold) on payment method
     * without capturing funds immediately.
     * 
     * @param array $preAuthData Preauthorization request data
     * @return array Preauthorization response with hold details
     * @throws JamboJetApiException
     */
    public function preauthorizePayment(array $preAuthData): array;

    /**
     * Capture preauthorized payment
     * POST /api/nsk/v1/booking/payments/{paymentKey}/capture
     * 
     * Captures funds from a preauthorized payment.
     * Can capture partial amounts if supported.
     * 
     * @param string $paymentKey Payment key of preauthorization
     * @param float $amount Amount to capture (can be less than preauth)
     * @return array Capture confirmation
     * @throws JamboJetApiException
     */
    public function capturePreauthorizedPayment(string $paymentKey, float $amount): array;
}
