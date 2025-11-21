<?php

namespace SantosDave\JamboJet\Contracts;

interface SettingsInterface
{
    // ==================== BOOKING SETTINGS ====================

    /**
     * Get general booking settings
     * GET /api/nsk/v1/settings/booking
     * 
     * @param string|null $roleCode Role code (e.g., 'ABD1', 'AIKO', 'RES')
     * @param string|null $eTag ETag for caching (304 Not Modified support)
     */
    public function getBookingSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get checkin settings
     * GET /api/nsk/v1/settings/booking/checkin
     */
    public function getCheckinSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get contact settings
     * GET /api/nsk/v1/settings/booking/contact
     */
    public function getContactSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get customer account settings
     * GET /api/nsk/v1/settings/booking/customerAccount
     */
    public function getCustomerAccountSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get fee settings
     * GET /api/nsk/v1/settings/booking/fee
     */
    public function getFeeSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get flight search settings
     * GET /api/nsk/v1/settings/booking/flightSearch
     */
    public function getFlightSearchSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Create flight search settings (BETA)
     * POST /api/nsk/v1/settings/booking/flightSearch
     */
    public function createFlightSearchSettings(array $settings): array;

    /**
     * Update flight search settings (BETA)
     * PUT /api/nsk/v1/settings/booking/flightSearch
     */
    public function updateFlightSearchSettings(array $settings): array;

    /**
     * Patch flight search settings (BETA)
     * PATCH /api/nsk/v1/settings/booking/flightSearch
     */
    public function patchFlightSearchSettings(array $settings): array;

    /**
     * Get non-role based general booking settings
     * GET /api/nsk/v1/settings/booking/general
     */
    public function getGeneralBookingSettings(?string $eTag = null): array;

    /**
     * Get passenger settings
     * GET /api/nsk/v1/settings/booking/passenger
     */
    public function getPassengerSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Create passenger settings (BETA)
     * POST /api/nsk/v1/settings/booking/passenger
     */
    public function createPassengerSettings(array $settings): array;

    /**
     * Update passenger settings (BETA)
     * PUT /api/nsk/v1/settings/booking/passenger
     */
    public function updatePassengerSettings(array $settings): array;

    /**
     * Patch passenger settings (BETA)
     * PATCH /api/nsk/v1/settings/booking/passenger
     */
    public function patchPassengerSettings(array $settings): array;

    // ==================== PAYMENT SETTINGS ====================

    /**
     * Get payment settings
     * GET /api/nsk/v2/settings/booking/payment
     */
    public function getPaymentSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Create payment settings (BETA)
     * POST /api/nsk/v2/settings/booking/payment
     */
    public function createPaymentSettings(array $settings): array;

    /**
     * Update payment settings (BETA)
     * PUT /api/nsk/v2/settings/booking/payment
     */
    public function updatePaymentSettings(array $settings): array;

    /**
     * Patch payment settings (BETA)
     * PATCH /api/nsk/v2/settings/booking/payment
     */
    public function patchPaymentSettings(array $settings): array;

    /**
     * Get payment codes settings
     * GET /api/nsk/v1/settings/booking/paymentCodes
     */
    public function getPaymentCodesSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get reserve flights settings
     * GET /api/nsk/v1/settings/booking/reserveFlights
     */
    public function getReserveFlightsSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get voucher settings
     * GET /api/nsk/v1/settings/booking/voucher
     */
    public function getVoucherSettings(?string $roleCode = null, ?string $eTag = null): array;

    // ==================== GENERAL SYSTEM SETTINGS ====================

    /**
     * Get application logon settings
     * GET /api/nsk/v1/settings/general/applicationLogon
     */
    public function getApplicationLogonSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get codes settings (payment methods, passenger types, fee codes, SSR codes)
     * GET /api/nsk/v1/settings/general/codes
     */
    public function getCodesSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get operations settings (FLIFO, IROP, checkin, baggage tracking)
     * GET /api/nsk/v1/settings/general/operations
     */
    public function getOperationsSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get organization settings
     * GET /api/nsk/v1/settings/general/organization
     */
    public function getOrganizationSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get session settings
     * GET /api/nsk/v1/settings/general/session
     */
    public function getSessionSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get role-based general system settings
     * GET /api/nsk/v1/settings/system/general
     */
    public function getGeneralSystemSettings(?string $roleCode = null, ?string $eTag = null): array;

    // ==================== OTHER SETTINGS ====================

    /**
     * Get customer program settings
     * GET /api/nsk/v1/settings/customerPrograms
     */
    public function getCustomerProgramSettings(?string $eTag = null): array;

    /**
     * Get e-ticket configuration
     * GET /api/nsk/v1/settings/eTickets
     */
    public function getETicketSettings(?string $eTag = null): array;

    /**
     * Get external message controls settings
     * GET /api/nsk/v1/settings/externalMessageControls
     */
    public function getExternalMessageControlsSettings(?string $eTag = null): array;

    /**
     * Get itinerary settings
     * GET /api/nsk/v1/settings/itinerary
     */
    public function getItinerarySettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get loyalty settings
     * GET /api/nsk/v1/settings/loyalty
     */
    public function getLoyaltySettings(?string $eTag = null): array;

    /**
     * Get notification settings
     * GET /api/nsk/v1/settings/notifications/general
     */
    public function getNotificationSettings(?string $eTag = null): array;

    /**
     * Get phone number validation settings
     * GET /api/nsk/v1/settings/phoneNumberValidation
     */
    public function getPhoneNumberValidationSettings(?string $eTag = null): array;

    /**
     * Get premium services settings
     * GET /api/nsk/v2/settings/premiumServices
     */
    public function getPremiumServicesSettings(?string $eTag = null): array;

    /**
     * Update premium services settings (BETA - System Master only)
     * PUT /api/nsk/v2/settings/premiumServices
     */
    public function updatePremiumServicesSettings(array $settings): array;

    /**
     * Get service bundles settings
     * GET /api/nsk/v1/settings/serviceBundles
     */
    public function getServiceBundlesSettings(?string $eTag = null): array;

    /**
     * Get SkySpeed settings
     * GET /api/nsk/v2/settings/skySpeed
     */
    public function getSkySpeedSettings(?string $eTag = null): array;

    /**
     * Get finance settings
     * GET /api/nsk/v1/settings/systemConfiguration/finance
     */
    public function getFinanceSettings(?string $roleCode = null, ?string $eTag = null): array;

    /**
     * Get traveler notification settings
     * GET /api/nsk/v1/settings/travelerNotification
     */
    public function getTravelerNotificationSettings(?string $eTag = null): array;

    /**
     * Get agency creation settings
     * GET /api/nsk/v1/settings/user/agencyCreation
     */
    public function getAgencyCreationSettings(?string $eTag = null): array;

    /**
     * Get customer creation settings
     * GET /api/nsk/v1/settings/user/customerCreation
     */
    public function getCustomerCreationSettings(?string $eTag = null): array;
}
