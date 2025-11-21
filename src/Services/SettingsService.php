<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\SettingsInterface;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;

class SettingsService implements SettingsInterface
{
    use HandlesApiRequests, ValidatesRequests;
    // ==================== BOOKING SETTINGS ====================

    /**
     * Get general booking settings
     * GET /api/nsk/v1/settings/booking
     */
    public function getBookingSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking', $roleCode, $eTag);
    }

    /**
     * Get checkin settings
     * GET /api/nsk/v1/settings/booking/checkin
     */
    public function getCheckinSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/checkin', $roleCode, $eTag);
    }

    /**
     * Get contact settings
     * GET /api/nsk/v1/settings/booking/contact
     */
    public function getContactSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/contact', $roleCode, $eTag);
    }

    /**
     * Get customer account settings
     * GET /api/nsk/v1/settings/booking/customerAccount
     */
    public function getCustomerAccountSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/customerAccount', $roleCode, $eTag);
    }

    /**
     * Get fee settings
     * GET /api/nsk/v1/settings/booking/fee
     */
    public function getFeeSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/fee', $roleCode, $eTag);
    }

    /**
     * Get flight search settings
     * GET /api/nsk/v1/settings/booking/flightSearch
     */
    public function getFlightSearchSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/flightSearch', $roleCode, $eTag);
    }

    /**
     * Create flight search settings (BETA)
     * POST /api/nsk/v1/settings/booking/flightSearch
     */
    public function createFlightSearchSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->post('api/nsk/v1/settings/booking/flightSearch', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create flight search settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update flight search settings (BETA)
     * PUT /api/nsk/v1/settings/booking/flightSearch
     */
    public function updateFlightSearchSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->put('api/nsk/v1/settings/booking/flightSearch', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update flight search settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch flight search settings (BETA)
     * PATCH /api/nsk/v1/settings/booking/flightSearch
     */
    public function patchFlightSearchSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->patch('api/nsk/v1/settings/booking/flightSearch', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch flight search settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get non-role based general booking settings
     * GET /api/nsk/v1/settings/booking/general
     */
    public function getGeneralBookingSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/general', null, $eTag);
    }

    /**
     * Get passenger settings
     * GET /api/nsk/v1/settings/booking/passenger
     */
    public function getPassengerSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/passenger', $roleCode, $eTag);
    }

    /**
     * Create passenger settings (BETA)
     * POST /api/nsk/v1/settings/booking/passenger
     */
    public function createPassengerSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->post('api/nsk/v1/settings/booking/passenger', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create passenger settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update passenger settings (BETA)
     * PUT /api/nsk/v1/settings/booking/passenger
     */
    public function updatePassengerSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->put('api/nsk/v1/settings/booking/passenger', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update passenger settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch passenger settings (BETA)
     * PATCH /api/nsk/v1/settings/booking/passenger
     */
    public function patchPassengerSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->patch('api/nsk/v1/settings/booking/passenger', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch passenger settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment settings
     * GET /api/nsk/v2/settings/booking/payment
     */
    public function getPaymentSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v2/settings/booking/payment', $roleCode, $eTag);
    }

    /**
     * Create payment settings (BETA)
     * POST /api/nsk/v2/settings/booking/payment
     */
    public function createPaymentSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->post('api/nsk/v2/settings/booking/payment', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create payment settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update payment settings (BETA)
     * PUT /api/nsk/v2/settings/booking/payment
     */
    public function updatePaymentSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->put('api/nsk/v2/settings/booking/payment', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update payment settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch payment settings (BETA)
     * PATCH /api/nsk/v2/settings/booking/payment
     */
    public function patchPaymentSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->patch('api/nsk/v2/settings/booking/payment', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch payment settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get payment codes settings
     * GET /api/nsk/v1/settings/booking/paymentCodes
     */
    public function getPaymentCodesSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/paymentCodes', $roleCode, $eTag);
    }

    /**
     * Get reserve flights settings
     * GET /api/nsk/v1/settings/booking/reserveFlights
     */
    public function getReserveFlightsSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/reserveFlights', $roleCode, $eTag);
    }

    /**
     * Get voucher settings
     * GET /api/nsk/v1/settings/booking/voucher
     */
    public function getVoucherSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/booking/voucher', $roleCode, $eTag);
    }

    /**
     * Get application logon settings
     * GET /api/nsk/v1/settings/general/applicationLogon
     */
    public function getApplicationLogonSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/general/applicationLogon', $roleCode, $eTag);
    }

    /**
     * Get codes settings (payment methods, passenger types, fee codes, SSR codes)
     * GET /api/nsk/v1/settings/general/codes
     * 
     * Returns system-wide enumerations:
     * - Payment method codes
     * - Passenger type codes
     * - Discount codes
     * - Fee codes
     * - SSR codes
     * - Fare types
     * - Product classes
     */
    public function getCodesSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/general/codes', $roleCode, $eTag);
    }

    /**
     * Get operations settings (FLIFO, IROP, checkin, baggage tracking)
     * GET /api/nsk/v1/settings/general/operations
     * 
     * Controls operational permissions:
     * - FLIFO access (Flight Following)
     * - IROP permissions
     * - Checkin restrictions
     * - Baggage tracking
     * - Inventory management
     * - Report access
     */
    public function getOperationsSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/general/operations', $roleCode, $eTag);
    }

    /**
     * Get organization settings
     * GET /api/nsk/v1/settings/general/organization
     */
    public function getOrganizationSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/general/organization', $roleCode, $eTag);
    }

    /**
     * Get session settings
     * GET /api/nsk/v1/settings/general/session
     * 
     * Includes:
     * - Session timeout settings
     * - Concurrency settings
     * - Session management rules
     */
    public function getSessionSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/general/session', $roleCode, $eTag);
    }

    /**
     * Get role-based general system settings
     * GET /api/nsk/v1/settings/system/general
     */
    public function getGeneralSystemSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/system/general', $roleCode, $eTag);
    }

    /**
     * Get customer program settings
     * GET /api/nsk/v1/settings/customerPrograms
     */
    public function getCustomerProgramSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/customerPrograms', null, $eTag);
    }

    /**
     * Get e-ticket configuration
     * GET /api/nsk/v1/settings/eTickets
     */
    public function getETicketSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/eTickets', null, $eTag);
    }

    /**
     * Get external message controls settings
     * GET /api/nsk/v1/settings/externalMessageControls
     */
    public function getExternalMessageControlsSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/externalMessageControls', null, $eTag);
    }

    /**
     * Get itinerary settings
     * GET /api/nsk/v1/settings/itinerary
     */
    public function getItinerarySettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/itinerary', $roleCode, $eTag);
    }

    /**
     * Get loyalty settings
     * GET /api/nsk/v1/settings/loyalty
     */
    public function getLoyaltySettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/loyalty', null, $eTag);
    }

    /**
     * Get notification settings
     * GET /api/nsk/v1/settings/notifications/general
     */
    public function getNotificationSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/notifications/general', null, $eTag);
    }

    /**
     * Get phone number validation settings
     * GET /api/nsk/v1/settings/phoneNumberValidation
     */
    public function getPhoneNumberValidationSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/phoneNumberValidation', null, $eTag);
    }

    /**
     * Get premium services settings
     * GET /api/nsk/v2/settings/premiumServices
     * 
     * Controls advanced features:
     * - Government data protection
     * - APIS (US, UK, Korea, Taiwan, Canada)
     * - Secure Flight
     * - Document check
     * - Bundles
     * - Travel notifications
     */
    public function getPremiumServicesSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v2/settings/premiumServices', null, $eTag);
    }

    /**
     * Update premium services settings (BETA - System Master only)
     * PUT /api/nsk/v2/settings/premiumServices
     * 
     * ⚠️ Requires System Master user permissions
     */
    public function updatePremiumServicesSettings(array $settings): array
    {
        $this->validateSettings($settings);

        try {
            return $this->put('api/nsk/v2/settings/premiumServices', $settings);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update premium services settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get service bundles settings
     * GET /api/nsk/v1/settings/serviceBundles
     */
    public function getServiceBundlesSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/serviceBundles', null, $eTag);
    }

    /**
     * Get SkySpeed settings
     * GET /api/nsk/v2/settings/skySpeed
     */
    public function getSkySpeedSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v2/settings/skySpeed', null, $eTag);
    }

    /**
     * Get finance settings
     * GET /api/nsk/v1/settings/systemConfiguration/finance
     */
    public function getFinanceSettings(?string $roleCode = null, ?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/systemConfiguration/finance', $roleCode, $eTag);
    }

    /**
     * Get traveler notification settings
     * GET /api/nsk/v1/settings/travelerNotification
     */
    public function getTravelerNotificationSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/travelerNotification', null, $eTag);
    }

    /**
     * Get agency creation settings
     * GET /api/nsk/v1/settings/user/agencyCreation
     */
    public function getAgencyCreationSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/user/agencyCreation', null, $eTag);
    }

    /**
     * Get customer creation settings
     * GET /api/nsk/v1/settings/user/customerCreation
     */
    public function getCustomerCreationSettings(?string $eTag = null): array
    {
        return $this->getSettings('api/nsk/v1/settings/user/customerCreation', null, $eTag);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Base method for getting settings with role code and ETag support
     * 
     * @param string $endpoint API endpoint
     * @param string|null $roleCode Role code for role-based settings
     * @param string|null $eTag ETag for caching (304 Not Modified)
     * @return array Settings data
     * @throws JamboJetApiException
     */
    private function getSettings(string $endpoint, ?string $roleCode = null, ?string $eTag = null): array
    {
        if ($roleCode !== null) {
            $this->validateRoleCode($roleCode);
        }

        $params = [];
        if ($roleCode !== null) {
            $params['roleCode'] = $roleCode;
        }
        if ($eTag !== null) {
            $params['eTag'] = $eTag;
        }

        try {
            return $this->get($endpoint, $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get settings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate role code
     * 
     * @param string $roleCode Role code to validate
     * @throws \InvalidArgumentException
     */
    private function validateRoleCode(string $roleCode): void
    {
        if (empty($roleCode)) {
            throw new \InvalidArgumentException('Role code cannot be empty');
        }

        // Role code validation - add more specific validation if needed
        if (strlen($roleCode) > 10) {
            throw new \InvalidArgumentException('Role code must be maximum 10 characters');
        }
    }

    /**
     * Validate settings data
     * 
     * @param array $settings Settings data to validate
     * @throws \InvalidArgumentException
     */
    private function validateSettings(array $settings): void
    {
        if (empty($settings)) {
            throw new \InvalidArgumentException('Settings data cannot be empty');
        }
    }
}
