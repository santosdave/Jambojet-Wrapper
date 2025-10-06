<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\AddOnsInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Add-ons Service for JamboJet NSK API
 * 
 * Handles all add-on services including seats, baggage, meals, insurance, activities, etc.
 * Base endpoints: /api/nsk/v{version}/addOns and /api/nsk/v{version}/booking/addOns
 * 
 * Supported endpoints:
 * - POST /api/nsk/v1/addOns/activities - Sell activity
 * - POST /api/nsk/v1/addOns/activities/available - Get available activities
 * - POST /api/nsk/v2/addOns/activities/available - Get paged available activities
 * - POST /api/nsk/v1/addOns/activities/quote - Quote activity
 * - POST /api/nsk/v1/addOns/cars - Sell car rental
 * - POST /api/nsk/v1/addOns/cars/available - Get available cars
 * - POST /api/nsk/v1/addOns/cars/quote - Quote car rental
 * - POST /api/nsk/v1/addOns/hotels - Sell hotel
 * - POST /api/nsk/v1/addOns/hotels/available - Get available hotels
 * - POST /api/nsk/v1/addOns/hotels/quote - Quote hotel
 * - POST /api/nsk/v1/addOns/insurance - Sell insurance
 * - POST /api/nsk/v1/addOns/insurance/available - Get available insurance
 * - POST /api/nsk/v1/addOns/insurance/quote - Quote insurance
 * - POST /api/nsk/v1/addOns/loungeAccess - Sell lounge access
 * - POST /api/nsk/v1/addOns/merchandise - Sell merchandise
 * - POST /api/nsk/v1/addOns/petTransport - Sell pet transport
 * - POST /api/nsk/v1/addOns/seats - Sell seat assignment
 * - POST /api/nsk/v1/addOns/serviceCharges - Sell service charges
 * - POST /api/nsk/v1/addOns/specialServiceRequests - Sell SSR
 * - POST /api/nsk/v1/addOns/bags - Sell baggage
 * - GET /api/nsk/v1/booking/addOns/* - Various booking add-on retrievals
 * - GET /api/nsk/v#/resources/addOns/vendors - Get vendors by type
 * 
 * @package SantosDave\JamboJet\Services
 */
class AddOnsService implements AddOnsInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // CORE ADD-ON SELLING METHODS
    // =================================================================

    /**
     * Add activity to booking
     * 
     * POST /api/nsk/v1/addOns/activities
     * Sells new activity items to the booking
     * 
     * @param array $activityData Activity selling request data
     * @return array Activity add-on details
     * @throws JamboJetApiException
     */
    public function addActivity(array $activityData): array
    {
        $this->validateTokenRequest($activityData);

        try {
            return $this->post('api/nsk/v1/addOns/activities', $activityData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add activity: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add insurance to booking
     * 
     * POST /api/nsk/v1/addOns/insurance
     * Adds travel insurance products to booking
     * 
     * @param array $insuranceData Insurance product data
     * @return array Insurance addition response
     * @throws JamboJetApiException
     */
    public function addInsurance(array $insuranceData): array
    {
        $this->validateInsuranceRequest($insuranceData);

        try {
            return $this->post('api/nsk/v1/addOns/insurance', $insuranceData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add insurance: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add lounge access
     * 
     * POST /api/nsk/v1/addOns/loungeAccess
     * Adds airport lounge access to booking
     * 
     * @param array $loungeData Lounge access data
     * @return array Lounge access addition response
     * @throws JamboJetApiException
     */
    public function addLoungeAccess(array $loungeData): array
    {
        $this->validateLoungeAccessRequest($loungeData);

        try {
            return $this->post('api/nsk/v1/addOns/loungeAccess', $loungeData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add lounge access: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add merchandise
     * 
     * POST /api/nsk/v1/addOns/merchandise
     * Adds merchandise items to booking
     * 
     * @param array $merchandiseData Merchandise data
     * @return array Merchandise addition response
     * @throws JamboJetApiException
     */
    public function addMerchandise(array $merchandiseData): array
    {
        $this->validateMerchandiseRequest($merchandiseData);

        try {
            return $this->post('api/nsk/v1/addOns/merchandise', $merchandiseData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add merchandise: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add pet transport
     * 
     * POST /api/nsk/v1/addOns/petTransport
     * Adds pet transport services to booking
     * 
     * @param array $petData Pet transport data
     * @return array Pet transport addition response
     * @throws JamboJetApiException
     */
    public function addPetTransport(array $petData): array
    {
        $this->validatePetTransportRequest($petData);

        try {
            return $this->post('api/nsk/v1/addOns/petTransport', $petData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add pet transport: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add seat assignment
     * 
     * POST /api/nsk/v1/addOns/seats
     * Assigns seats to passengers in the booking
     * 
     * @param array $seatData Seat assignment request data
     * @return array Seat assignment add-on details
     * @throws JamboJetApiException
     */
    public function addSeatAssignment(array $seatData): array
    {
        $this->validateSeatRequest($seatData);

        try {
            return $this->post('api/nsk/v1/addOns/seats', $seatData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add seat assignment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add service charges
     * 
     * POST /api/nsk/v1/addOns/serviceCharges
     * Adds service charges to the booking
     * 
     * @param array $chargeData Service charge request data
     * @return array Service charge add-on details
     * @throws JamboJetApiException
     */
    public function addServiceCharges(array $chargeData): array
    {
        $this->validateServiceChargeRequest($chargeData);

        try {
            return $this->post('api/nsk/v1/addOns/serviceCharges', $chargeData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add service charges: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add special service requests (SSR)
     * 
     * POST /api/nsk/v1/addOns/specialServiceRequests
     * Adds special service requests to passengers
     * 
     * @param array $ssrData SSR request data
     * @return array SSR add-on details
     * @throws JamboJetApiException
     */
    public function addSpecialServiceRequest(array $ssrData): array
    {
        $this->validateSsrRequest($ssrData);

        try {
            return $this->post('api/nsk/v1/addOns/specialServiceRequests', $ssrData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add special service request: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add baggage to booking
     * 
     * POST /api/nsk/v1/addOns/bags
     * Adds baggage allowances or excess baggage to booking
     * 
     * @param array $baggageData Baggage addition data
     * @return array Baggage addition response
     * @throws JamboJetApiException
     */
    public function addBaggage(array $baggageData): array
    {
        $this->validateBaggageRequest($baggageData);

        try {
            return $this->post('api/nsk/v1/addOns/bags', $baggageData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add baggage: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // ADDITIONAL ADD-ON SERVICES (Hotels, Cars)
    // =================================================================

    /**
     * Add hotel to booking
     * 
     * POST /api/nsk/v1/addOns/hotels
     * Sells hotel accommodation to the booking
     * 
     * @param array $hotelData Hotel selling request data
     * @return array Hotel add-on details
     * @throws JamboJetApiException
     */
    public function addHotel(array $hotelData): array
    {
        $this->validateTokenRequest($hotelData);

        try {
            return $this->post('api/nsk/v1/addOns/hotels', $hotelData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add hotel: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add car rental to booking
     * 
     * POST /api/nsk/v1/addOns/cars
     * Sells car rental to the booking
     * 
     * @param array $carData Car rental selling request data
     * @return array Car rental add-on details
     * @throws JamboJetApiException
     */
    public function addCarRental(array $carData): array
    {
        $this->validateTokenRequest($carData);

        try {
            return $this->post('api/nsk/v1/addOns/cars', $carData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add car rental: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // AVAILABILITY CHECKING METHODS
    // =================================================================

    /**
     * Get available activities
     * 
     * POST /api/nsk/v2/addOns/activities/available
     * Retrieves available activities with pagination
     * 
     * @param array $criteria Activity search criteria
     * @return array Available activities
     * @throws JamboJetApiException
     */
    public function getAvailableActivities(array $criteria): array
    {
        $this->validateActivityAvailabilityRequest($criteria);

        try {
            return $this->post('api/nsk/v2/addOns/activities/available', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get available activities: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get available hotels
     * 
     * POST /api/nsk/v1/addOns/hotels/available
     * Retrieves available hotels
     * 
     * @param array $criteria Hotel search criteria
     * @return array Available hotels
     * @throws JamboJetApiException
     */
    public function getAvailableHotels(array $criteria): array
    {
        $this->validateHotelAvailabilityRequest($criteria);

        try {
            return $this->post('api/nsk/v1/addOns/hotels/available', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get available hotels: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get available cars
     * 
     * POST /api/nsk/v1/addOns/cars/available
     * Retrieves available car rentals
     * 
     * @param array $criteria Car rental search criteria
     * @return array Available cars
     * @throws JamboJetApiException
     */
    public function getAvailableCars(array $criteria): array
    {
        $this->validateCarAvailabilityRequest($criteria);

        try {
            return $this->post('api/nsk/v1/addOns/cars/available', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get available cars: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get available insurance
     * 
     * POST /api/nsk/v1/addOns/insurance/available
     * Retrieves available insurance products
     * 
     * @param array $criteria Insurance search criteria
     * @return array Available insurance
     * @throws JamboJetApiException
     */
    public function getAvailableInsurance(array $criteria): array
    {
        $this->validateInsuranceAvailabilityRequest($criteria);

        try {
            return $this->post('api/nsk/v1/addOns/insurance/available', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get available insurance: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // QUOTE METHODS
    // =================================================================

    /**
     * Quote activity
     * 
     * POST /api/nsk/v1/addOns/activities/quote
     * Gets pricing quote for activity without adding to booking
     * 
     * @param array $quoteData Activity quote data
     * @return array Activity quote
     * @throws JamboJetApiException
     */
    public function quoteActivity(array $quoteData): array
    {
        $this->validateActivityQuoteRequest($quoteData);

        try {
            return $this->post('api/nsk/v1/addOns/activities/quote', $quoteData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to quote activity: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Quote hotel
     * 
     * POST /api/nsk/v1/addOns/hotels/quote
     * Gets a quote for a hotel
     * 
     * @param array $quoteRequest Quote request data
     * @return array Hotel quote
     * @throws JamboJetApiException
     */
    public function quoteHotel(array $quoteRequest): array
    {
        $this->validateQuoteTokenRequest($quoteRequest);

        try {
            return $this->post('api/nsk/v1/addOns/hotels/quote', $quoteRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to quote hotel: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Quote car rental
     * 
     * POST /api/nsk/v1/addOns/cars/quote
     * Gets a quote for a car rental
     * 
     * @param array $quoteRequest Quote request data
     * @return array Car rental quote
     * @throws JamboJetApiException
     */
    public function quoteCar(array $quoteRequest): array
    {
        $this->validateQuoteTokenRequest($quoteRequest);

        try {
            return $this->post('api/nsk/v1/addOns/cars/quote', $quoteRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to quote car rental: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Quote insurance
     * 
     * POST /api/nsk/v1/addOns/insurance/quote
     * Gets a quote for insurance
     * 
     * @param array $quoteRequest Quote request data
     * @return array Insurance quote
     * @throws JamboJetApiException
     */
    public function quoteInsurance(array $quoteRequest): array
    {
        $this->validateQuoteTokenRequest($quoteRequest);

        try {
            return $this->post('api/nsk/v1/addOns/insurance/quote', $quoteRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to quote insurance: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // BOOKING STATE ADD-ON RETRIEVAL METHODS
    // =================================================================

    /**
     * Get booking add-ons by type
     * 
     * GET /api/nsk/v1/booking/addOns/{type}
     * Retrieves add-ons from booking state by type
     * 
     * @param string $type Add-on type (activities, hotels, insurance, etc.)
     * @param array $parameters Optional query parameters
     * @return array Add-ons of specified type
     * @throws JamboJetApiException
     */
    public function getBookingAddOnsByType(string $type, array $parameters = []): array
    {
        $validTypes = ['activities', 'hotels', 'insurance', 'cars', 'lounge', 'merchandise'];

        if (!in_array($type, $validTypes)) {
            throw new JamboJetValidationException("Invalid add-on type: {$type}");
        }

        try {
            return $this->get("api/nsk/v1/booking/addOns/{$type}", $parameters);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                "Failed to get booking {$type} add-ons: " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get add-on payment methods
     * 
     * GET /api/nsk/v1/booking/addons/payments
     * Gets all available add-on payment methods for booking
     * 
     * @return array Available payment methods for add-ons
     * @throws JamboJetApiException
     */
    public function getAddOnPaymentMethods(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/addons/payments');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get add-on payment methods: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific add-on payment methods
     * 
     * GET /api/nsk/v1/booking/addons/{addOnKey}/payments
     * Gets payment methods for a specific add-on
     * 
     * @param string $addOnKey The unique add-on key
     * @return array Payment methods for the specific add-on
     * @throws JamboJetApiException
     */
    public function getSpecificAddOnPaymentMethods(string $addOnKey): array
    {
        if (empty($addOnKey)) {
            throw new JamboJetValidationException('Add-on key is required');
        }

        try {
            return $this->get("api/nsk/v1/booking/addons/{$addOnKey}/payments");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get specific add-on payment methods: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // RESOURCE/VENDOR METHODS
    // =================================================================

    /**
     * Get vendors by type
     * 
     * GET /api/nsk/v2/resources/addOns/vendors
     * Retrieves vendors for specific add-on types
     * 
     * @param array $parameters Vendor search parameters
     * @return array Vendors list
     * @throws JamboJetApiException
     */
    public function getVendors(array $parameters = []): array
    {
        $this->validateVendorRequest($parameters);

        try {
            $endpoint = isset($parameters['version']) && $parameters['version'] === 'v2' ?
                'api/nsk/v2/resources/addOns/vendors' : 'api/nsk/v1/resources/addOns/vendors';
            return $this->get($endpoint, $parameters);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get vendors: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // =================================================================
    // VALIDATION METHODS
    // =================================================================

    /**
     * Validate token request (common for most add-on selling)
     */
    private function validateTokenRequest(array $data): void
    {
        $this->validateRequiredFields($data, ['productKey']);
    }

    /**
     * Validate sell insurance request
     * 
     * @param array $data Insurance sell data
     * @throws JamboJetValidationException
     */
    private function validateSellInsuranceRequest(array $data): void
    {
        // Validate required fields for insurance selling
        $this->validateRequired($data, ['productKey', 'coverageType']);

        // Validate product key
        $this->validateProductKey($data['productKey']);

        // Validate coverage type
        $validCoverageTypes = ['Trip', 'Medical', 'Cancellation', 'Baggage', 'Comprehensive'];
        if (!in_array($data['coverageType'], $validCoverageTypes)) {
            throw new JamboJetValidationException(
                'Invalid coverage type. Expected one of: ' . implode(', ', $validCoverageTypes)
            );
        }

        // Validate coverage amount if provided
        if (isset($data['coverageAmount'])) {
            if (!is_numeric($data['coverageAmount']) || $data['coverageAmount'] <= 0) {
                throw new JamboJetValidationException(
                    'Coverage amount must be a positive number'
                );
            }
        }

        // Validate passenger assignments
        if (isset($data['passengerAssignments'])) {
            $this->validatePassengerAssignments($data['passengerAssignments']);
        }

        // Validate policy period if provided
        if (isset($data['policyPeriod'])) {
            $this->validatePolicyPeriod($data['policyPeriod']);
        }

        // Validate beneficiaries if provided
        if (isset($data['beneficiaries']) && is_array($data['beneficiaries'])) {
            foreach ($data['beneficiaries'] as $beneficiary) {
                $this->validateBeneficiary($beneficiary);
            }
        }
    }



    /**
     * Validate paged activity request (v2)
     * 
     * @param array $data Paged activity request data
     * @throws JamboJetValidationException
     */
    private function validatePagedActivityRequest(array $data): void
    {
        // Validate location or destination
        if (isset($data['location'])) {
            $this->validateLocation($data['location']);
        }

        // Validate activity category if provided
        if (isset($data['activityCategory'])) {
            $validCategories = ['Adventure', 'Cultural', 'Entertainment', 'Food', 'Shopping', 'Sightseeing', 'Sports'];
            if (!in_array($data['activityCategory'], $validCategories)) {
                throw new JamboJetValidationException(
                    'Invalid activity category. Expected one of: ' . implode(', ', $validCategories)
                );
            }
        }

        // Validate date range if provided
        if (isset($data['dateRange'])) {
            $this->validateDateRange($data['dateRange']);
        }

        // Validate paging parameters
        if (isset($data['pageSize'])) {
            $this->validateNumericRanges($data, ['pageSize' => ['min' => 1, 'max' => 100]]);
        }

        if (isset($data['pageIndex'])) {
            $this->validateNumericRanges($data, ['pageIndex' => ['min' => 0, 'max' => 1000]]);
        }

        // Validate participant count if provided
        if (isset($data['participantCount'])) {
            $this->validateNumericRanges($data, ['participantCount' => ['min' => 1, 'max' => 20]]);
        }
    }

    /**
     * Validate activity simple request (v1)
     * 
     * @param array $data Simple activity request data
     * @throws JamboJetValidationException
     */
    private function validateActivitySimpleRequest(array $data): void
    {
        // Validate destination/location
        if (isset($data['destination'])) {
            $this->validateFormats($data, ['destination' => 'airport_code']);
        } elseif (isset($data['location'])) {
            $this->validateLocation($data['location']);
        }

        // Validate activity date if provided
        if (isset($data['activityDate'])) {
            $this->validateFormats($data, ['activityDate' => 'date']);

            // Activity date should be in the future
            $activityDate = new \DateTime($data['activityDate']);
            $now = new \DateTime();

            if ($activityDate <= $now) {
                throw new JamboJetValidationException(
                    'Activity date must be in the future'
                );
            }
        }

        // Validate duration if provided
        if (isset($data['duration'])) {
            $validDurations = ['Half Day', 'Full Day', 'Multi Day', 'Evening'];
            if (!in_array($data['duration'], $validDurations)) {
                throw new JamboJetValidationException(
                    'Invalid duration. Expected one of: ' . implode(', ', $validDurations)
                );
            }
        }

        // Validate price range if provided
        if (isset($data['priceRange'])) {
            $this->validatePriceRange($data['priceRange']);
        }
    }

    /**
     * Validate hotel simple request
     * 
     * @param array $data Hotel simple request data
     * @throws JamboJetValidationException
     */
    private function validateHotelSimpleRequest(array $data): void
    {
        // Hotel searches typically need location and dates
        if (isset($data['location'])) {
            $this->validateLocation($data['location']);
        }

        if (isset($data['checkInDate'])) {
            $this->validateFormats($data, ['checkInDate' => 'date']);
        }

        if (isset($data['checkOutDate'])) {
            $this->validateFormats($data, ['checkOutDate' => 'date']);

            // Check-out must be after check-in
            if (isset($data['checkInDate'])) {
                $checkIn = new \DateTime($data['checkInDate']);
                $checkOut = new \DateTime($data['checkOutDate']);

                if ($checkOut <= $checkIn) {
                    throw new JamboJetValidationException(
                        'Check-out date must be after check-in date'
                    );
                }
            }
        }

        // Validate room requirements
        if (isset($data['rooms'])) {
            $this->validateNumericRanges($data, ['rooms' => ['min' => 1, 'max' => 10]]);
        }

        if (isset($data['adults'])) {
            $this->validateNumericRanges($data, ['adults' => ['min' => 1, 'max' => 20]]);
        }

        if (isset($data['children'])) {
            $this->validateNumericRanges($data, ['children' => ['min' => 0, 'max' => 15]]);
        }

        // Validate star rating if provided
        if (isset($data['starRating'])) {
            $this->validateNumericRanges($data, ['starRating' => ['min' => 1, 'max' => 5]]);
        }
    }

    /**
     * Validate car simple request
     * 
     * @param array $data Car rental simple request data
     * @throws JamboJetValidationException
     */
    private function validateCarSimpleRequest(array $data): void
    {
        // Car rentals need pickup location and dates
        if (isset($data['pickupLocation'])) {
            $this->validateLocation($data['pickupLocation']);
        }

        if (isset($data['dropoffLocation'])) {
            $this->validateLocation($data['dropoffLocation']);
        }

        if (isset($data['pickupDate'])) {
            $this->validateFormats($data, ['pickupDate' => 'datetime']);
        }

        if (isset($data['dropoffDate'])) {
            $this->validateFormats($data, ['dropoffDate' => 'datetime']);

            // Drop-off must be after pickup
            if (isset($data['pickupDate'])) {
                $pickup = new \DateTime($data['pickupDate']);
                $dropoff = new \DateTime($data['dropoffDate']);

                if ($dropoff <= $pickup) {
                    throw new JamboJetValidationException(
                        'Drop-off date must be after pickup date'
                    );
                }
            }
        }

        // Validate car category if provided
        if (isset($data['carCategory'])) {
            $validCategories = ['Economy', 'Compact', 'Intermediate', 'Standard', 'Full Size', 'Premium', 'Luxury', 'SUV'];
            if (!in_array($data['carCategory'], $validCategories)) {
                throw new JamboJetValidationException(
                    'Invalid car category. Expected one of: ' . implode(', ', $validCategories)
                );
            }
        }

        // Validate driver age if provided
        if (isset($data['driverAge'])) {
            $this->validateNumericRanges($data, ['driverAge' => ['min' => 18, 'max' => 85]]);
        }
    }

    /**
     * Validate insurance simple request
     * 
     * @param array $data Insurance simple request data
     * @throws JamboJetValidationException
     */
    private function validateInsuranceSimpleRequest(array $data): void
    {
        // Insurance requests need trip details and coverage preferences
        if (isset($data['tripDestination'])) {
            $this->validateFormats($data, ['tripDestination' => 'country_code']);
        }

        if (isset($data['tripStartDate'])) {
            $this->validateFormats($data, ['tripStartDate' => 'date']);
        }

        if (isset($data['tripEndDate'])) {
            $this->validateFormats($data, ['tripEndDate' => 'date']);

            // Trip end must be after start
            if (isset($data['tripStartDate'])) {
                $start = new \DateTime($data['tripStartDate']);
                $end = new \DateTime($data['tripEndDate']);

                if ($end <= $start) {
                    throw new JamboJetValidationException(
                        'Trip end date must be after start date'
                    );
                }
            }
        }

        // Validate traveler count
        if (isset($data['travelerCount'])) {
            $this->validateNumericRanges($data, ['travelerCount' => ['min' => 1, 'max' => 20]]);
        }

        // Validate trip cost for coverage calculation
        if (isset($data['tripCost'])) {
            if (!is_numeric($data['tripCost']) || $data['tripCost'] <= 0) {
                throw new JamboJetValidationException(
                    'Trip cost must be a positive number'
                );
            }
        }

        // Validate coverage preferences
        if (isset($data['coveragePreferences']) && is_array($data['coveragePreferences'])) {
            $validCoverages = ['Trip Cancellation', 'Medical', 'Baggage', 'Trip Delay', 'Emergency Evacuation'];
            foreach ($data['coveragePreferences'] as $coverage) {
                if (!in_array($coverage, $validCoverages)) {
                    throw new JamboJetValidationException(
                        'Invalid coverage preference. Expected one of: ' . implode(', ', $validCoverages)
                    );
                }
            }
        }
    }

    /**
     * Validate quote token request
     */
    private function validateQuoteTokenRequest(array $data): void
    {
        $this->validateRequiredFields($data, ['productKey']);
    }


    // =================================================================
    // VALIDATION METHODS - COMPREHENSIVE AND UPDATED
    // =================================================================

    /**
     * Validate activity selling request
     * 
     * @param array $data Activity data
     * @throws JamboJetValidationException
     */
    private function validateActivityRequest(array $data): void
    {
        $this->validateRequired($data, ['productKey', 'quantity']);

        // Validate product key
        $this->validateProductKey($data['productKey']);

        // Validate quantity
        $this->validateNumericRanges($data, ['quantity' => ['min' => 1, 'max' => 10]]);

        if (!is_int($data['quantity'])) {
            throw new JamboJetValidationException(
                'Quantity must be an integer',
                400
            );
        }

        // Validate passenger assignments if provided
        if (isset($data['passengerAssignments'])) {
            $this->validatePassengerAssignments($data['passengerAssignments']);
        }

        // Validate activity date if provided
        if (isset($data['activityDate'])) {
            $this->validateFormats($data, ['activityDate' => 'date']);

            // Activity date cannot be in the past
            $activityDate = new \DateTime($data['activityDate']);
            $now = new \DateTime();

            if ($activityDate < $now) {
                throw new JamboJetValidationException(
                    'Activity date cannot be in the past',
                    400
                );
            }
        }

        // Validate vendor code if provided
        if (isset($data['vendorCode'])) {
            $this->validateStringLengths($data, ['vendorCode' => ['max' => 10]]);
        }

        // Validate special requirements if provided
        if (isset($data['specialRequirements'])) {
            $this->validateStringLengths($data, ['specialRequirements' => ['max' => 500]]);
        }
    }

    /**
     * Validate seat assignment request
     * 
     * @param array $data Seat data
     * @throws JamboJetValidationException
     */
    private function validateSeatRequest(array $data): void
    {
        $this->validateRequired($data, ['seats']);

        if (!is_array($data['seats']) || empty($data['seats'])) {
            throw new JamboJetValidationException(
                'Seats must be a non-empty array',
                400
            );
        }

        foreach ($data['seats'] as $index => $seat) {
            $this->validateSeatAssignment($seat, $index);
        }

        // Validate seat map version if provided
        if (isset($data['seatMapVersion'])) {
            $this->validateStringLengths($data, ['seatMapVersion' => ['max' => 50]]);
        }

        // Validate auto-assign preferences if provided
        if (isset($data['autoAssignPreferences'])) {
            $this->validateAutoAssignPreferences($data['autoAssignPreferences']);
        }
    }

    /**
     * Validate individual seat assignment
     * 
     * @param array $seat Seat assignment data
     * @param int $index Seat index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateSeatAssignment(array $seat, int $index): void
    {
        $this->validateRequired($seat, ['seatNumber', 'segmentKey', 'passengerKey']);

        // Validate seat number format (e.g., "12A", "05F")
        if (!preg_match('/^\d{1,3}[A-Z]$/', $seat['seatNumber'])) {
            throw new JamboJetValidationException(
                "Invalid seat number format at index {$index}. Expected format: 12A",
                400
            );
        }

        // Validate keys are not empty
        if (empty(trim($seat['segmentKey'])) || empty(trim($seat['passengerKey']))) {
            throw new JamboJetValidationException(
                "Segment key and passenger key cannot be empty at index {$index}",
                400
            );
        }

        // Validate seat characteristics if provided
        if (isset($seat['characteristics'])) {
            $this->validateSeatCharacteristics($seat['characteristics'], $index);
        }

        // Validate seat fees if provided
        if (isset($seat['fees'])) {
            foreach ($seat['fees'] as $feeIndex => $fee) {
                if (isset($fee['amount'])) {
                    $this->validateFormats(['amount' => $fee['amount']], ['amount' => 'non_negative_number']);
                }
            }
        }
    }

    /**
     * Validate seat characteristics
     * 
     * @param array $characteristics Seat characteristics
     * @param int $seatIndex Seat index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateSeatCharacteristics(array $characteristics, int $seatIndex): void
    {
        $validCharacteristics = [
            'Window',
            'Aisle',
            'Middle',
            'ExtraLegroom',
            'Premium',
            'Blocked',
            'Occupied',
            'Infant',
            'Emergency',
            'Restricted'
        ];

        foreach ($characteristics as $characteristic) {
            if (!in_array($characteristic, $validCharacteristics)) {
                throw new JamboJetValidationException(
                    "Invalid seat characteristic at seat index {$seatIndex}: {$characteristic}",
                    400
                );
            }
        }
    }

    /**
     * Validate baggage request
     * 
     * @param array $data Baggage data
     * @throws JamboJetValidationException
     */
    private function validateBaggageRequest(array $data): void
    {
        $this->validateRequired($data, ['bags']);

        if (!is_array($data['bags']) || empty($data['bags'])) {
            throw new JamboJetValidationException(
                'Bags must be a non-empty array',
                400
            );
        }

        foreach ($data['bags'] as $index => $bag) {
            $this->validateBaggageItem($bag, $index);
        }
    }

    /**
     * Validate individual baggage item
     * 
     * @param array $bag Baggage item
     * @param int $index Bag index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateBaggageItem(array $bag, int $index): void
    {
        $this->validateRequired($bag, ['baggageType', 'weight', 'passengerKey']);

        // Validate baggage type
        $validBaggageTypes = ['Checked', 'CarryOn', 'Personal', 'Excess', 'Oversize', 'Sports'];
        if (!in_array($bag['baggageType'], $validBaggageTypes)) {
            throw new JamboJetValidationException(
                "Invalid baggage type at index {$index}. Expected one of: " . implode(', ', $validBaggageTypes),
                400
            );
        }

        // Validate weight
        $this->validateFormats(['weight' => $bag['weight']], ['weight' => 'positive_number']);

        // Validate weight limits based on baggage type
        $maxWeights = [
            'Checked' => 50,
            'CarryOn' => 10,
            'Personal' => 5,
            'Excess' => 50,
            'Oversize' => 50,
            'Sports' => 50
        ];

        if (isset($maxWeights[$bag['baggageType']]) && $bag['weight'] > $maxWeights[$bag['baggageType']]) {
            throw new JamboJetValidationException(
                "Weight exceeds maximum limit for {$bag['baggageType']} baggage at index {$index}",
                400
            );
        }

        // Validate dimensions if provided
        if (isset($bag['dimensions'])) {
            $this->validateBaggageDimensions($bag['dimensions'], $index);
        }

        // Validate journey keys if provided
        if (isset($bag['journeyKeys'])) {
            if (!is_array($bag['journeyKeys']) || empty($bag['journeyKeys'])) {
                throw new JamboJetValidationException(
                    "Journey keys must be a non-empty array at baggage index {$index}",
                    400
                );
            }
        }

        // Validate special handling if provided
        if (isset($bag['specialHandling'])) {
            $this->validateSpecialHandling($bag['specialHandling'], $index);
        }
    }

    /**
     * Validate baggage dimensions
     * 
     * @param array $dimensions Baggage dimensions
     * @param int $bagIndex Bag index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateBaggageDimensions(array $dimensions, int $bagIndex): void
    {
        $this->validateRequired($dimensions, ['length', 'width', 'height']);

        $dimensionFields = ['length', 'width', 'height'];
        foreach ($dimensionFields as $field) {
            $this->validateFormats([$field => $dimensions[$field]], [$field => 'positive_number']);

            // Reasonable dimension limits (in cm)
            if ($dimensions[$field] > 300) {
                throw new JamboJetValidationException(
                    "Baggage {$field} exceeds maximum limit at index {$bagIndex}",
                    400
                );
            }
        }

        // Validate unit if provided
        if (isset($dimensions['unit'])) {
            $validUnits = ['cm', 'in'];
            if (!in_array($dimensions['unit'], $validUnits)) {
                throw new JamboJetValidationException(
                    "Invalid dimension unit at baggage index {$bagIndex}. Expected: cm or in",
                    400
                );
            }
        }
    }

    /**
     * Validate special handling requirements
     * 
     * @param array $specialHandling Special handling requirements
     * @param int $bagIndex Bag index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateSpecialHandling(array $specialHandling, int $bagIndex): void
    {
        $validHandlingTypes = [
            'Fragile',
            'LiveAnimals',
            'Perishable',
            'Valuable',
            'Medical',
            'Sports',
            'Musical',
            'Oversized',
            'Hazardous',
            'Diplomatic'
        ];

        foreach ($specialHandling as $handling) {
            if (!in_array($handling, $validHandlingTypes)) {
                throw new JamboJetValidationException(
                    "Invalid special handling type at baggage index {$bagIndex}: {$handling}",
                    400
                );
            }
        }
    }

    /**
     * Validate insurance request
     * 
     * @param array $data Insurance data
     * @throws JamboJetValidationException
     */
    private function validateInsuranceRequest(array $data): void
    {
        $this->validateRequired($data, ['productKey', 'coverageAmount']);

        $this->validateProductKey($data['productKey']);

        // Validate coverage amount
        $this->validateFormats($data, ['coverageAmount' => 'positive_number']);

        // Validate coverage limits
        $this->validateNumericRanges($data, ['coverageAmount' => ['min' => 100, 'max' => 1000000]]);

        // Validate beneficiaries if provided
        if (isset($data['beneficiaries'])) {
            $this->validateInsuranceBeneficiaries($data['beneficiaries']);
        }

        // Validate coverage type if provided
        if (isset($data['coverageType'])) {
            $validTypes = ['Trip', 'Medical', 'Cancellation', 'Baggage', 'Comprehensive'];
            if (!in_array($data['coverageType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid insurance coverage type. Expected one of: ' . implode(', ', $validTypes),
                    400
                );
            }
        }

        // Validate policy period if provided
        if (isset($data['policyPeriod'])) {
            $this->validateInsurancePolicyPeriod($data['policyPeriod']);
        }

        // Validate pre-existing conditions if provided
        if (isset($data['preExistingConditions'])) {
            if (!is_bool($data['preExistingConditions'])) {
                throw new JamboJetValidationException(
                    'preExistingConditions must be a boolean value',
                    400
                );
            }
        }
    }

    /**
     * Validate insurance beneficiaries
     * 
     * @param array $beneficiaries Beneficiaries list
     * @throws JamboJetValidationException
     */
    private function validateInsuranceBeneficiaries(array $beneficiaries): void
    {
        if (empty($beneficiaries)) {
            throw new JamboJetValidationException(
                'At least one beneficiary is required for insurance',
                400
            );
        }

        foreach ($beneficiaries as $index => $beneficiary) {
            $this->validateRequired($beneficiary, ['name', 'relationship', 'percentage']);

            $this->validateStringLengths($beneficiary, [
                'name' => ['min' => 2, 'max' => 100],
                'relationship' => ['max' => 50]
            ]);

            // Validate percentage
            $this->validateNumericRanges($beneficiary, ['percentage' => ['min' => 0, 'max' => 100]]);

            if (isset($beneficiary['contactInfo'])) {
                $this->validateBeneficiaryContact($beneficiary['contactInfo'], $index);
            }
        }

        // Validate total percentage adds up to 100
        $totalPercentage = array_sum(array_column($beneficiaries, 'percentage'));
        if ($totalPercentage !== 100) {
            throw new JamboJetValidationException(
                'Beneficiary percentages must add up to 100%',
                400
            );
        }
    }

    /**
     * Validate SSR request
     * 
     * @param array $data SSR data
     * @throws JamboJetValidationException
     */
    private function validateSsrRequest(array $data): void
    {
        $this->validateRequired($data, ['ssrs']);

        if (!is_array($data['ssrs']) || empty($data['ssrs'])) {
            throw new JamboJetValidationException(
                'SSRs must be a non-empty array',
                400
            );
        }

        foreach ($data['ssrs'] as $index => $ssr) {
            $this->validateSSRItem($ssr, $index);
        }
    }

    /**
     * Validate individual SSR item
     * 
     * @param array $ssr SSR item
     * @param int $index SSR index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateSSRItem(array $ssr, int $index): void
    {
        $this->validateRequired($ssr, ['ssrCode']);

        // Validate SSR code format (4-letter codes)
        if (!preg_match('/^[A-Z]{4}$/', $ssr['ssrCode'])) {
            throw new JamboJetValidationException(
                "Invalid SSR code format at index {$index}. Expected 4-letter code",
                400
            );
        }

        // Validate common SSR codes
        $validSSRCodes = [
            'VGML',
            'KSML',
            'JNML',
            'AVML',
            'VLML',
            'HNML',
            'FPML',
            'DBML',
            'LCML',
            'WCHR',
            'WCHS',
            'WCHC',
            'BLND',
            'DEAF',
            'DPNA',
            'UMNR',
            'PETC',
            'AVIH'
        ];

        if (!in_array($ssr['ssrCode'], $validSSRCodes)) {
            // Allow other codes but log warning
            error_log("Unknown SSR code used: {$ssr['ssrCode']}");
        }

        // Validate passenger key if provided
        if (isset($ssr['passengerKey'])) {
            if (empty(trim($ssr['passengerKey']))) {
                throw new JamboJetValidationException(
                    "Passenger key cannot be empty at SSR index {$index}",
                    400
                );
            }
        }

        // Validate segment keys if provided
        if (isset($ssr['segmentKeys']) && is_array($ssr['segmentKeys'])) {
            foreach ($ssr['segmentKeys'] as $segmentKey) {
                if (empty(trim($segmentKey))) {
                    throw new JamboJetValidationException(
                        "Segment key cannot be empty at SSR index {$index}",
                        400
                    );
                }
            }
        }

        // Validate free text if provided
        if (isset($ssr['freeText'])) {
            $this->validateStringLengths(['text' => $ssr['freeText']], ['text' => ['max' => 200]]);
        }

        // Validate quantity if provided
        if (isset($ssr['quantity'])) {
            $this->validateNumericRanges($ssr, ['quantity' => ['min' => 1, 'max' => 10]]);
        }
    }

    /**
     * Validate service charge request
     * 
     * @param array $data Service charge data
     * @throws JamboJetValidationException
     */
    private function validateServiceChargeRequest(array $data): void
    {
        $this->validateRequired($data, ['serviceCharges']);

        if (!is_array($data['serviceCharges']) || empty($data['serviceCharges'])) {
            throw new JamboJetValidationException(
                'Service charges must be a non-empty array',
                400
            );
        }

        foreach ($data['serviceCharges'] as $index => $charge) {
            $this->validateServiceChargeItem($charge, $index);
        }
    }

    /**
     * Validate individual service charge item
     * 
     * @param array $charge Service charge item
     * @param int $index Charge index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateServiceChargeItem(array $charge, int $index): void
    {
        $this->validateRequired($charge, ['chargeCode', 'amount']);

        // Validate charge code
        $this->validateStringLengths($charge, ['chargeCode' => ['max' => 10]]);

        // Validate amount
        $this->validateFormats($charge, ['amount' => 'positive_number']);

        // Validate charge type if provided
        if (isset($charge['chargeType'])) {
            $validTypes = ['Fee', 'Tax', 'Surcharge', 'Discount', 'Credit', 'Penalty'];
            if (!in_array($charge['chargeType'], $validTypes)) {
                throw new JamboJetValidationException(
                    "Invalid charge type at index {$index}. Expected one of: " . implode(', ', $validTypes),
                    400
                );
            }
        }

        // Validate applicability if provided
        if (isset($charge['applicability'])) {
            $validApplicabilities = ['PerPerson', 'PerBooking', 'PerSegment', 'PerJourney'];
            if (!in_array($charge['applicability'], $validApplicabilities)) {
                throw new JamboJetValidationException(
                    "Invalid charge applicability at index {$index}. Expected one of: " . implode(', ', $validApplicabilities),
                    400
                );
            }
        }

        // Validate currency if provided
        if (isset($charge['currencyCode'])) {
            $this->validateFormats($charge, ['currencyCode' => 'currency_code']);
        }
    }

    // =================================================================
    // ADDITIONAL VALIDATION METHODS FOR AVAILABILITY REQUESTS
    // =================================================================

    /**
     * Validate activity availability request
     * 
     * @param array $data Activity availability criteria
     * @throws JamboJetValidationException
     */
    private function validateActivityAvailabilityRequest(array $data): void
    {
        // Validate location if provided
        if (isset($data['location'])) {
            $this->validateLocation($data['location']);
        }

        // Validate date range if provided
        if (isset($data['dateRange'])) {
            $this->validateDateRange($data['dateRange']);
        }

        // Validate activity categories if provided
        if (isset($data['categories'])) {
            $this->validateActivityCategories($data['categories']);
        }

        // Validate pagination parameters
        if (isset($data['startIndex'])) {
            $this->validateNumericRanges($data, ['startIndex' => ['min' => 0]]);
        }

        if (isset($data['itemCount'])) {
            $this->validateNumericRanges($data, ['itemCount' => ['min' => 1, 'max' => 100]]);
        }
    }

    /**
     * Validate activity quote request
     * 
     * @param array $data Activity quote data
     * @throws JamboJetValidationException
     */
    private function validateActivityQuoteRequest(array $data): void
    {
        $this->validateRequired($data, ['productKey', 'quantity']);

        $this->validateProductKey($data['productKey']);

        $this->validateNumericRanges($data, ['quantity' => ['min' => 1, 'max' => 10]]);

        // Validate participant ages if provided
        if (isset($data['participantAges'])) {
            foreach ($data['participantAges'] as $index => $age) {
                $this->validateNumericRanges(['age' => $age], ['age' => ['min' => 0, 'max' => 120]]);
            }
        }
    }

    // =================================================================
    // HELPER VALIDATION METHODS
    // =================================================================

    /**
     * Validate product key format
     * 
     * @param string $productKey Product key
     * @throws JamboJetValidationException
     */
    private function validateProductKey(string $productKey): void
    {
        if (empty(trim($productKey))) {
            throw new JamboJetValidationException(
                'Product key cannot be empty',
                400
            );
        }

        // Product keys are typically alphanumeric with minimum length
        if (strlen($productKey) < 5) {
            throw new JamboJetValidationException(
                'Invalid product key format',
                400
            );
        }
    }

    /**
     * Validate passenger assignments
     * 
     * @param array $assignments Passenger assignments
     * @throws JamboJetValidationException
     */
    private function validatePassengerAssignments(array $assignments): void
    {
        foreach ($assignments as $index => $assignment) {
            if (isset($assignment['passengerKey']) && empty(trim($assignment['passengerKey']))) {
                throw new JamboJetValidationException(
                    "Passenger key cannot be empty at assignment index {$index}",
                    400
                );
            }

            if (isset($assignment['quantity'])) {
                $this->validateNumericRanges($assignment, ['quantity' => ['min' => 1, 'max' => 10]]);
            }
        }
    }

    /**
     * Validate auto-assign preferences
     * 
     * @param array $preferences Auto-assign preferences
     * @throws JamboJetValidationException
     */
    private function validateAutoAssignPreferences(array $preferences): void
    {
        if (isset($preferences['seatType'])) {
            $validTypes = ['Any', 'Window', 'Aisle', 'Middle', 'ExtraLegroom'];
            if (!in_array($preferences['seatType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid auto-assign seat type preference',
                    400
                );
            }
        }

        if (isset($preferences['keepTogether']) && !is_bool($preferences['keepTogether'])) {
            throw new JamboJetValidationException(
                'keepTogether must be a boolean value',
                400
            );
        }
    }

    /**
     * Validate insurance policy period
     * 
     * @param array $period Policy period
     * @throws JamboJetValidationException
     */
    private function validateInsurancePolicyPeriod(array $period): void
    {
        $this->validateRequired($period, ['startDate', 'endDate']);

        $this->validateFormats($period, [
            'startDate' => 'date',
            'endDate' => 'date'
        ]);

        $startDate = new \DateTime($period['startDate']);
        $endDate = new \DateTime($period['endDate']);

        if ($endDate <= $startDate) {
            throw new JamboJetValidationException(
                'Policy end date must be after start date',
                400
            );
        }

        // Policy period cannot be more than 1 year
        $interval = $startDate->diff($endDate);
        if ($interval->days > 365) {
            throw new JamboJetValidationException(
                'Insurance policy period cannot exceed 365 days',
                400
            );
        }
    }

    /**
     * Validate beneficiary contact information
     * 
     * @param array $contact Contact information
     * @param int $beneficiaryIndex Beneficiary index
     * @throws JamboJetValidationException
     */
    private function validateBeneficiaryContact(array $contact, int $beneficiaryIndex): void
    {
        if (isset($contact['email'])) {
            $this->validateFormats($contact, ['email' => 'email']);
        }

        if (isset($contact['phone'])) {
            $this->validateFormats($contact, ['phone' => 'phone']);
        }

        if (isset($contact['address'])) {
            $this->validateRequired($contact['address'], ['lineOne', 'city', 'countryCode']);
            $this->validateFormats($contact['address'], ['countryCode' => 'country_code']);
        }
    }

    /**
     * Validate location for activities
     * 
     * @param array $location Location data
     * @throws JamboJetValidationException
     */
    private function validateLocation(array $location): void
    {
        if (isset($location['airportCode'])) {
            $this->validateFormats($location, ['airportCode' => 'airport_code']);
        }

        if (isset($location['cityCode'])) {
            $this->validateStringLengths($location, ['cityCode' => ['max' => 5]]);
        }

        if (isset($location['countryCode'])) {
            $this->validateFormats($location, ['countryCode' => 'country_code']);
        }

        if (isset($location['coordinates'])) {
            $this->validateCoordinates($location['coordinates']);
        }
    }

    /**
     * Validate coordinates
     * 
     * @param array $coordinates Coordinates
     * @throws JamboJetValidationException
     */
    private function validateCoordinates(array $coordinates): void
    {
        $this->validateRequired($coordinates, ['latitude', 'longitude']);

        // Validate latitude range
        $this->validateNumericRanges($coordinates, ['latitude' => ['min' => -90, 'max' => 90]]);

        // Validate longitude range
        $this->validateNumericRanges($coordinates, ['longitude' => ['min' => -180, 'max' => 180]]);
    }

    /**
     * Validate date range
     * 
     * @param array $dateRange Date range
     * @throws JamboJetValidationException
     */
    private function validateDateRange(array $dateRange): void
    {
        $this->validateRequired($dateRange, ['startDate', 'endDate']);

        $this->validateFormats($dateRange, [
            'startDate' => 'date',
            'endDate' => 'date'
        ]);

        $startDate = new \DateTime($dateRange['startDate']);
        $endDate = new \DateTime($dateRange['endDate']);

        if ($endDate < $startDate) {
            throw new JamboJetValidationException(
                'End date must be after start date',
                400
            );
        }
    }

    /**
     * Validate activity categories
     * 
     * @param array $categories Activity categories
     * @throws JamboJetValidationException
     */
    private function validateActivityCategories(array $categories): void
    {
        $validCategories = [
            'Adventure',
            'Cultural',
            'Food',
            'Entertainment',
            'Sports',
            'Sightseeing',
            'Nature',
            'Shopping',
            'Relaxation',
            'Education'
        ];

        foreach ($categories as $category) {
            if (!in_array($category, $validCategories)) {
                throw new JamboJetValidationException(
                    "Invalid activity category: {$category}. Expected one of: " . implode(', ', $validCategories),
                    400
                );
            }
        }
    }

    // Placeholder validation methods for remaining add-on types

    /**
     * Validate lounge access request
     */
    private function validateLoungeAccessRequest(array $data): void
    {
        $this->validateRequired($data, ['loungeCode', 'passengerKey']);

        $this->validateStringLengths($data, ['loungeCode' => ['max' => 10]]);

        if (isset($data['accessTime'])) {
            $this->validateFormats($data, ['accessTime' => 'datetime']);
        }
    }

    /**
     * Validate merchandise request
     */
    private function validateMerchandiseRequest(array $data): void
    {
        $this->validateRequired($data, ['productKey', 'quantity']);

        $this->validateProductKey($data['productKey']);
        $this->validateNumericRanges($data, ['quantity' => ['min' => 1, 'max' => 10]]);
    }

    /**
     * Validate pet transport request
     */
    private function validatePetTransportRequest(array $data): void
    {
        $this->validateRequired($data, ['petType', 'weight', 'passengerKey']);

        $validPetTypes = ['Dog', 'Cat', 'Bird', 'Rabbit', 'Other'];
        if (!in_array($data['petType'], $validPetTypes)) {
            throw new JamboJetValidationException(
                'Invalid pet type. Expected one of: ' . implode(', ', $validPetTypes),
                400
            );
        }

        $this->validateFormats($data, ['weight' => 'positive_number']);
    }

    /**
     * Validate hotel availability request
     */
    private function validateHotelAvailabilityRequest(array $data): void
    {
        if (isset($data['location'])) {
            $this->validateLocation($data['location']);
        }

        if (isset($data['dateRange'])) {
            $this->validateDateRange($data['dateRange']);
        }
    }

    /**
     * Validate car availability request
     */
    private function validateCarAvailabilityRequest(array $data): void
    {
        if (isset($data['pickupLocation'])) {
            $this->validateLocation($data['pickupLocation']);
        }

        if (isset($data['dateRange'])) {
            $this->validateDateRange($data['dateRange']);
        }
    }

    /**
     * Validate insurance availability request
     */
    private function validateInsuranceAvailabilityRequest(array $data): void
    {
        if (isset($data['coverageType'])) {
            $validTypes = ['Trip', 'Medical', 'Cancellation', 'Baggage', 'Comprehensive'];
            if (!in_array($data['coverageType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid insurance coverage type',
                    400
                );
            }
        }
    }

    /**
     * Validate policy period
     * 
     * @param array $policyPeriod Policy period data
     * @throws JamboJetValidationException
     */
    private function validatePolicyPeriod(array $policyPeriod): void
    {
        if (isset($policyPeriod['startDate'])) {
            $this->validateFormats($policyPeriod, ['startDate' => 'date']);
        }

        if (isset($policyPeriod['endDate'])) {
            $this->validateFormats($policyPeriod, ['endDate' => 'date']);

            if (isset($policyPeriod['startDate'])) {
                $start = new \DateTime($policyPeriod['startDate']);
                $end = new \DateTime($policyPeriod['endDate']);

                if ($end <= $start) {
                    throw new JamboJetValidationException(
                        'Policy end date must be after start date'
                    );
                }
            }
        }
    }

    /**
     * Validate beneficiary information
     * 
     * @param array $beneficiary Beneficiary data
     * @throws JamboJetValidationException
     */
    private function validateBeneficiary(array $beneficiary): void
    {
        if (isset($beneficiary['name'])) {
            if (empty(trim($beneficiary['name']))) {
                throw new JamboJetValidationException('Beneficiary name cannot be empty');
            }
        }

        if (isset($beneficiary['relationship'])) {
            $validRelationships = ['Spouse', 'Child', 'Parent', 'Sibling', 'Other'];
            if (!in_array($beneficiary['relationship'], $validRelationships)) {
                throw new JamboJetValidationException(
                    'Invalid beneficiary relationship. Expected one of: ' . implode(', ', $validRelationships)
                );
            }
        }

        if (isset($beneficiary['percentage'])) {
            $this->validateNumericRanges($beneficiary, ['percentage' => ['min' => 1, 'max' => 100]]);
        }
    }

    /**
     * Validate price range
     * 
     * @param array $priceRange Price range data
     * @throws JamboJetValidationException
     */
    private function validatePriceRange(array $priceRange): void
    {
        if (isset($priceRange['minPrice'])) {
            if (!is_numeric($priceRange['minPrice']) || $priceRange['minPrice'] < 0) {
                throw new JamboJetValidationException('Minimum price must be a non-negative number');
            }
        }

        if (isset($priceRange['maxPrice'])) {
            if (!is_numeric($priceRange['maxPrice']) || $priceRange['maxPrice'] < 0) {
                throw new JamboJetValidationException('Maximum price must be a non-negative number');
            }

            if (isset($priceRange['minPrice']) && $priceRange['maxPrice'] < $priceRange['minPrice']) {
                throw new JamboJetValidationException('Maximum price must be greater than minimum price');
            }
        }

        if (isset($priceRange['currency'])) {
            $this->validateFormats($priceRange, ['currency' => 'currency_code']);
        }
    }

    /**
     * Validate vendor request
     */
    private function validateVendorRequest(array $data): void
    {
        if (isset($data['vendorType'])) {
            $validTypes = ['Hotel', 'Car', 'Activity', 'Insurance', 'Transfer'];
            if (!in_array($data['vendorType'], $validTypes)) {
                throw new JamboJetValidationException(
                    'Invalid vendor type',
                    400
                );
            }
        }
    }
}
