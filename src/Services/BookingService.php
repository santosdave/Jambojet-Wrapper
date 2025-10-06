<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\BookingInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Booking Service for JamboJet NSK API
 * 
 * Handles all booking management operations including creation, modification,
 * cancellation, and state management for the New Skies booking system
 * 
 * NSK uses a "stateful" booking approach:
 * 1. Build booking in session state
 * 2. Commit booking to finalize
 * 3. Retrieve/manage completed bookings
 * 
 * Supported endpoints:
 * - GET/POST/PUT /api/nsk/v3/booking - Current booking state management
 * - GET /api/nsk/v2/booking/status - Booking commit status
 * - GET /api/nsk/v1/bookings/{recordLocator} - Retrieve booking by record locator
 * - GET /api/nsk/v1/bookings - Search bookings with criteria
 * - POST /api/nsk/v2/bookings/quote - Get booking quote (stateless)
 * - GET /api/nsk/v1/bookings/{recordLocator}/history - Booking history
 * - POST /api/nsk/v2/bookings/{recordLocator}/notification - Send notifications
 * 
 * @package SantosDave\JamboJet\Services
 */
class BookingService implements BookingInterface
{
    use HandlesApiRequests, ValidatesRequests;

    /**
     * Create a new booking (Latest Version - Recommended)
     * 
     * POST /api/nsk/v3/booking
     * Commits stateful changes and processes the booking
     * 
     * @param array $bookingData Complete booking commit request
     * @return array Booking creation response
     * @throws JamboJetApiException
     */
    public function create(array $bookingData): array
    {
        try {
            return $this->post('api/nsk/v3/booking', $bookingData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Booking creation failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update booking (Latest Version)
     * 
     * PUT /api/nsk/v3/booking
     * Commits stateful changes made to current booking
     * 
     * @param string $recordLocator Record locator (for interface compliance)
     * @param array $updateData Booking update/commit request
     * @return array Booking update response
     * @throws JamboJetApiException
     */
    public function update(string $recordLocator, array $updateData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateBookingUpdateRequest($updateData);

        try {
            return $this->put('api/nsk/v3/booking', $updateData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Booking update failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get current booking in state
     * 
     * GET /api/nsk/v1/booking
     * Retrieves the current booking being worked on in session state
     * 
     * @return array Current booking data
     * @throws JamboJetApiException
     */
    public function getCurrentBooking(): array
    {
        try {
            return $this->get('api/nsk/v1/booking');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get current booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking by record locator (Stateless)
     * 
     * GET /api/nsk/v1/bookings/{recordLocator}
     * Retrieves a completed booking by its record locator
     * 
     * @param string $recordLocator Booking record locator
     * @return array Booking details
     * @throws JamboJetApiException
     */
    public function getByRecordLocator(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Commit booking changes
     * 
     * @param string $recordLocator Record locator
     * @return array Commit response
     */
    public function commit(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->post('api/nsk/v3/booking', [
                'action' => 'commit',
                'recordLocator' => $recordLocator
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to commit booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    /**
     * Get booking commit status
     * 
     * GET /api/nsk/v2/booking/status
     * Gets the status of booking commit and returns booking data
     * 
     * @return array Booking status and data
     * @throws JamboJetApiException
     */
    public function getCommitStatus(): array
    {
        try {
            return $this->get('api/nsk/v2/booking/status');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking history
     * 
     * GET /api/nsk/v1/bookings/{recordLocator}/history
     * Retrieve the modification history for a booking
     * 
     * @param string $recordLocator Booking record locator
     * @return array Booking history
     * @throws JamboJetApiException
     */
    public function getHistory(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}/history");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get current booking history
     * 
     * GET /api/nsk/v1/booking/history
     * Retrieves history for the current booking in state
     * 
     * @param array $options History filter options
     * @return array Current booking history
     * @throws JamboJetApiException
     */
    public function getCurrentBookingHistory(array $options = []): array
    {
        try {
            $url = 'api/nsk/v1/booking/history';

            if (!empty($options)) {
                $url .= '?' . http_build_query($options);
            }

            return $this->get($url);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get current booking history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Search bookings with criteria
     * 
     * GET /api/nsk/v1/bookings
     * Search for bookings using various criteria
     * 
     * @param array $searchCriteria Search criteria
     * @return array Search results
     * @throws JamboJetApiException
     */
    public function searchBookings(array $searchCriteria): array
    {
        $this->validateBookingSearchRequest($searchCriteria);

        try {
            return $this->get('api/nsk/v1/bookings', $searchCriteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Booking search failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking quote (Stateless)
     * 
     * POST /api/nsk/v2/bookings/quote
     * Get a quote for a booking without creating it
     * 
     * @param array $quoteData Quote request data
     * @return array Quote details
     * @throws JamboJetApiException
     */
    public function getQuote(array $quoteData): array
    {
        $this->validateBookingQuoteRequest($quoteData);

        try {
            return $this->post('api/nsk/v2/bookings/quote', $quoteData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Quote generation failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Send booking notification
     * 
     * POST /api/nsk/v2/bookings/{recordLocator}/notification
     * Send notification (email, SMS) for a booking
     * 
     * @param string $recordLocator Booking record locator
     * @param array $notificationData Notification details
     * @return array Notification response
     * @throws JamboJetApiException
     */
    public function sendNotification(string $recordLocator, array $notificationData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateNotificationRequest($notificationData);

        try {
            return $this->post("api/nsk/v2/bookings/{$recordLocator}/notification", $notificationData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to send notification: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Send email notification
     * 
     * POST /api/nsk/v1/bookings/{recordLocator}/email
     * Sends itinerary notification via email regardless of distribution option
     * 
     * @param string $recordLocator Booking record locator
     * @return array Email response
     * @throws JamboJetApiException
     */
    public function sendEmail(string $recordLocator): array
    {
        if (empty($recordLocator)) {
            throw new JamboJetValidationException('Record locator is required');
        }

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/email");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to send email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add comments to booking
     * 
     * POST /api/nsk/v3/bookings/{recordLocator}/comments
     * Adds comments to booking (requires agent permissions)
     * 
     * @param string $recordLocator Booking record locator
     * @param array $comments Comments to add
     * @return array Response
     * @throws JamboJetApiException
     */
    public function addComments(string $recordLocator, array $comments): array
    {
        if (empty($recordLocator)) {
            throw new JamboJetValidationException('Record locator is required');
        }

        if (empty($comments)) {
            throw new JamboJetValidationException('Comments are required');
        }

        try {
            return $this->post("api/nsk/v3/bookings/{$recordLocator}/comments", $comments);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add comments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Cancel booking
     * 
     * @param string $recordLocator Record locator
     * @param array $cancellationData Cancellation data
     * @return array Cancellation result
     */
    public function cancel(string $recordLocator, array $cancellationData = []): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateCancellationRequest($cancellationData);

        try {
            // NSK typically uses state management for cancellations
            return $this->post('api/nsk/v3/booking', array_merge($cancellationData, [
                'action' => 'cancel',
                'recordLocator' => $recordLocator
            ]));
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Booking cancellation failed: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add passengers to booking
     * 
     * @param string $recordLocator Record locator
     * @param array $passengers Passenger data
     * @return array Response
     */
    public function addPassengers(string $recordLocator, array $passengers): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validatePassengersData($passengers);

        try {
            return $this->post('api/nsk/v3/booking', [
                'action' => 'addPassengers',
                'recordLocator' => $recordLocator,
                'passengers' => $passengers
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add passengers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update passenger information
     * 
     * @param string $recordLocator Record locator
     * @param string $passengerKey Passenger key
     * @param array $passengerData Updated passenger data
     * @return array Response
     */
    public function updatePassenger(string $recordLocator, string $passengerKey, array $passengerData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validatePassengerKey($passengerKey);
        $this->validatePassengerData($passengerData);

        try {
            return $this->put('api/nsk/v3/booking', [
                'action' => 'updatePassenger',
                'recordLocator' => $recordLocator,
                'passengerKey' => $passengerKey,
                'passengerData' => $passengerData
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update passenger: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove passenger from booking
     * 
     * @param string $recordLocator Record locator
     * @param string $passengerKey Passenger key
     * @return array Response
     */
    public function removePassenger(string $recordLocator, string $passengerKey): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->post('api/nsk/v3/booking', [
                'action' => 'removePassenger',
                'recordLocator' => $recordLocator,
                'passengerKey' => $passengerKey
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove passenger: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Quick booking search by passenger name and record locator
     * 
     * @param string $recordLocator Record locator
     * @param string $lastName Passenger last name
     * @param array $options Additional search options
     * @return array Booking data
     * @throws JamboJetApiException
     */
    public function findByNameAndRecord(string $recordLocator, string $lastName, array $options = []): array
    {
        $criteria = array_merge([
            'RecordLocator' => $recordLocator,
            'LastName' => $lastName
        ], $options);

        return $this->searchBookings($criteria);
    }

    /**
     * Search bookings by passenger email
     * 
     * @param string $email Passenger email address
     * @param array $options Additional search options
     * @return array Booking search results
     * @throws JamboJetApiException
     */
    public function findByEmail(string $email, array $options = []): array
    {
        $this->validateFormats(['email' => $email], ['email' => 'email']);

        $criteria = array_merge([
            'EmailAddress' => $email
        ], $options);

        return $this->searchBookings($criteria);
    }

    /**
     * Search bookings by phone number
     * 
     * @param string $phoneNumber Phone number
     * @param array $options Additional search options
     * @return array Booking search results
     * @throws JamboJetApiException
     */
    public function findByPhone(string $phoneNumber, array $options = []): array
    {
        $criteria = array_merge([
            'PhoneNumber' => $phoneNumber
        ], $options);

        return $this->searchBookings($criteria);
    }

    // =================================================================
    // VALIDATION METHODS - UPDATED AND COMPREHENSIVE
    // =================================================================

    /**
     * Validate booking creation request
     * Based on NSK booking commit request structure
     * 
     * @param array $data Booking creation data
     * @throws JamboJetValidationException
     */
    private function validateBookingCreateRequest(array $data): void
    {
        // For booking creation, we need meaningful data
        if (empty($data['passengers']) && empty($data['journeys']) && empty($data['segments'])) {
            throw new JamboJetValidationException(
                'Booking request must include passengers, journeys, or segments data',
                400
            );
        }

        // Validate passengers if provided
        if (isset($data['passengers'])) {
            $this->validatePassengersData($data['passengers']);
        }

        // Validate journeys if provided
        if (isset($data['journeys'])) {
            $this->validateJourneysData($data['journeys']);
        }

        // Validate segments if provided
        if (isset($data['segments'])) {
            $this->validateSegmentsData($data['segments']);
        }

        // Validate contact details if provided
        if (isset($data['contactDetails'])) {
            $this->validateContactDetails($data['contactDetails']);
        }

        // Validate currency code if provided
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate booking flags
        if (isset($data['validateOnly']) && !is_bool($data['validateOnly'])) {
            throw new JamboJetValidationException(
                'validateOnly must be a boolean value',
                400
            );
        }

        if (isset($data['bypassWarnings']) && !is_bool($data['bypassWarnings'])) {
            throw new JamboJetValidationException(
                'bypassWarnings must be a boolean value',
                400
            );
        }

        // Validate special requests if provided
        if (isset($data['specialRequests'])) {
            $this->validateSpecialRequests($data['specialRequests']);
        }

        // Validate payment information if provided
        if (isset($data['paymentInfo'])) {
            $this->validateBookingPaymentInfo($data['paymentInfo']);
        }
    }

    /**
     * Validate booking update request
     * 
     * @param array $data Update data
     * @throws JamboJetValidationException
     */
    private function validateBookingUpdateRequest(array $data): void
    {
        // Similar to create but may have different required fields
        $this->validateBookingCreateRequest($data);

        // Additional validations specific to updates
        if (isset($data['version'])) {
            if (!is_int($data['version']) || $data['version'] < 0) {
                throw new JamboJetValidationException(
                    'Booking version must be a non-negative integer',
                    400
                );
            }
        }
    }

    /**
     * Validate booking search request
     * 
     * @param array $criteria Search criteria
     * @throws JamboJetValidationException
     */
    private function validateBookingSearchRequest(array $criteria): void
    {
        // At least one search criterion must be provided
        $validCriteria = [
            'recordLocator',
            'customerKey',
            'emailAddress',
            'lastName',
            'departureDate',
            'arrivalDate',
            'phoneNumber',
            'ticketNumber'
        ];

        $hasValidCriterion = false;
        foreach ($validCriteria as $criterion) {
            if (isset($criteria[$criterion]) && !empty(trim($criteria[$criterion]))) {
                $hasValidCriterion = true;
                break;
            }
        }

        if (!$hasValidCriterion) {
            throw new JamboJetValidationException(
                'At least one search criterion must be provided: ' . implode(', ', $validCriteria),
                400
            );
        }

        // Validate specific criteria formats
        if (isset($criteria['recordLocator'])) {
            $this->validateRecordLocator($criteria['recordLocator']);
        }

        if (isset($criteria['emailAddress'])) {
            $this->validateFormats($criteria, ['emailAddress' => 'email']);
        }

        if (isset($criteria['departureDate'])) {
            $this->validateFormats($criteria, ['departureDate' => 'date']);
        }

        if (isset($criteria['arrivalDate'])) {
            $this->validateFormats($criteria, ['arrivalDate' => 'date']);
        }

        if (isset($criteria['phoneNumber'])) {
            $this->validateFormats($criteria, ['phoneNumber' => 'phone']);
        }

        // Validate pagination parameters
        if (isset($criteria['startIndex'])) {
            $this->validateNumericRanges($criteria, ['startIndex' => ['min' => 0]]);
        }

        if (isset($criteria['itemCount'])) {
            $this->validateNumericRanges($criteria, ['itemCount' => ['min' => 1, 'max' => 100]]);
        }
    }

    /**
     * Validate booking quote request
     * 
     * @param array $data Quote request data
     * @throws JamboJetValidationException
     */
    private function validateBookingQuoteRequest(array $data): void
    {
        // Quote requests typically need journey/fare information
        $this->validateRequired($data, ['journeys']);

        $this->validateJourneysData($data['journeys']);

        // Validate passengers if provided
        if (isset($data['passengers'])) {
            $this->validatePassengersData($data['passengers']);
        }

        // Validate currency for pricing
        if (isset($data['currencyCode'])) {
            $this->validateFormats($data, ['currencyCode' => 'currency_code']);
        }

        // Validate pricing options
        if (isset($data['includeTaxes']) && !is_bool($data['includeTaxes'])) {
            throw new JamboJetValidationException(
                'includeTaxes must be a boolean value',
                400
            );
        }

        if (isset($data['includeFees']) && !is_bool($data['includeFees'])) {
            throw new JamboJetValidationException(
                'includeFees must be a boolean value',
                400
            );
        }
    }

    /**
     * Validate notification request
     * 
     * @param array $data Notification data
     * @throws JamboJetValidationException
     */
    private function validateNotificationRequest(array $data): void
    {
        $this->validateRequired($data, ['notificationType', 'recipient']);

        // Validate notification type
        $validTypes = ['Email', 'SMS', 'Push', 'Confirmation', 'Reminder', 'Update'];
        if (!in_array($data['notificationType'], $validTypes)) {
            throw new JamboJetValidationException(
                'Invalid notification type. Expected one of: ' . implode(', ', $validTypes),
                400
            );
        }

        // Validate recipient based on notification type
        if ($data['notificationType'] === 'Email') {
            $this->validateFormats($data, ['recipient' => 'email']);
        } elseif ($data['notificationType'] === 'SMS') {
            $this->validateFormats($data, ['recipient' => 'phone']);
        }

        // Validate message content if provided
        if (isset($data['message'])) {
            $this->validateStringLengths($data, ['message' => ['max' => 1000]]);
        }

        // Validate template if provided
        if (isset($data['templateCode'])) {
            $this->validateStringLengths($data, ['templateCode' => ['max' => 50]]);
        }
    }

    /**
     * Validate cancellation request
     * 
     * @param array $data Cancellation data
     * @throws JamboJetValidationException
     */
    private function validateCancellationRequest(array $data): void
    {
        // Validate reason code if provided
        if (isset($data['reasonCode'])) {
            $validReasons = ['CustomerRequest', 'NoShow', 'Schedule', 'Weather', 'Medical', 'Other'];
            if (!in_array($data['reasonCode'], $validReasons)) {
                throw new JamboJetValidationException(
                    'Invalid cancellation reason. Expected one of: ' . implode(', ', $validReasons),
                    400
                );
            }
        }

        // Validate refund request if provided
        if (isset($data['requestRefund']) && !is_bool($data['requestRefund'])) {
            throw new JamboJetValidationException(
                'requestRefund must be a boolean value',
                400
            );
        }

        // Validate waive fees if provided
        if (isset($data['waiveFees']) && !is_bool($data['waiveFees'])) {
            throw new JamboJetValidationException(
                'waiveFees must be a boolean value',
                400
            );
        }

        // Validate comments if provided
        if (isset($data['comments'])) {
            $this->validateStringLengths($data, ['comments' => ['max' => 500]]);
        }
    }

    // =================================================================
    // HELPER VALIDATION METHODS FOR COMPLEX STRUCTURES
    // =================================================================

    /**
     * Validate passengers data array
     * 
     * @param array $passengers Passengers data
     * @throws JamboJetValidationException
     */
    private function validatePassengersData(array $passengers): void
    {
        if (empty($passengers)) {
            throw new JamboJetValidationException(
                'Passengers data cannot be empty',
                400
            );
        }

        foreach ($passengers as $index => $passenger) {
            $this->validatePassengerData($passenger, $index);
        }
    }

    /**
     * Validate individual passenger data
     * 
     * @param array $passenger Passenger data
     * @param int|null $index Passenger index for error reporting
     * @throws JamboJetValidationException
     */
    private function validatePassengerData(array $passenger, ?int $index = null): void
    {
        $indexStr = $index !== null ? " at index {$index}" : '';

        // Validate name information
        if (isset($passenger['name'])) {
            $this->validatePassengerName($passenger['name'], $indexStr);
        }

        // Validate passenger type if provided
        if (isset($passenger['type'])) {
            $this->validateFormats(['type' => $passenger['type']], ['type' => 'passenger_type']);
        }

        // Validate date of birth if provided
        if (isset($passenger['dateOfBirth'])) {
            $this->validateFormats(['dob' => $passenger['dateOfBirth']], ['dob' => 'date']);

            // Validate age constraints
            $dob = new \DateTime($passenger['dateOfBirth']);
            $now = new \DateTime();
            $age = $now->diff($dob)->y;

            if ($age > 120) {
                throw new JamboJetValidationException(
                    "Invalid age for passenger{$indexStr}. Age cannot exceed 120 years",
                    400
                );
            }
        }

        // Validate contact information if provided
        if (isset($passenger['contactInfo'])) {
            $this->validatePassengerContactInfo($passenger['contactInfo'], $indexStr);
        }

        // Validate travel documents if provided
        if (isset($passenger['travelDocuments'])) {
            $this->validateTravelDocuments($passenger['travelDocuments'], $indexStr);
        }

        // Validate special requests if provided
        if (isset($passenger['specialRequests'])) {
            $this->validateSpecialRequests($passenger['specialRequests']);
        }

        // Validate loyalty programs if provided
        if (isset($passenger['loyaltyPrograms'])) {
            $this->validateLoyaltyPrograms($passenger['loyaltyPrograms']);
        }
    }

    /**
     * Validate passenger name structure
     * 
     * @param array $name Name data
     * @param string $context Context for error reporting
     * @throws JamboJetValidationException
     */
    private function validatePassengerName(array $name, string $context): void
    {
        // First and last names are required
        $this->validateRequired($name, ['first', 'last']);

        // Validate name lengths (airline restrictions)
        $nameLengths = [
            'title' => ['max' => 10],
            'first' => ['min' => 1, 'max' => 30],
            'middle' => ['max' => 30],
            'last' => ['min' => 1, 'max' => 30],
            'suffix' => ['max' => 10]
        ];

        foreach ($nameLengths as $field => $limits) {
            if (isset($name[$field])) {
                try {
                    $this->validateStringLengths([$field => $name[$field]], [$field => $limits]);
                } catch (JamboJetValidationException $e) {
                    throw new JamboJetValidationException(
                        "Passenger{$context} name validation failed: {$e->getMessage()}",
                        400
                    );
                }
            }
        }

        // Validate name characters (no special characters except hyphens, apostrophes)
        foreach (['first', 'middle', 'last'] as $nameField) {
            if (isset($name[$nameField])) {
                if (!preg_match("/^[a-zA-Z\s\-']+$/", $name[$nameField])) {
                    throw new JamboJetValidationException(
                        "Passenger{$context} {$nameField} name contains invalid characters",
                        400
                    );
                }
            }
        }
    }

    /**
     * Validate passenger contact information
     * 
     * @param array $contact Contact information
     * @param string $context Context for error reporting
     * @throws JamboJetValidationException
     */
    private function validatePassengerContactInfo(array $contact, string $context): void
    {
        if (isset($contact['email'])) {
            $this->validateFormats($contact, ['email' => 'email']);
        }

        if (isset($contact['phone'])) {
            $this->validateFormats($contact, ['phone' => 'phone']);
        }

        // Validate address if provided
        if (isset($contact['address'])) {
            $this->validateContactAddress($contact['address']);
        }
    }

    /**
     * Validate travel documents
     * 
     * @param array $documents Travel documents
     * @param string $context Context for error reporting
     * @throws JamboJetValidationException
     */
    private function validateTravelDocuments(array $documents, string $context): void
    {
        foreach ($documents as $docIndex => $document) {
            if (isset($document['number']) && empty(trim($document['number']))) {
                throw new JamboJetValidationException(
                    "Passenger{$context} document {$docIndex} number cannot be empty",
                    400
                );
            }

            if (isset($document['type'])) {
                $validTypes = ['Passport', 'NationalID', 'DriverLicense', 'Other'];
                if (!in_array($document['type'], $validTypes)) {
                    throw new JamboJetValidationException(
                        "Invalid document type for passenger{$context} document {$docIndex}",
                        400
                    );
                }
            }

            if (isset($document['expiryDate'])) {
                $this->validateFormats(['expiry' => $document['expiryDate']], ['expiry' => 'date']);

                // Check document is not expired
                $expiryDate = new \DateTime($document['expiryDate']);
                $now = new \DateTime();

                if ($expiryDate < $now) {
                    throw new JamboJetValidationException(
                        "Passenger{$context} document {$docIndex} has expired",
                        400
                    );
                }
            }

            if (isset($document['issuingCountry'])) {
                $this->validateFormats(['country' => $document['issuingCountry']], ['country' => 'country_code']);
            }
        }
    }

    /**
     * Validate journeys data
     * 
     * @param array $journeys Journeys data
     * @throws JamboJetValidationException
     */
    private function validateJourneysData(array $journeys): void
    {
        if (empty($journeys)) {
            throw new JamboJetValidationException(
                'Journeys data cannot be empty',
                400
            );
        }

        foreach ($journeys as $index => $journey) {
            if (isset($journey['fareAvailabilityKey']) && empty(trim($journey['fareAvailabilityKey']))) {
                throw new JamboJetValidationException(
                    "Journey {$index} fareAvailabilityKey cannot be empty",
                    400
                );
            }

            if (isset($journey['segments'])) {
                $this->validateSegmentsData($journey['segments'], $index);
            }

            // Validate journey-level pricing information
            if (isset($journey['fareInfo'])) {
                $this->validateJourneyFareInfo($journey['fareInfo'], $index);
            }
        }
    }

    /**
     * Validate segments data
     * 
     * @param array $segments Segments data
     * @param int|null $journeyIndex Journey index for error reporting
     * @throws JamboJetValidationException
     */
    private function validateSegmentsData(array $segments, ?int $journeyIndex = null): void
    {
        $context = $journeyIndex !== null ? " for journey {$journeyIndex}" : '';

        foreach ($segments as $segIndex => $segment) {
            if (isset($segment['inventoryKey']) && empty(trim($segment['inventoryKey']))) {
                throw new JamboJetValidationException(
                    "Segment {$segIndex}{$context} inventoryKey cannot be empty",
                    400
                );
            }

            // Validate flight information
            if (isset($segment['flight'])) {
                $this->validateFlightInfo($segment['flight'], $segIndex, $context);
            }

            // Validate fare information
            if (isset($segment['fare'])) {
                $this->validateSegmentFareInfo($segment['fare'], $segIndex, $context);
            }
        }
    }

    /**
     * Validate flight information
     * 
     * @param array $flight Flight information
     * @param int $segIndex Segment index
     * @param string $context Context for error reporting
     * @throws JamboJetValidationException
     */
    private function validateFlightInfo(array $flight, int $segIndex, string $context): void
    {
        if (isset($flight['flightNumber'])) {
            if (!preg_match('/^[A-Z]{2,3}\d{1,4}$/', $flight['flightNumber'])) {
                throw new JamboJetValidationException(
                    "Invalid flight number format for segment {$segIndex}{$context}",
                    400
                );
            }
        }

        if (isset($flight['departureStation'])) {
            $this->validateFormats(['dep' => $flight['departureStation']], ['dep' => 'airport_code']);
        }

        if (isset($flight['arrivalStation'])) {
            $this->validateFormats(['arr' => $flight['arrivalStation']], ['arr' => 'airport_code']);
        }

        if (isset($flight['departureTime'])) {
            $this->validateFormats(['depTime' => $flight['departureTime']], ['depTime' => 'datetime']);
        }

        if (isset($flight['arrivalTime'])) {
            $this->validateFormats(['arrTime' => $flight['arrivalTime']], ['arrTime' => 'datetime']);
        }
    }

    /**
     * Validate contact details structure
     * 
     * @param array $contactDetails Contact details
     * @throws JamboJetValidationException
     */
    private function validateContactDetails(array $contactDetails): void
    {
        if (isset($contactDetails['email'])) {
            $this->validateFormats($contactDetails, ['email' => 'email']);
        }

        if (isset($contactDetails['phone'])) {
            $this->validateFormats($contactDetails, ['phone' => 'phone']);
        }

        if (isset($contactDetails['address'])) {
            $this->validateContactAddress($contactDetails['address']);
        }
    }

    /**
     * Validate contact address
     * 
     * @param array $address Address information
     * @throws JamboJetValidationException
     */
    private function validateContactAddress(array $address): void
    {
        if (isset($address['countryCode'])) {
            $this->validateFormats($address, ['countryCode' => 'country_code']);
        }

        // Address validation based on requirements
        $addressLengths = [
            'lineOne' => ['max' => 100],
            'lineTwo' => ['max' => 100],
            'city' => ['max' => 50],
            'postalCode' => ['max' => 20],
            'provinceState' => ['max' => 50]
        ];

        foreach ($addressLengths as $field => $limits) {
            if (isset($address[$field])) {
                $this->validateStringLengths([$field => $address[$field]], [$field => $limits]);
            }
        }
    }

    /**
     * Validate special requests
     * 
     * @param array $specialRequests Special requests
     * @throws JamboJetValidationException
     */
    private function validateSpecialRequests(array $specialRequests): void
    {
        foreach ($specialRequests as $index => $ssr) {
            if (isset($ssr['code']) && empty(trim($ssr['code']))) {
                throw new JamboJetValidationException(
                    "Special request {$index} code cannot be empty",
                    400
                );
            }

            // Validate SSR code format (4-letter codes)
            if (isset($ssr['code']) && !preg_match('/^[A-Z]{4}$/', $ssr['code'])) {
                throw new JamboJetValidationException(
                    "Invalid SSR code format for request {$index}. Expected 4-letter code",
                    400
                );
            }

            // Validate free text if provided
            if (isset($ssr['freeText'])) {
                $this->validateStringLengths(['text' => $ssr['freeText']], ['text' => ['max' => 200]]);
            }
        }
    }

    /**
     * Validate loyalty programs
     * 
     * @param array $loyaltyPrograms Loyalty programs
     * @throws JamboJetValidationException
     */
    private function validateLoyaltyPrograms(array $loyaltyPrograms): void
    {
        foreach ($loyaltyPrograms as $index => $program) {
            $this->validateRequired($program, ['programCode', 'membershipNumber']);

            $this->validateStringLengths($program, [
                'programCode' => ['max' => 10],
                'membershipNumber' => ['max' => 50]
            ]);
        }
    }

    /**
     * Validate journey fare information
     * 
     * @param array $fareInfo Fare information
     * @param int $journeyIndex Journey index
     * @throws JamboJetValidationException
     */
    private function validateJourneyFareInfo(array $fareInfo, int $journeyIndex): void
    {
        if (isset($fareInfo['totalPrice'])) {
            $this->validateFormats(['price' => $fareInfo['totalPrice']], ['price' => 'positive_number']);
        }

        if (isset($fareInfo['currency'])) {
            $this->validateFormats($fareInfo, ['currency' => 'currency_code']);
        }

        if (isset($fareInfo['fareType'])) {
            $validFareTypes = ['Published', 'Private', 'Corporate', 'Promotional'];
            if (!in_array($fareInfo['fareType'], $validFareTypes)) {
                throw new JamboJetValidationException(
                    "Invalid fare type for journey {$journeyIndex}",
                    400
                );
            }
        }
    }

    /**
     * Validate segment fare information
     * 
     * @param array $fareInfo Fare information
     * @param int $segIndex Segment index
     * @param string $context Context
     * @throws JamboJetValidationException
     */
    private function validateSegmentFareInfo(array $fareInfo, int $segIndex, string $context): void
    {
        if (isset($fareInfo['baseFare'])) {
            $this->validateFormats(['fare' => $fareInfo['baseFare']], ['fare' => 'non_negative_number']);
        }

        if (isset($fareInfo['taxes'])) {
            $this->validateFormats(['taxes' => $fareInfo['taxes']], ['taxes' => 'non_negative_number']);
        }

        if (isset($fareInfo['fees'])) {
            $this->validateFormats(['fees' => $fareInfo['fees']], ['fees' => 'non_negative_number']);
        }
    }

    /**
     * Validate booking payment information
     * 
     * @param array $paymentInfo Payment information
     * @throws JamboJetValidationException
     */
    private function validateBookingPaymentInfo(array $paymentInfo): void
    {
        if (isset($paymentInfo['totalAmount'])) {
            $this->validateFormats(['amount' => $paymentInfo['totalAmount']], ['amount' => 'positive_number']);
        }

        if (isset($paymentInfo['currency'])) {
            $this->validateFormats($paymentInfo, ['currency' => 'currency_code']);
        }

        if (isset($paymentInfo['paymentMethods'])) {
            foreach ($paymentInfo['paymentMethods'] as $index => $method) {
                if (isset($method['amount'])) {
                    $this->validateFormats(['amt' => $method['amount']], ['amt' => 'positive_number']);
                }
            }
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
    }

    /**
     * Get available booking operations
     * 
     * @return array Available operations and their descriptions
     */
    public function getAvailableOperations(): array
    {
        return [
            'stateful_operations' => [
                'create' => [
                    'method' => 'create',
                    'description' => 'Create new booking (commits stateful booking)',
                    'endpoint' => '/api/nsk/v3/booking (POST)',
                ],
                'update' => [
                    'method' => 'update',
                    'description' => 'Update booking in state',
                    'endpoint' => '/api/nsk/v3/booking (PUT)',
                ],
                'get_current' => [
                    'method' => 'getCurrentBooking',
                    'description' => 'Get current booking in session state',
                    'endpoint' => '/api/nsk/v1/booking (GET)',
                ],
                'commit' => [
                    'method' => 'commit',
                    'description' => 'Commit booking changes',
                    'endpoint' => '/api/nsk/v3/booking (POST)',
                ],
                'status' => [
                    'method' => 'getCommitStatus',
                    'description' => 'Get booking commit status',
                    'endpoint' => '/api/nsk/v2/booking/status (GET)',
                ]
            ],
            'stateless_operations' => [
                'retrieve' => [
                    'method' => 'getByRecordLocator',
                    'description' => 'Retrieve booking by record locator',
                    'endpoint' => '/api/nsk/v1/bookings/{recordLocator} (GET)',
                ],
                'search' => [
                    'method' => 'searchBookings',
                    'description' => 'Search bookings with criteria',
                    'endpoint' => '/api/nsk/v1/bookings (GET)',
                ],
                'quote' => [
                    'method' => 'getQuote',
                    'description' => 'Get booking quote (stateless)',
                    'endpoint' => '/api/nsk/v2/bookings/quote (POST)',
                ],
                'history' => [
                    'method' => 'getHistory',
                    'description' => 'Get booking history',
                    'endpoint' => '/api/nsk/v1/bookings/{recordLocator}/history (GET)',
                ]
            ],
            'communication' => [
                'notification' => [
                    'method' => 'sendNotification',
                    'description' => 'Send booking notification',
                    'endpoint' => '/api/nsk/v2/bookings/{recordLocator}/notification (POST)',
                ],
                'email' => [
                    'method' => 'sendEmail',
                    'description' => 'Send email notification',
                    'endpoint' => '/api/nsk/v1/bookings/{recordLocator}/email (POST)',
                ],
                'comments' => [
                    'method' => 'addComments',
                    'description' => 'Add booking comments',
                    'endpoint' => '/api/nsk/v3/bookings/{recordLocator}/comments (POST)',
                ]
            ],
            'convenience_methods' => [
                'find_by_name' => [
                    'method' => 'findByNameAndRecord',
                    'description' => 'Quick search by name and record locator',
                ],
                'find_by_email' => [
                    'method' => 'findByEmail',
                    'description' => 'Search bookings by email address',
                ],
                'find_by_phone' => [
                    'method' => 'findByPhone',
                    'description' => 'Search bookings by phone number',
                ]
            ]
        ];
    }
}
