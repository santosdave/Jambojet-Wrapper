<?php

namespace SantosDave\JamboJet\Contracts;

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
}
