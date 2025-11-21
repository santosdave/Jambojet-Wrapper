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
     * GET /api/nsk/v1/booking
     * 
     * @return array Booking object
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
     * Commit booking changes (create new booking)
     * POST /api/nsk/v3/booking
     * 
     * Can return new PNR in response if configured
     * (NskSystemOptions.ReturnPnrOnv3BookingPost = true)
     * 
     * @param array $commitData Commit request data
     * @return array Commit response with optional record locator
     * @throws JamboJetApiException
     */
    public function commitBooking(array $commitData = []): array
    {
        $this->validateCommitRequest($commitData);

        try {
            return $this->post('api/nsk/v3/booking', $commitData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to commit booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Commit booking changes (update existing booking)
     * PUT /api/nsk/v3/booking
     * 
     * Supports concurrent changes if allowConcurrentChanges=true
     * Uses MergeAndCommit2 in New Skies
     * 
     * @param array $commitData Commit request data
     * @param bool $allowConcurrentChanges Allow merge of concurrent changes
     * @return array Commit response
     * @throws JamboJetApiException
     */
    public function updateAndCommitBooking(
        array $commitData = [],
        bool $allowConcurrentChanges = false
    ): array {
        $this->validateCommitRequest($commitData);

        $params = [];
        if ($allowConcurrentChanges) {
            $params['allowConcurrentChanges'] = 'true';
        }

        try {
            return $this->put(
                'api/nsk/v3/booking',
                $commitData,
                $params
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update and commit booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate booking before commit or payment
     * GET /api/nsk/v1/booking/validation
     * 
     * Returns forecasted booking status and seat warnings
     * 
     * @return array Validation response
     * @throws JamboJetApiException
     */
    public function validateBooking(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/validation');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to validate booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking commit status (async check)
     * GET /api/nsk/v2/booking/status
     * 
     * Returns 200 when processed, 202 while processing
     * Non-persisted data (like payment attachments) only available in 200 response
     * 
     * @return array Status response with optional booking data
     * @throws JamboJetApiException
     */
    public function getBookingCommitStatus(): array
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
     * Divide current booking
     * POST /api/nsk/v2/booking/divide
     * 
     * Requires valid user to be logged in
     * 
     * @param array $divideRequest Divide request data
     * @return array New booking or cancellation confirmation
     * @throws JamboJetApiException
     */
    public function divideBooking(array $divideRequest): array
    {
        $this->validateDivideRequest($divideRequest);

        try {
            return $this->post('api/nsk/v2/booking/divide', $divideRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to divide booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get recommended hold date if available
     * GET /api/nsk/v2/booking/hold/available
     * 
     * @return array|null Hold date or null if not available
     * @throws JamboJetApiException
     */
    public function getAvailableHoldDate(): array
    {
        try {
            $response = $this->get('api/nsk/v2/booking/hold/available');
            return $response['data'] ?? [];
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return []; // Hold not available
            }
            throw new JamboJetApiException(
                'Failed to get hold date: ' . $e->getMessage(),
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

    // =================================================================
    // CONTACTS MANAGEMENT - PRIMARY CONTACT (5 endpoints)
    // =================================================================

    public function getContact(string $contactTypeCode): array
    {
        $this->validateContactTypeCode($contactTypeCode);

        try {
            return $this->get("api/nsk/v1/booking/contacts/{$contactTypeCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get contact: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getContactPhoneNumbers(string $contactTypeCode): array
    {
        $this->validateContactTypeCode($contactTypeCode);

        try {
            return $this->get("api/nsk/v1/booking/contacts/{$contactTypeCode}/phoneNumbers");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get contact phone numbers: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addContactPhoneNumber(string $contactTypeCode, int $phoneNumberType, string $number): array
    {
        $this->validateContactTypeCode($contactTypeCode);
        $this->validatePhoneNumberType($phoneNumberType);

        try {
            return $this->post("api/nsk/v1/booking/contacts/{$contactTypeCode}/phoneNumbers/{$phoneNumberType}", ['number' => $number]);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add phone number: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function updateContactPhoneNumber(string $contactTypeCode, int $phoneNumberType, string $number): array
    {
        $this->validateContactTypeCode($contactTypeCode);
        $this->validatePhoneNumberType($phoneNumberType);

        try {
            return $this->put("api/nsk/v1/booking/contacts/{$contactTypeCode}/phoneNumbers/{$phoneNumberType}", ['number' => $number]);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to update phone number: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteContactPhoneNumber(string $contactTypeCode, int $phoneNumberType): array
    {
        $this->validateContactTypeCode($contactTypeCode);
        $this->validatePhoneNumberType($phoneNumberType);

        try {
            return $this->delete("api/nsk/v1/booking/contacts/{$contactTypeCode}/phoneNumbers/{$phoneNumberType}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete phone number: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get primary contact
     * GET /api/nsk/v1/booking/contacts/primary
     * 
     * @return array Contact data
     * @throws JamboJetApiException
     */
    public function getPrimaryContact(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/contacts/primary');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get primary contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create primary contact
     * POST /api/nsk/v1/booking/contacts/primary
     * 
     * @param array $contactData Contact request data
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function createPrimaryContact(array $contactData): array
    {
        $this->validateContactRequest($contactData);

        try {
            return $this->post('api/nsk/v1/booking/contacts/primary', $contactData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create primary contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update primary contact (full replace)
     * PUT /api/nsk/v1/booking/contacts/primary
     * 
     * @param array $contactData Contact request data
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function updatePrimaryContact(array $contactData): array
    {
        $this->validateContactRequest($contactData);

        try {
            return $this->put('api/nsk/v1/booking/contacts/primary', $contactData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update primary contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch primary contact (partial update)
     * PATCH /api/nsk/v1/booking/contacts/primary
     * 
     * @param array $contactData Partial contact data
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function patchPrimaryContact(array $contactData): array
    {
        try {
            return $this->patch('api/nsk/v1/booking/contacts/primary', $contactData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch primary contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete primary contact
     * DELETE /api/nsk/v1/booking/contacts/primary
     * 
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function deletePrimaryContact(): array
    {
        try {
            return $this->delete('api/nsk/v1/booking/contacts/primary');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete primary contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all contacts on booking
     * GET /api/nsk/v1/booking/contacts
     * 
     * Returns dictionary of contacts keyed by contactTypeCode (char)
     * 
     * @return array Contacts dictionary
     * @throws JamboJetApiException
     */
    public function getAllContacts(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/contacts');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get all contacts: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create new contact on booking
     * POST /api/nsk/v1/booking/contacts
     * 
     * ContactTypeCode must be unique on booking
     * 
     * @param array $contactData Contact data with contactTypeCode
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function createContact(array $contactData): array
    {
        // Validate contact type code is present
        if (!isset($contactData['contactTypeCode'])) {
            throw new JamboJetValidationException(
                'contactTypeCode is required',
                400
            );
        }

        $this->validateContactTypeCode($contactData['contactTypeCode']);
        $this->validateContactRequest($contactData);

        try {
            return $this->post('api/nsk/v1/booking/contacts', $contactData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update contact (full replace)
     * PUT /api/nsk/v1/booking/contacts/{contactTypeCode}
     * 
     * @param string $contactTypeCode Single character contact type code
     * @param array $contactData Contact request data
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function updateContact(string $contactTypeCode, array $contactData): array
    {
        $this->validateContactTypeCode($contactTypeCode);
        $this->validateContactRequest($contactData);

        try {
            return $this->put(
                "api/nsk/v1/booking/contacts/{$contactTypeCode}",
                $contactData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Patch contact (partial update)
     * PATCH /api/nsk/v1/booking/contacts/{contactTypeCode}
     * 
     * @param string $contactTypeCode Single character contact type code
     * @param array $contactData Partial contact data
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function patchContact(string $contactTypeCode, array $contactData): array
    {
        $this->validateContactTypeCode($contactTypeCode);

        try {
            return $this->patch(
                "api/nsk/v1/booking/contacts/{$contactTypeCode}",
                $contactData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to patch contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete contact
     * DELETE /api/nsk/v1/booking/contacts/{contactTypeCode}
     * 
     * @param string $contactTypeCode Single character contact type code
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function deleteContact(string $contactTypeCode): array
    {
        $this->validateContactTypeCode($contactTypeCode);

        try {
            return $this->delete("api/nsk/v1/booking/contacts/{$contactTypeCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete contact: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // ==================== PASSENGER METHODS ====================

    public function getPassenger(string $passengerKey): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->get("api/nsk/v1/booking/passengers/{$passengerKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get passenger: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function patchPassenger(string $passengerKey, array $patchData): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->patch("api/nsk/v2/booking/passengers/{$passengerKey}", $patchData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to patch passenger: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getPassengerFees(string $passengerKey): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->get("api/nsk/v1/booking/passengers/{$passengerKey}/fees");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get passenger fees: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addPassengerFee(string $passengerKey, array $feeData): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->post("api/nsk/v1/booking/passengers/{$passengerKey}/fees", $feeData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add passenger fee: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deletePassengerFee(string $passengerKey, string $feeKey): array
    {
        $this->validatePassengerKey($passengerKey);
        $this->validateFeeKey($feeKey);

        try {
            return $this->delete("api/nsk/v1/booking/passengers/{$passengerKey}/fees/{$feeKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete passenger fee: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // ==================== INFANT MANAGEMENT ====================

    public function addInfant(string $passengerKey, array $infantData): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->post("api/nsk/v1/booking/passengers/{$passengerKey}/infant", $infantData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add infant: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function updateInfant(string $passengerKey, array $infantData): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->put("api/nsk/v1/booking/passengers/{$passengerKey}/infant", $infantData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to update infant: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function removeInfant(string $passengerKey): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->delete("api/nsk/v1/booking/passengers/{$passengerKey}/infant");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to remove infant: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // ==================== PASSENGER LOYALTY ====================

    public function addPassengerLoyalty(string $passengerKey, array $loyaltyData): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->post("api/nsk/v2/booking/passengers/{$passengerKey}/loyaltyProgram", $loyaltyData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add passenger loyalty: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deletePassengerLoyalty(string $passengerKey): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->delete("api/nsk/v2/booking/passengers/{$passengerKey}/loyaltyProgram");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete passenger loyalty: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // ==================== PASSENGER TRAVEL DOCUMENTS ====================

    public function getPassengerTravelDocuments(string $passengerKey): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->get("api/nsk/v1/booking/passengers/{$passengerKey}/travelDocuments");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get travel documents: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addPassengerTravelDocument(string $passengerKey, array $documentData): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->post("api/nsk/v2/booking/passengers/{$passengerKey}/travelDocuments", $documentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add travel document: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function updatePassengerTravelDocument(string $passengerKey, string $documentKey, array $documentData): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->put("api/nsk/v2/booking/passengers/{$passengerKey}/travelDocuments/{$documentKey}", $documentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to update travel document: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deletePassengerTravelDocument(string $passengerKey, string $documentKey): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->delete("api/nsk/v2/booking/passengers/{$passengerKey}/travelDocuments/{$documentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete travel document: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // ==================== JOURNEYS & SEGMENTS ====================

    public function getJourney(string $journeyKey): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->get("api/nsk/v1/booking/journeys/{$journeyKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteJourney(string $journeyKey): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->delete("api/nsk/v2/booking/journeys/{$journeyKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getJourneySegments(string $journeyKey): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->get("api/nsk/v1/booking/journeys/{$journeyKey}/segments");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get journey segments: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getSegment(string $journeyKey, string $segmentKey): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->get("api/nsk/v1/booking/journeys/{$journeyKey}/segments/{$segmentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getLeg(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v1/booking/legs/{$legKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get leg: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // ==================== FEES ====================

    public function getFees(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/fees');
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get fees: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addFee(array $feeData): array
    {
        try {
            return $this->post('api/nsk/v1/booking/fees', $feeData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add fee: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteFee(string $feeKey): array
    {
        $this->validateFeeKey($feeKey);

        try {
            return $this->delete("api/nsk/v1/booking/fees/{$feeKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete fee: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function waiveFees(array $waiveRequest): array
    {
        try {
            return $this->post('api/nsk/v2/booking/fees/waive', $waiveRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to waive fees: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // ==================== SSRS ====================

    public function getSsrs(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/ssrs');
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get SSRs: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addSsrByKey(string $ssrKey, array $request): array
    {
        $this->validateSsrKey($ssrKey);

        try {
            return $this->post("api/nsk/v3/booking/ssrs/{$ssrKey}", $request);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add SSR by key: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addSsrsManual(array $ssrRequest): array
    {
        try {
            return $this->post('api/nsk/v2/booking/ssrs/manual', $ssrRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add SSRs manual: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // ==================== OTHER OPERATIONS ====================

    public function updateGroupName(array $groupNameRequest): array
    {
        try {
            return $this->put('api/nsk/v2/booking/groupName', $groupNameRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to update group name: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }


    public function addBookingLoyalty(array $loyaltyData): array
    {
        try {
            return $this->post('api/nsk/v2/booking/loyaltyProgram', $loyaltyData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add booking loyalty: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteBookingLoyalty(): array
    {
        try {
            return $this->delete('api/nsk/v2/booking/loyaltyProgram');
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete booking loyalty: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function dequeuesBooking(string $bookingQueueKey, array $dequeueRequest): array
    {
        try {
            return $this->delete("api/nsk/v1/booking/queues/{$bookingQueueKey}", $dequeueRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to dequeue booking: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function updateSalesChannel(?int $channelType): array
    {
        try {
            return $this->put('api/nsk/v1/booking/salesChannel', ['channelType' => $channelType]);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to update sales channel: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }


    public function getIndiaGstRequirement(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/indiaGstRegistrationRequirement');
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get India GST requirement: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function printItinerary(array $printRequest): array
    {
        try {
            return $this->post('api/nsk/v1/booking/itinerary/print', $printRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to print itinerary: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }



    // =================================================================
// PHONE NUMBER MANAGEMENT (5 endpoints)
// =================================================================

    /**
     * Get specific phone number
     * GET /api/nsk/v1/booking/contacts/{contactTypeCode}/phoneNumbers/{phoneNumberType}
     * 
     * @param string $contactTypeCode Single character contact type code
     * @param int $phoneNumberType Phone type (0-4: Other, Home, Work, Mobile, Fax)
     * @return array Phone number data
     * @throws JamboJetApiException
     */
    public function getContactPhoneNumber(string $contactTypeCode, int $phoneNumberType): array
    {
        $this->validateContactTypeCode($contactTypeCode);
        $this->validatePhoneNumberType($phoneNumberType);

        try {
            return $this->get(
                "api/nsk/v1/booking/contacts/{$contactTypeCode}/phoneNumbers/{$phoneNumberType}"
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get phone number: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


     // =================================================================
    // BOOKINGS MANAGEMENT - RETRIEVE & QUOTE (2 endpoints)
    // =================================================================

    /**
     * Retrieve booking without loading into state
     * GET /api/nsk/v2/bookings
     * 
     * Uses configured booking retrieve strategies
     * Available strategies: firstAndLastName, email, originAndDepartureDate, 
     *                       lastName, customerNumber
     * 
     * @param array $searchCriteria Search parameters
     * @return array Booking data
     * @throws JamboJetApiException
     */
    public function retrieveBooking(array $searchCriteria): array
    {
        $this->validateBookingSearchCriteria($searchCriteria);

        try {
            return $this->get('api/nsk/v2/bookings', $searchCriteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to retrieve booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get itinerary quote (stateless)
     * POST /api/nsk/v2/bookings/quote
     * 
     * @param array $quoteRequest Quote request data
     * @return array Itinerary quote with pricing
     * @throws JamboJetApiException
     */
    public function getItineraryQuote(array $quoteRequest): array
    {
        $this->validateQuoteRequest($quoteRequest);

        try {
            return $this->post('api/nsk/v2/bookings/quote', $quoteRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get itinerary quote: ' . $e->getMessage(),
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
     * @param array $emailData Optional email data
     * @return array Email response
     * @throws JamboJetApiException
     */
    public function sendEmail(string $recordLocator, array $emailData = []): array
    {
        if (empty($recordLocator)) {
            throw new JamboJetValidationException('Record locator is required');
        }

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/email", $emailData);
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

    /**
     * BookingService - Phase 3A: Baggage & Boarding Operations
     * 
     * Add these methods to the main BookingService class
     */

    // =================================================================
    // PHASE 3A: BAGGAGE MANAGEMENT (5 endpoints)
    // =================================================================

    /**
     * Remove bag from booking (stateless)
     * DELETE /api/dcs/v1/baggage
     * 
     * Agent only. Requires booking to be committed.
     * Sets bag status to 'Removed'
     * 
     * @param array $removeRequest Remove baggage request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function removeBaggage(array $removeRequest): array
    {
        $this->validateRemoveBaggageRequest($removeRequest);

        try {
            return $this->delete('api/dcs/v1/baggage', $removeRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove baggage: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove bag by leg key (stateless)
     * DELETE /api/dcs/v1/baggage/byLegKey
     * 
     * All downline legs/segments will have bag marked as Removed
     * For whole journey removal, pass first leg key
     * 
     * @param array $removeRequest Remove baggage by leg key request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function removeBaggageByLegKey(array $removeRequest): array
    {
        $this->validateRemoveBaggageByLegKeyRequest($removeRequest);

        try {
            return $this->delete('api/dcs/v1/baggage/byLegKey', $removeRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove baggage by leg key: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Check-in individual baggage item
     * PUT /api/dcs/v2/baggage/checkIn
     * 
     * Can update weight and baggage type code
     * If journeyKey set, processes all segments (ignores segmentKey)
     * Does NOT sync with booking baggage allowances
     * 
     * @param array $checkInRequest Bag check-in request
     * @return array Success response (may include 207 Multi-Status)
     * @throws JamboJetApiException
     */
    public function checkInBaggage(array $checkInRequest): array
    {
        $this->validateBaggageCheckInRequest($checkInRequest);

        try {
            return $this->put('api/dcs/v2/baggage/checkIn', $checkInRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to check-in baggage: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get bag tag printers
     * GET /api/dcs/v1/bagTag/printers
     * 
     * Returns printers based on logged-in user's location
     * Falls back to default printers if location not configured
     * 
     * @return array Bag tag printers list
     * @throws JamboJetApiException
     */
    public function getBagTagPrinters(): array
    {
        try {
            return $this->get('api/dcs/v1/bagTag/printers');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get bag tag printers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * BookingService - Phase 3B: SSR (Special Service Request) Management
     * 
     * Add these methods to the main BookingService class
     */

    // =================================================================
    // PHASE 3B: SSR MANAGEMENT (11 endpoints)
    // =================================================================

    /**
     * Get all SSRs on booking
     * GET /api/nsk/v1/booking/ssrs
     * 
     * @return array List of passenger SSRs
     * @throws JamboJetApiException
     */
    public function getAllSSRs(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/ssrs');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSRs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add SSRs by keys (batch)
     * POST /api/nsk/v3/booking/ssrs
     * 
     * Adding by journey automatically adds to all segments
     * 
     * @param array $ssrRequest SSR by keys request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function addSSRs(array $ssrRequest): array
    {
        $this->validateSsrByKeysRequest($ssrRequest);

        try {
            return $this->post('api/nsk/v3/booking/ssrs', $ssrRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add SSRs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get specific SSR
     * GET /api/nsk/v1/booking/ssrs/{ssrKey}
     * 
     * @param string $ssrKey SSR key
     * @return array SSR details
     * @throws JamboJetApiException
     */
    public function getSSR(string $ssrKey): array
    {
        if (empty($ssrKey)) {
            throw new JamboJetValidationException('SSR key is required', 400);
        }

        try {
            return $this->get("api/nsk/v1/booking/ssrs/{$ssrKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update SSR note
     * PUT /api/nsk/v1/booking/ssrs/{ssrKey}
     * 
     * Only updates the note field
     * 
     * @param string $ssrKey SSR key
     * @param string $note New note (max 255 chars)
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function updateSSRNote(string $ssrKey, string $note): array
    {
        if (empty($ssrKey)) {
            throw new JamboJetValidationException('SSR key is required', 400);
        }

        if (strlen($note) > 255) {
            throw new JamboJetValidationException(
                'SSR note cannot exceed 255 characters',
                400
            );
        }

        try {
            // Note: Request body should be an array with the note
            return $this->put("api/nsk/v1/booking/ssrs/{$ssrKey}", ['note' => $note]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update SSR note: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete SSR by key
     * DELETE /api/nsk/v1/booking/ssrs/{ssrKey}
     * 
     * @param string $ssrKey SSR key
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function deleteSSR(string $ssrKey): array
    {
        if (empty($ssrKey)) {
            throw new JamboJetValidationException('SSR key is required', 400);
        }

        try {
            return $this->delete("api/nsk/v1/booking/ssrs/{$ssrKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete SSR: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add single SSR by key
     * POST /api/nsk/v3/booking/ssrs/{ssrKey}
     * 
     * @param string $ssrKey SSR key to add
     * @param array $ssrRequest Single SSR request
     * @return array Success response with possible warnings
     * @throws JamboJetApiException
     */
    public function addSingleSSR(string $ssrKey, array $ssrRequest): array
    {
        if (empty($ssrKey)) {
            throw new JamboJetValidationException('SSR key is required', 400);
        }

        $this->validateSingleSsrRequest($ssrRequest);

        try {
            return $this->post("api/nsk/v3/booking/ssrs/{$ssrKey}", $ssrRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add SSR: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get SSR availability
     * POST /api/nsk/v2/booking/ssrs/availability
     * 
     * By default returns all SSR availability for booking
     * Infant SSR availability not included by default
     * 
     * @param array $availabilityRequest Availability filter request
     * @return array SSR availability
     * @throws JamboJetApiException
     */
    public function getSSRAvailability(array $availabilityRequest = []): array
    {
        try {
            return $this->post('api/nsk/v2/booking/ssrs/availability', $availabilityRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get SSR availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Resell cancelled SSR bundles
     * POST /api/nsk/v1/booking/ssrs/bundles/resell
     * 
     * @param array $resellRequest Bundle resell request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function resellSSRBundles(array $resellRequest): array
    {
        $this->validateRequired($resellRequest, ['journeyKey']);

        try {
            return $this->post('api/nsk/v1/booking/ssrs/bundles/resell', $resellRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to resell SSR bundles: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete SSR manually (by criteria)
     * DELETE /api/nsk/v1/booking/ssrs/manual
     * 
     * Alternate deletion method if not using dynamic SSR info
     * 
     * @param array $ssrCriteria SSR identification criteria
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function deleteSSRManual(array $ssrCriteria): array
    {
        $this->validateManualSsrCriteria($ssrCriteria);

        try {
            return $this->delete('api/nsk/v1/booking/ssrs/manual', $ssrCriteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete SSR manually: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add SSR manually (without dynamic info)
     * POST /api/nsk/v2/booking/ssrs/manual
     * 
     * For when UI not querying availability
     * 
     * @param array $ssrRequest Manual SSR request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function addSSRManual(array $ssrRequest): array
    {
        $this->validateManualSsrRequest($ssrRequest);

        try {
            return $this->post('api/nsk/v2/booking/ssrs/manual', $ssrRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add SSR manually: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Resell cancelled SSRs (after flight changes)
     * POST /api/nsk/v2/booking/ssrs/resell
     * 
     * Supports seat-dependent SSR reselling with auto-assignment
     * 
     * @param array $resellRequest Resell SSR request
     * @return array Success response with warnings
     * @throws JamboJetApiException
     */
    public function resellSSRs(array $resellRequest): array
    {
        $this->validateResellSsrRequest($resellRequest);

        try {
            return $this->post('api/nsk/v2/booking/ssrs/resell', $resellRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to resell SSRs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * BookingService - Phase 3C: E-Ticketing, Queues, Promotions, History
     * 
     * Add these methods to the main BookingService class
     */

    // =================================================================
    // E-TICKETING (3 endpoints)
    // =================================================================

    /**
     * Issue or re-issue e-tickets (voluntary changes)
     * POST /api/nsk/v1/booking/eTickets
     * 
     * For new tickets or voluntary change re-issuance
     * May issue new tickets if re-issuance not allowed
     * 
     * @return array Ticket numbers
     * @throws JamboJetApiException
     */
    public function issueETickets(): array
    {
        try {
            return $this->post('api/nsk/v1/booking/eTickets');
        } catch (\Exception $e) {
            // 502 = ticketing service error
            throw new JamboJetApiException(
                'Failed to issue e-tickets: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Re-issue e-tickets (involuntary changes)
     * PUT /api/nsk/v1/booking/eTickets
     * 
     * For IROP, schedule changes, etc.
     * May issue new tickets if re-issuance not allowed
     * 
     * @return array Ticket numbers
     * @throws JamboJetApiException
     */
    public function reissueETickets(): array
    {
        try {
            return $this->put('api/nsk/v1/booking/eTickets');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to re-issue e-tickets: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Validate e-ticket eligibility
     * GET /api/nsk/v1/booking/eTickets/validation
     * 
     * @return array Validation response
     * @throws JamboJetApiException
     */
    public function validateETicketing(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/eTickets/validation');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to validate e-ticketing: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// QUEUE MANAGEMENT (3 endpoints)
// =================================================================

    /**
     * Add booking to queue
     * POST /api/nsk/v3/booking/queue
     * 
     * @param array $queueRequest Queue request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function enqueueBooking(array $queueRequest): array
    {
        $this->validateQueueRequest($queueRequest);

        try {
            return $this->post('api/nsk/v3/booking/queue', $queueRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to enqueue booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove booking from queue
     * DELETE /api/nsk/v2/booking/queue
     * 
     * @param array $dequeueRequest Dequeue request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function dequeueBooking(string $bookingQueueKey, array $dequeueRequest): array
    {
        if (empty($bookingQueueKey)) {
            throw new JamboJetValidationException('Booking queue key is required', 400);
        }

        try {
            return $this->delete("api/nsk/v2/booking/queue/{$bookingQueueKey}", $dequeueRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to dequeue booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove booking by queue key
     * DELETE /api/nsk/v1/booking/queues/{bookingQueueKey}
     * 
     * @param string $bookingQueueKey Booking queue key
     * @param array $dequeueRequest Dequeue request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function dequeueByKey(string $bookingQueueKey, array $dequeueRequest = []): array
    {
        if (empty($bookingQueueKey)) {
            throw new JamboJetValidationException('Booking queue key is required', 400);
        }

        try {
            return $this->delete(
                "api/nsk/v1/booking/queues/{$bookingQueueKey}",
                $dequeueRequest
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to dequeue booking by key: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// BOOKINGS QUEUE MANAGEMENT (3 endpoints)
// =================================================================

    /**
     * Queue bookings by inventory leg
     * POST /api/nsk/v1/bookings/queues
     * 
     * @param array $queueRequest Inventory leg queue request
     * @return array Success response with warnings
     * @throws JamboJetApiException
     */
    public function queueBookingsByInventoryLeg(array $queueRequest): array
    {
        $this->validateInventoryQueueRequest($queueRequest);

        try {
            return $this->post('api/nsk/v1/bookings/queues', $queueRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to queue bookings by inventory leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


// =================================================================
// PROMOTION MANAGEMENT (3 endpoints)
// =================================================================

    /**
     * Add promotion code
     * POST /api/nsk/v1/booking/promotion
     * 
     * @param array $promotionRequest Promotion request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function addPromotionCode(array $promotionRequest): array
    {
        $this->validatePromotionRequest($promotionRequest);

        try {
            return $this->post('api/nsk/v1/booking/promotion', $promotionRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add promotion code: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update promotion code
     * PUT /api/nsk/v1/booking/promotion
     * 
     * @param array $promotionRequest Promotion request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function updatePromotionCode(array $promotionRequest): array
    {
        $this->validatePromotionRequest($promotionRequest);

        try {
            return $this->put('api/nsk/v1/booking/promotion', $promotionRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update promotion code: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete promotion code
     * DELETE /api/nsk/v1/booking/promotion
     * 
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function deletePromotionCode(): array
    {
        try {
            return $this->delete('api/nsk/v1/booking/promotion');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete promotion code: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// ADDITIONAL BOOKING OPERATIONS
// =================================================================

    /**
     * Delete group name
     * DELETE /api/nsk/v1/booking/groupName
     * 
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function deleteGroupName(): array
    {
        try {
            return $this->delete('api/nsk/v1/booking/groupName');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete group name: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Set group name
     * PUT /api/nsk/v2/booking/groupName
     * 
     * @param array $groupNameRequest Group name request
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function setGroupName(array $groupNameRequest): array
    {
        $this->validateRequired($groupNameRequest, ['groupName']);

        try {
            return $this->put('api/nsk/v2/booking/groupName', $groupNameRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to set group name: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update system code
     * PUT /api/nsk/v1/booking/systemCode
     * 
     * @param string $systemCode New system code
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function updateSystemCode(string $systemCode): array
    {
        if (empty($systemCode)) {
            throw new JamboJetValidationException('System code is required', 400);
        }

        try {
            return $this->put('api/nsk/v1/booking/systemCode', ['systemCode' => $systemCode]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update system code: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete system code
     * DELETE /api/nsk/v1/booking/systemCode
     * 
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function deleteSystemCode(): array
    {
        try {
            return $this->delete('api/nsk/v1/booking/systemCode');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete system code: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Override sales channel
     * PUT /api/nsk/v1/booking/salesChannel
     * 
     * @param int $channelType Channel type (0-5)
     * @return array Success response
     * @throws JamboJetApiException
     */
    public function overrideSalesChannel(int $channelType): array
    {
        // 0=Direct, 1=Web, 2=Api, 3=DigitalApi, 4=DigitalWeb, 5=Ndc
        if ($channelType < 0 || $channelType > 5) {
            throw new JamboJetValidationException(
                'Channel type must be 0-5',
                400
            );
        }

        try {
            return $this->put('api/nsk/v1/booking/salesChannel', ['channelType' => $channelType]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to override sales channel: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Check India GST registration requirement
     * GET /api/nsk/v1/booking/indiaGstRegistrationRequirement
     * 
     * @return bool Whether GST registration is required
     * @throws JamboJetApiException
     */
    public function checkIndiaGstRequirement(): bool
    {
        try {
            $response = $this->get('api/nsk/v1/booking/indiaGstRegistrationRequirement');
            return $response['data'] ?? false;
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to check GST requirement: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // =================================================================
    // BOARDING OPERATIONS (2 endpoints)
    // =================================================================


    /**
     * Board all passengers on leg
     * POST /api/dcs/v2/boarding/legs/{legKey}/passengers
     * 
     * If not change of gauge: boards downstream legs too
     * If change of gauge: boards only specified leg
     * Check lift status with manifest endpoint
     * 
     * @param string $legKey Leg key to board
     * @return array Board all response with passenger details
     * @throws JamboJetApiException
     */
    public function boardAllPassengers(string $legKey): array
    {
        if (empty($legKey)) {
            throw new JamboJetValidationException('Leg key is required', 400);
        }

        try {
            return $this->post("api/dcs/v2/boarding/legs/{$legKey}/passengers");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to board passengers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
// COMMENTS IMPLEMENTATION (2 endpoints)
// =================================================================

    /**
     * Get booking comments
     * GET /api/nsk/v1/booking/comments
     */
    public function getComments(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/comments');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking comments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete booking comment
     * DELETE /api/nsk/v2/booking/comments/{commentKey}
     */
    public function deleteComment(string $commentKey): array
    {
        $this->validateCommentKey($commentKey);

        try {
            return $this->delete("api/nsk/v2/booking/comments/{$commentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// EQUIPMENT INFORMATION IMPLEMENTATION (4 endpoints)
// =================================================================

    /**
     * Get leg equipment information
     * GET /api/nsk/v2/booking/equipment/legs/{legKey}
     */
    public function getLegEquipment(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v2/booking/equipment/legs/{$legKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get leg equipment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get leg equipment properties
     * GET /api/nsk/v2/booking/equipment/legs/{legKey}/properties
     */
    public function getLegEquipmentProperties(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v2/booking/equipment/legs/{$legKey}/properties");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get leg equipment properties: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get segment equipment information
     * GET /api/nsk/v2/booking/equipment/segments/{segmentKey}
     */
    public function getSegmentEquipment(string $segmentKey): array
    {
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->get("api/nsk/v2/booking/equipment/segments/{$segmentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get segment equipment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get segment equipment properties
     * GET /api/nsk/v2/booking/equipment/segments/{segmentKey}/properties
     */
    public function getSegmentEquipmentProperties(string $segmentKey): array
    {
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->get("api/nsk/v2/booking/equipment/segments/{$segmentKey}/properties");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get segment equipment properties: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// BOOKING HISTORY IMPLEMENTATION (10 endpoints)
// =================================================================

    /**
     * Get booking history
     * GET /api/nsk/v1/booking/history
     */
    public function getBookingHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get bag tag print history
     * GET /api/nsk/v1/booking/history/bagTagPrint
     */
    public function getBagTagPrintHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history/bagTagPrint', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get bag tag print history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create bag tag print history record
     * POST /api/nsk/v1/booking/history/bagTagPrint
     */
    public function createBagTagPrintHistory(array $printData): array
    {
        $this->validateBagTagPrintData($printData);

        try {
            return $this->post('api/nsk/v1/booking/history/bagTagPrint', $printData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create bag tag print history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get flight move history
     * GET /api/nsk/v1/booking/history/flightMove
     */
    public function getFlightMoveHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history/flightMove', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get flight move history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get hold date change history
     * GET /api/nsk/v1/booking/history/holdDateChange
     */
    public function getHoldDateChangeHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history/holdDateChange', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get hold date change history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get itinerary sent history
     * GET /api/nsk/v1/booking/history/itinerarySent
     */
    public function getItinerarySentHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history/itinerarySent', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get itinerary sent history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get message history
     * GET /api/nsk/v1/booking/history/message
     */
    public function getMessageHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history/message', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get message history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get notification history
     * GET /api/nsk/v1/booking/history/notification
     */
    public function getNotificationHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history/notification', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get notification history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get seat assignment history
     * GET /api/nsk/v1/booking/history/seatAssignment
     */
    public function getSeatAssignmentHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history/seatAssignment', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get seat assignment history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get segment change history
     * GET /api/nsk/v1/booking/history/segmentChange
     */
    public function getSegmentChangeHistory(array $criteria = []): array
    {
        $this->validateHistoryCriteria($criteria);

        try {
            return $this->get('api/nsk/v1/booking/history/segmentChange', $criteria);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get segment change history: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// VERIFIED TRAVEL DOCUMENTS IMPLEMENTATION (1 endpoint)
// =================================================================

    /**
     * Add verified travel document
     * POST /api/nsk/v1/booking/verifiedTravelDocuments/segments/{segmentKey}/passengers/{passengerKey}
     */
    public function addVerifiedTravelDocument(
        string $segmentKey,
        string $passengerKey,
        array $documentData
    ): array {
        $this->validateSegmentKey($segmentKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateTravelDocumentData($documentData);

        try {
            return $this->post(
                "api/nsk/v1/booking/verifiedTravelDocuments/segments/{$segmentKey}/passengers/{$passengerKey}",
                $documentData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add verified travel document: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Send email for completed booking
     * POST /api/nsk/v1/bookings/{recordLocator}/email
     */
    public function sendBookingEmail(string $recordLocator, array $emailData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateEmailData($emailData);

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/email", $emailData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to send booking email: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add comments to completed booking
     * POST /api/nsk/v3/bookings/{recordLocator}/comments
     */
    public function addBookingComments(string $recordLocator, array $comments): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateCommentsArray($comments);

        try {
            return $this->post("api/nsk/v3/bookings/{$recordLocator}/comments", $comments);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add booking comments: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Queue bookings by inventory leg
     * POST /api/nsk/v1/bookings/queues
     */
    public function queueBookingsByLeg(array $queueRequest): array
    {
        $this->validateQueueRequest($queueRequest);

        try {
            return $this->post('api/nsk/v1/bookings/queues', $queueRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to queue bookings by leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Dequeue booking by queue key (stateless)
     * DELETE /api/nsk/v1/bookings/queues/{bookingQueueKey}
     */
    public function dequeueBookingByKey(string $bookingQueueKey, array $dequeueRequest): array
    {
        $this->validateBookingQueueKey($bookingQueueKey);
        $this->validateDequeueRequest($dequeueRequest);

        try {
            return $this->delete("api/nsk/v1/bookings/queues/{$bookingQueueKey}", $dequeueRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to dequeue booking by key: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking queue items by queue code
     * GET /api/nsk/v2/bookings/queues/{queueCode}/items
     */
    public function getBookingQueueItems(string $queueCode, array $filters = []): array
    {
        $this->validateQueueCode($queueCode);

        try {
            return $this->get("api/nsk/v2/bookings/queues/{$queueCode}/items", $filters);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking queue items: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Board passenger by leg
     * POST /api/dcs/v2/boarding/legs/{legKey}/passengers/{passengerKey}
     */
    public function boardPassengerByLeg(string $recordLocator, string $legKey, string $passengerKey): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateLegKey($legKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->post(
                "api/dcs/v2/boarding/legs/{$legKey}/passengers/{$passengerKey}",
                ['recordLocator' => $recordLocator]
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to board passenger: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Board all passengers on a leg
     * POST /api/dcs/v2/boarding/legs/{legKey}/passengers
     */
    public function boardAllPassengersByLeg(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->post("api/dcs/v2/boarding/legs/{$legKey}/passengers");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to board all passengers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Unboard passenger from leg
     * DELETE /api/dcs/v2/boarding/legs/{legKey}/passengers/{passengerKey}
     */
    public function unboardPassenger(string $recordLocator, string $legKey, string $passengerKey): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateLegKey($legKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->delete(
                "api/dcs/v2/boarding/legs/{$legKey}/passengers/{$passengerKey}",
                ['recordLocator' => $recordLocator]
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to unboard passenger: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Unboard all passengers from leg
     * DELETE /api/dcs/v2/boarding/legs/{legKey}/passengers
     */
    public function unboardAllPassengers(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->delete("api/dcs/v2/boarding/legs/{$legKey}/passengers");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to unboard all passengers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get boarding status for booking on specific leg
     * GET /api/dcs/v1/boarding/bookings/{recordLocator}/legs/{legKey}
     */
    public function getBoardingStatus(string $recordLocator, string $legKey): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/dcs/v1/boarding/bookings/{$recordLocator}/legs/{$legKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get boarding status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// BAGGAGE OPERATIONS (DCS)
// =================================================================

    /**
     * Add baggage to booking (stateless)
     * POST /api/dcs/v1/baggage
     */
    public function addBaggage(array $baggageData): array
    {
        $this->validateBaggageData($baggageData);

        try {
            return $this->post('api/dcs/v1/baggage', $baggageData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add baggage: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update baggage information
     * PUT /api/dcs/v1/baggage
     */
    public function updateBaggage(array $baggageData): array
    {
        $this->validateBaggageData($baggageData);

        try {
            return $this->put('api/dcs/v1/baggage', $baggageData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update baggage: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get baggage details for passenger on leg
     * GET /api/dcs/v1/baggage/bookings/{recordLocator}/legs/{legKey}/passengers/{passengerKey}
     */
    public function getBaggageDetails(string $recordLocator, string $legKey, string $passengerKey): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateLegKey($legKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->get("api/dcs/v1/baggage/bookings/{$recordLocator}/legs/{$legKey}/passengers/{$passengerKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get baggage details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all baggage for booking
     * GET /api/dcs/v1/baggage/bookings/{recordLocator}
     */
    public function getAllBaggage(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/dcs/v1/baggage/bookings/{$recordLocator}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get all baggage: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get baggage for booking on specific leg
     * GET /api/dcs/v1/baggage/bookings/{recordLocator}/legs/{legKey}
     */
    public function getBaggageByLeg(string $recordLocator, string $legKey): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/dcs/v1/baggage/bookings/{$recordLocator}/legs/{$legKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get baggage by leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get ancillary service availability for booking
     * GET /api/nsk/v1/bookings/{recordLocator}/ancillary/availability
     */
    public function getAncillaryAvailability(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}/ancillary/availability");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get ancillary availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Add ancillary service to booking
     * POST /api/nsk/v1/bookings/{recordLocator}/ancillary
     */
    public function addAncillaryService(string $recordLocator, array $ancillaryData): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateAncillaryData($ancillaryData);

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/ancillary", $ancillaryData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add ancillary service: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove ancillary service from booking
     * DELETE /api/nsk/v1/bookings/{recordLocator}/ancillary/{ancillaryKey}
     */
    public function removeAncillaryService(string $recordLocator, string $ancillaryKey): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateAncillaryKey($ancillaryKey);

        try {
            return $this->delete("api/nsk/v1/bookings/{$recordLocator}/ancillary/{$ancillaryKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove ancillary service: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// BOOKING MODIFICATIONS
// =================================================================

    /**
     * Split booking into multiple bookings
     * POST /api/nsk/v2/bookings/{recordLocator}/split
     */
    public function splitBooking(string $recordLocator, array $splitRequest): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateSplitRequest($splitRequest);

        try {
            return $this->post("api/nsk/v2/bookings/{$recordLocator}/split", $splitRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to split booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Merge multiple bookings into one
     * POST /api/nsk/v2/bookings/merge
     */
    public function mergeBookings(array $mergeRequest): array
    {
        $this->validateMergeRequest($mergeRequest);

        try {
            return $this->post('api/nsk/v2/bookings/merge', $mergeRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to merge bookings: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Move passenger to different booking
     * POST /api/nsk/v2/bookings/{recordLocator}/move
     */
    public function movePassenger(string $recordLocator, array $moveRequest): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateMoveRequest($moveRequest);

        try {
            return $this->post("api/nsk/v2/bookings/{$recordLocator}/move", $moveRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to move passenger: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// BOOKING LOCKS
// =================================================================

    /**
     * Lock booking to prevent concurrent modifications
     * POST /api/nsk/v1/bookings/{recordLocator}/lock
     */
    public function lockBooking(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->post("api/nsk/v1/bookings/{$recordLocator}/lock");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to lock booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Unlock booking
     * DELETE /api/nsk/v1/bookings/{recordLocator}/lock
     */
    public function unlockBooking(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->delete("api/nsk/v1/bookings/{$recordLocator}/lock");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to unlock booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking lock status
     * GET /api/nsk/v1/bookings/{recordLocator}/lock
     */
    public function getBookingLockStatus(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v1/bookings/{$recordLocator}/lock");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get lock status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// BOOKING NOTES & REMARKS
// =================================================================

    /**
     * Add note to booking in state
     * POST /api/nsk/v1/booking/notes
     */
    public function addBookingNote(array $noteData): array
    {
        $this->validateNoteData($noteData);

        try {
            return $this->post('api/nsk/v1/booking/notes', $noteData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add booking note: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all notes for booking in state
     * GET /api/nsk/v1/booking/notes
     */
    public function getBookingNotes(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/notes');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking notes: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update booking note
     * PUT /api/nsk/v1/booking/notes/{noteKey}
     */
    public function updateBookingNote(string $noteKey, array $noteData): array
    {
        $this->validateNoteKey($noteKey);
        $this->validateNoteData($noteData);

        try {
            return $this->put("api/nsk/v1/booking/notes/{$noteKey}", $noteData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update booking note: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete booking note
     * DELETE /api/nsk/v1/booking/notes/{noteKey}
     */
    public function deleteBookingNote(string $noteKey): array
    {
        $this->validateNoteKey($noteKey);

        try {
            return $this->delete("api/nsk/v1/booking/notes/{$noteKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete booking note: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// PRICING & PROMOTIONS
// =================================================================

    /**
     * Reprice booking with current fares
     * POST /api/nsk/v2/bookings/{recordLocator}/reprice
     */
    public function reprice(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->post("api/nsk/v2/bookings/{$recordLocator}/reprice");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to reprice booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get detailed price breakdown
     * GET /api/nsk/v2/bookings/{recordLocator}/pricing
     */
    public function getBookingPriceBreakdown(string $recordLocator): array
    {
        $this->validateRecordLocator($recordLocator);

        try {
            return $this->get("api/nsk/v2/bookings/{$recordLocator}/pricing");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get price breakdown: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Apply promotion code to booking
     * POST /api/nsk/v1/booking/promotions
     */
    public function applyPromotion(string $recordLocator, string $promotionCode): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validatePromotionCode($promotionCode);

        try {
            return $this->post('api/nsk/v1/booking/promotions', [
                'recordLocator' => $recordLocator,
                'promotionCode' => $promotionCode
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to apply promotion: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove promotion from booking
     * DELETE /api/nsk/v1/booking/promotions/{promotionCode}
     */
    public function removePromotion(string $recordLocator, string $promotionCode): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validatePromotionCode($promotionCode);

        try {
            return $this->delete("api/nsk/v1/booking/promotions/{$promotionCode}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to remove promotion: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// SPECIAL REQUESTS
// =================================================================

    /**
     * Add special request to booking
     * POST /api/nsk/v1/booking/specialRequests
     */
    public function addSpecialRequest(array $requestData): array
    {
        $this->validateSpecialRequestData($requestData);

        try {
            return $this->post('api/nsk/v1/booking/specialRequests', $requestData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to add special request: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get all special requests
     * GET /api/nsk/v1/booking/specialRequests
     */
    public function getSpecialRequests(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/specialRequests');
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get special requests: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update special request
     * PUT /api/nsk/v1/booking/specialRequests/{requestKey}
     */
    public function updateSpecialRequest(string $requestKey, array $requestData): array
    {
        $this->validateRequestKey($requestKey);
        $this->validateSpecialRequestData($requestData);

        try {
            return $this->put("api/nsk/v1/booking/specialRequests/{$requestKey}", $requestData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update special request: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete special request
     * DELETE /api/nsk/v1/booking/specialRequests/{requestKey}
     */
    public function deleteSpecialRequest(string $requestKey): array
    {
        $this->validateRequestKey($requestKey);

        try {
            return $this->delete("api/nsk/v1/booking/specialRequests/{$requestKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete special request: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

// =================================================================
// BOOKING VERSIONS & HISTORY
// =================================================================

    /**
     * Get specific version of booking
     * GET /api/nsk/v2/bookings/{recordLocator}/versions/{version}
     */
    public function getBookingVersion(string $recordLocator, int $version): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateVersionNumber($version);

        try {
            return $this->get("api/nsk/v2/bookings/{$recordLocator}/versions/{$version}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking version: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Compare two versions of booking
     * GET /api/nsk/v2/bookings/{recordLocator}/versions/compare
     */
    public function compareBookingVersions(string $recordLocator, int $version1, int $version2): array
    {
        $this->validateRecordLocator($recordLocator);
        $this->validateVersionNumber($version1);
        $this->validateVersionNumber($version2);

        try {
            return $this->get("api/nsk/v2/bookings/{$recordLocator}/versions/compare", [
                'version1' => $version1,
                'version2' => $version2
            ]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to compare booking versions: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get booking changes since specific date
     * GET /api/nsk/v2/bookings/{recordLocator}/changes
     */
    public function getBookingChanges(string $recordLocator, ?\DateTime $since = null): array
    {
        $this->validateRecordLocator($recordLocator);

        $params = [];
        if ($since !== null) {
            $params['since'] = $since->format('c');
        }

        try {
            return $this->get("api/nsk/v2/bookings/{$recordLocator}/changes", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking changes: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // JOURNEY OPERATIONS (EXTENDED)
    // =================================================================

    public function deleteAllJourneys(): array
    {
        try {
            return $this->delete('api/nsk/v1/booking/journeys');
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete all journeys: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteJourneyWithOptions(string $journeyKey, array $cancelOptions): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->delete("api/nsk/v1/booking/journeys/{$journeyKey}", $cancelOptions);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete journey with options: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function waiveJourneyPenaltyFee(string $journeyKey): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->delete("api/nsk/v1/booking/journeys/{$journeyKey}/fees/penalty");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to waive journey penalty fee: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // JOURNEY BUNDLES
    // =================================================================

    public function sellJourneyBundle(string $journeyKey, array $bundleRequest): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validateBundleRequest($bundleRequest);

        try {
            return $this->post("api/nsk/v2/booking/journeys/{$journeyKey}/bundles", $bundleRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to sell journey bundle: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deleteJourneyBundles(string $journeyKey, array $passengerKeys): array
    {
        $this->validateJourneyKey($journeyKey);

        if (empty($passengerKeys)) {
            throw new JamboJetValidationException('Passenger keys array cannot be empty', 400);
        }

        try {
            return $this->delete("api/nsk/v1/booking/journeys/{$journeyKey}/bundles", $passengerKeys);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete journey bundles: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // JOURNEY PASSENGER REQUIREMENTS
    // =================================================================

    public function getPassengerAddressRequirements(string $journeyKey, string $passengerKey): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->get("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/address/requirements");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get address requirements: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getPassengerAddressRequirementsAll(string $passengerKey): array
    {
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->get("api/nsk/v1/booking/journeys/passengers/{$passengerKey}/address/requirements");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get all address requirements: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getJourneyTravelDocumentRequirements(string $journeyKey): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->get("api/nsk/v1/booking/journeys/{$journeyKey}/travelDocument/requirements");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get journey travel document requirements: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getAllTravelDocumentRequirements(): array
    {
        try {
            return $this->get('api/nsk/v1/booking/journeys/travelDocument/requirements');
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get all travel document requirements: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // JOURNEY BAGGAGE OPERATIONS
    // =================================================================

    public function getPassengerBaggageByJourney(string $journeyKey, string $passengerKey): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->get("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/baggage");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get passenger baggage: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addPassengerBaggage(string $journeyKey, string $passengerKey, array $baggageRequest): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);
        $this->validatePassengerBaggageRequest($baggageRequest);

        try {
            return $this->post("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/baggage", $baggageRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add passenger baggage: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getPassengerBag(string $journeyKey, string $passengerKey, string $baggageKey): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateBaggageKey($baggageKey);

        try {
            return $this->get("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/baggage/{$baggageKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get passenger bag: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function deletePassengerBag(string $journeyKey, string $passengerKey, string $baggageKey): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateBaggageKey($baggageKey);

        try {
            return $this->delete("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/baggage/{$baggageKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete passenger bag: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function updatePassengerBag(string $journeyKey, string $passengerKey, string $baggageKey, array $updateRequest): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateBaggageKey($baggageKey);

        try {
            return $this->put("api/nsk/v2/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/baggage/{$baggageKey}", $updateRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to update passenger bag: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function checkinPassengerBag(string $journeyKey, string $passengerKey, string $baggageKey, array $checkinRequest): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateBaggageKey($baggageKey);

        try {
            return $this->put("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/baggage/{$baggageKey}/checkin", $checkinRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to check in passenger bag: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function updatePassengerBaggageGroup(string $journeyKey, string $passengerKey, array $groupRequest): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->put("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/baggage/group", $groupRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to update baggage group: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addManualBaggage(string $journeyKey, string $passengerKey, array $manualBagRequest): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->post("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/baggage/manual", $manualBagRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add manual baggage: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // JOURNEY INFANT OPERATIONS
    // =================================================================

    public function addInfantToJourney(string $journeyKey, string $passengerKey, array $infantData): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateInfantData($infantData);

        try {
            return $this->post("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/infant", $infantData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add infant to journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function removeInfantFromJourney(string $journeyKey, string $passengerKey): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->delete("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/infant");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to remove infant from journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // JOURNEY FEES
    // =================================================================

    public function waivePassengerJourneyFees(string $journeyKey, string $passengerKey, string $feeType): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->delete("api/nsk/v1/booking/journeys/{$journeyKey}/passengers/{$passengerKey}/fees/amount", ['feeType' => $feeType]);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to waive passenger journey fees: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // SEGMENT OPERATIONS
    // =================================================================

    public function deleteSegment(string $segmentKey): array
    {
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->delete("api/nsk/v1/booking/segments/{$segmentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to delete segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function modifySegmentStatus(string $journeyKey, string $segmentKey, int $newStatus): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validateSegmentKey($segmentKey);
        $this->validateSegmentStatus($newStatus);

        try {
            return $this->patch("api/nsk/v1/booking/journeys/{$journeyKey}/segments/{$segmentKey}/status", ['newStatus' => $newStatus]);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to modify segment status: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getPassengerSegment(string $segmentKey, string $passengerKey): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->get("api/nsk/v1/booking/segments/{$segmentKey}/passengers/{$passengerKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get passenger segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // SEGMENT INFANT OPERATIONS
    // =================================================================

    public function addInfantToSegment(string $segmentKey, string $passengerKey, array $infantData): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateInfantData($infantData);

        try {
            return $this->post("api/nsk/v1/booking/segments/{$segmentKey}/passengers/{$passengerKey}/infant", $infantData);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add infant to segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function removeInfantFromSegment(string $segmentKey, string $passengerKey): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validatePassengerKey($passengerKey);

        try {
            return $this->delete("api/nsk/v1/booking/segments/{$segmentKey}/passengers/{$passengerKey}/infant");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to remove infant from segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // SEGMENT TICKETS
    // =================================================================

    public function addTicket(string $segmentKey, string $passengerKey, array $ticketRequest): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateTicketRequest($ticketRequest);

        try {
            return $this->post("api/nsk/v1/booking/segments/{$segmentKey}/passengers/{$passengerKey}/tickets", $ticketRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to add ticket: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function updateTicket(string $segmentKey, string $passengerKey, array $ticketRequest): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validatePassengerKey($passengerKey);
        $this->validateTicketRequest($ticketRequest);

        try {
            return $this->put("api/nsk/v1/booking/segments/{$segmentKey}/passengers/{$passengerKey}/tickets", $ticketRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to update ticket: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // CLASS OF SERVICE
    // =================================================================

    public function getClassOfServiceAvailability(bool $isUpgrade, array $options = []): array
    {
        $params = array_merge(['isUpgrade' => $isUpgrade], $options);

        try {
            return $this->get('api/nsk/v1/booking/segments/classOfService/availability', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get class of service availability: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function modifyClassOfService(string $classModifyKey, array $modifyRequest): array
    {
        $this->validateClassModifyKey($classModifyKey);

        try {
            return $this->put("api/nsk/v1/booking/segments/classOfService/{$classModifyKey}", $modifyRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to modify class of service: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function resetClassOfService(string $segmentKey, ?bool $overSell = null): array
    {
        $this->validateSegmentKey($segmentKey);

        $params = [];
        if ($overSell !== null) {
            $params['overSell'] = $overSell;
        }

        try {
            return $this->delete("api/nsk/v1/booking/segments/{$segmentKey}/classOfService", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to reset class of service: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // BOARDING PASSES
    // =================================================================

    public function getBoardingPassesByJourney(string $journeyKey, array $filterRequest): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->post("api/nsk/v3/booking/boardingpasses/journey/{$journeyKey}", $filterRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get boarding passes by journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getBoardingPassesByJourneyM2D(string $journeyKey, array $passengerFilter): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->post("api/nsk/v1/booking/boardingpasses/m2d/journey/{$journeyKey}", $passengerFilter);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get M2D boarding passes: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getBoardingPassesByJourneyS2D(string $journeyKey, array $passengerFilter): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->post("api/nsk/v1/booking/boardingpasses/s2d/journey/{$journeyKey}", $passengerFilter);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get S2D boarding passes: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getBoardingPassesBySegment(string $segmentKey, array $passengerFilter): array
    {
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->post("api/nsk/v3/booking/boardingpasses/segment/{$segmentKey}", $passengerFilter);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get boarding passes by segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // CHECK-IN OPERATIONS
    // =================================================================

    public function checkinByJourney(string $journeyKey, array $checkinRequest): array
    {
        $this->validateJourneyKey($journeyKey);
        $this->validateCheckinRequest($checkinRequest);

        try {
            return $this->post("api/nsk/v3/booking/checkin/journey/{$journeyKey}", $checkinRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to check in by journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function uncheckinByJourney(string $journeyKey, array $uncheckinRequest): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->delete("api/nsk/v1/booking/checkin/journey/{$journeyKey}", $uncheckinRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to uncheck in by journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getCheckinRequirementsByJourney(string $journeyKey): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->get("api/nsk/v2/booking/checkin/journey/{$journeyKey}/requirements");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get check-in requirements by journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getCheckinStatusByJourney(string $journeyKey): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->get("api/nsk/v1/booking/checkin/journey/{$journeyKey}/status");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get check-in status by journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function checkinBySegment(string $segmentKey, array $checkinRequest): array
    {
        $this->validateSegmentKey($segmentKey);
        $this->validateCheckinRequest($checkinRequest);

        try {
            return $this->post("api/nsk/v3/booking/checkin/segment/{$segmentKey}", $checkinRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to check in by segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function uncheckinBySegment(string $segmentKey, array $uncheckinRequest): array
    {
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->delete("api/nsk/v1/booking/checkin/segment/{$segmentKey}", $uncheckinRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to uncheck in by segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getCheckinRequirementsBySegment(string $segmentKey): array
    {
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->get("api/nsk/v2/booking/checkin/segment/{$segmentKey}/requirements");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get check-in requirements by segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getCheckinStatusBySegment(string $segmentKey): array
    {
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->get("api/nsk/v1/booking/checkin/segment/{$segmentKey}/status");
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get check-in status by segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // SEAT OPERATIONS
    // =================================================================

    public function getSeatMapsForBooking(array $options = []): array
    {
        try {
            return $this->get('api/nsk/v3/booking/seatmaps', $options);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get seat maps: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getSeatMapsByJourney(string $journeyKey, array $options = []): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->get("api/nsk/v4/booking/seatmaps/journey/{$journeyKey}", $options);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get seat maps by journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getSeatMapsBySegment(string $segmentKey, array $options = []): array
    {
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->get("api/nsk/v3/booking/seatmaps/segment/{$segmentKey}", $options);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get seat maps by segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function autoAssignSeats(string $primaryPassengerKey, array $autoAssignRequest): array
    {
        $this->validatePassengerKey($primaryPassengerKey);

        try {
            return $this->post("api/nsk/v1/booking/seats/auto/{$primaryPassengerKey}", $autoAssignRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to auto assign seats: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function autoAssignSeatsByJourney(string $primaryPassengerKey, string $journeyKey, array $autoAssignRequest): array
    {
        $this->validatePassengerKey($primaryPassengerKey);
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->post("api/nsk/v1/booking/seats/auto/{$primaryPassengerKey}/journey/{$journeyKey}", $autoAssignRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to auto assign seats by journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function autoAssignSeatsBySegment(string $primaryPassengerKey, string $segmentKey, array $autoAssignRequest): array
    {
        $this->validatePassengerKey($primaryPassengerKey);
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->post("api/nsk/v1/booking/seats/auto/{$primaryPassengerKey}/segment/{$segmentKey}", $autoAssignRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to auto assign seats by segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getAutoAssignSeatFeeQuoteByJourney(string $primaryPassengerKey, string $journeyKey, array $quoteRequest): array
    {
        $this->validatePassengerKey($primaryPassengerKey);
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->post("api/nsk/v1/booking/seats/auto/{$primaryPassengerKey}/journey/{$journeyKey}/quote", $quoteRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get seat fee quote by journey: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getAutoAssignSeatFeeQuoteBySegment(string $primaryPassengerKey, string $segmentKey, array $quoteRequest): array
    {
        $this->validatePassengerKey($primaryPassengerKey);
        $this->validateSegmentKey($segmentKey);

        try {
            return $this->post("api/nsk/v1/booking/seats/auto/{$primaryPassengerKey}/segment/{$segmentKey}/quote", $quoteRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException('Failed to get seat fee quote by segment: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    // =================================================================
    // VALIDATION METHODS - UPDATED AND COMPREHENSIVE
    // =================================================================

    private function validateBaggageKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Baggage key is required', 400);
        }
    }

    private function validatePassengerBaggageRequest(array $request): void
    {
        if (!isset($request['weight'])) {
            throw new JamboJetValidationException('Baggage weight is required', 400);
        }
    }

    private function validateBundleRequest(array $request): void
    {
        if (!isset($request['bundleCode'])) {
            throw new JamboJetValidationException('Bundle code is required', 400);
        }

        if (!isset($request['passengerKeys']) || !is_array($request['passengerKeys'])) {
            throw new JamboJetValidationException('Passenger keys array is required', 400);
        }
    }

    private function validateSegmentStatus(int $status): void
    {
        // 0=UnknownStatus, 1=NoActionTaken, 2=UnableToConfirmSegment, 3=HeldConfirmed, 4=HeldCancelled, 5=ConfirmedTimeChange
        if ($status < 0 || $status > 5) {
            throw new JamboJetValidationException('Invalid segment status', 400);
        }
    }

    private function validateTicketRequest(array $request): void
    {
        if (!isset($request['ticketNumber'])) {
            throw new JamboJetValidationException('Ticket number is required', 400);
        }
    }

    private function validateClassModifyKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Class modify key is required', 400);
        }
    }

    private function validateCheckinRequest(array $request): void
    {
        if (!isset($request['passengerKeys']) || !is_array($request['passengerKeys'])) {
            throw new JamboJetValidationException('Passenger keys array is required for check-in', 400);
        }

        if (empty($request['passengerKeys'])) {
            throw new JamboJetValidationException('At least one passenger key is required for check-in', 400);
        }
    }

    private function validateAncillaryData(array $data): void
    {
        if (!isset($data['ancillaryCode'])) {
            throw new JamboJetValidationException('Ancillary code is required', 400);
        }
    }

    private function validateAncillaryKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Ancillary key is required', 400);
        }
    }

    private function validateSplitRequest(array $request): void
    {
        if (!isset($request['passengerKeys']) || !is_array($request['passengerKeys'])) {
            throw new JamboJetValidationException('Passenger keys array is required for split', 400);
        }
    }

    private function validateMergeRequest(array $request): void
    {
        if (!isset($request['recordLocators']) || !is_array($request['recordLocators'])) {
            throw new JamboJetValidationException('Record locators array is required for merge', 400);
        }

        if (count($request['recordLocators']) < 2) {
            throw new JamboJetValidationException('At least 2 bookings required for merge', 400);
        }
    }

    private function validateMoveRequest(array $request): void
    {
        if (!isset($request['passengerKey'])) {
            throw new JamboJetValidationException('Passenger key is required for move', 400);
        }

        if (!isset($request['targetRecordLocator'])) {
            throw new JamboJetValidationException('Target record locator is required', 400);
        }
    }

    private function validateNoteData(array $data): void
    {
        if (!isset($data['noteText']) || empty(trim($data['noteText']))) {
            throw new JamboJetValidationException('Note text is required', 400);
        }
    }

    private function validateNoteKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Note key is required', 400);
        }
    }

    private function validatePromotionCode(string $code): void
    {
        if (empty(trim($code))) {
            throw new JamboJetValidationException('Promotion code is required', 400);
        }
    }

    private function validateSpecialRequestData(array $data): void
    {
        if (!isset($data['requestType'])) {
            throw new JamboJetValidationException('Request type is required', 400);
        }
    }

    private function validateRequestKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Request key is required', 400);
        }
    }

    private function validateVersionNumber(int $version): void
    {
        if ($version < 1) {
            throw new JamboJetValidationException('Version number must be positive', 400);
        }
    }

    /**
     * Validate baggage data
     */
    private function validateBaggageData(array $data): void
    {
        if (!isset($data['recordLocator'])) {
            throw new JamboJetValidationException('Record locator is required', 400);
        }

        if (!isset($data['passengerKey'])) {
            throw new JamboJetValidationException('Passenger key is required', 400);
        }

        if (!isset($data['legKey'])) {
            throw new JamboJetValidationException('Leg key is required', 400);
        }

        if (!isset($data['weight'])) {
            throw new JamboJetValidationException('Baggage weight is required', 400);
        }

        if (!is_numeric($data['weight']) || $data['weight'] <= 0) {
            throw new JamboJetValidationException('Baggage weight must be a positive number', 400);
        }

        if (isset($data['weightUnit']) && !in_array(strtoupper($data['weightUnit']), ['KG', 'LB'])) {
            throw new JamboJetValidationException('Weight unit must be KG or LB', 400);
        }
    }

    /**
     * Validate boarding data
     */
    private function validateBoardingData(array $data): void
    {
        if (!isset($data['recordLocator'])) {
            throw new JamboJetValidationException('Record locator is required for boarding', 400);
        }
    }


    /**
     * Validate queue request
     */
    private function validateQueueRequest(array $request): void
    {
        if (!isset($request['legKey'])) {
            throw new JamboJetValidationException('Leg key is required for queue request', 400);
        }

        if (!isset($request['queueCode'])) {
            throw new JamboJetValidationException('Queue code is required', 400);
        }
    }

    /**
     * Validate booking queue key
     */
    private function validateBookingQueueKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Booking queue key is required', 400);
        }
    }

    /**
     * Validate queue code
     */
    private function validateQueueCode(string $code): void
    {
        if (empty(trim($code))) {
            throw new JamboJetValidationException('Queue code is required', 400);
        }
    }

    /**
     * Validate booking update request
     */
    private function validateBookingUpdateRequest(array $data): void
    {
        if (empty($data)) {
            throw new JamboJetValidationException('Update data cannot be empty', 400);
        }
    }

    /**
     * Validate journey key
     */
    private function validateJourneyKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Journey key is required', 400);
        }
    }

    /**
     * Validate fee key
     */
    private function validateFeeKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Fee key is required', 400);
        }
    }

    /**
     * Validate SSR key
     */
    private function validateSsrKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('SSR key is required', 400);
        }
    }

    /**
     * Validate comment key
     */
    private function validateCommentKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Comment key is required', 400);
        }
    }

    /**
     * Validate document key
     */
    private function validateDocumentKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Document key is required', 400);
        }
    }

    /**
     * Validate travel document data
     */
    private function validateTravelDocumentData(array $data): void
    {
        if (!isset($data['documentTypeCode'])) {
            throw new JamboJetValidationException('Document type code is required', 400);
        }

        if (!isset($data['issuedByCode'])) {
            throw new JamboJetValidationException('Issued by code is required', 400);
        }
    }

    /**
     * Validate history criteria
     */
    private function validateHistoryCriteria(array $criteria): void
    {
        if (isset($criteria['PageSize'])) {
            if ($criteria['PageSize'] < 10 || $criteria['PageSize'] > 5000) {
                throw new JamboJetValidationException('PageSize must be between 10 and 5000', 400);
            }
        }
    }

    /**
     * Validate remove baggage request
     */
    private function validateRemoveBaggageRequest(array $request): void
    {
        if (!isset($request['recordLocator'])) {
            throw new JamboJetValidationException('Record locator is required', 400);
        }

        if (!isset($request['passengerKey'])) {
            throw new JamboJetValidationException('Passenger key is required', 400);
        }

        if (!isset($request['legKey'])) {
            throw new JamboJetValidationException('Leg key is required', 400);
        }
    }

    /**
     * Validate infant data
     */
    private function validateInfantData(array $data): void
    {
        if (!isset($data['name'])) {
            throw new JamboJetValidationException('Infant name is required', 400);
        }

        if (!isset($data['name']['first']) || empty($data['name']['first'])) {
            throw new JamboJetValidationException('Infant first name is required', 400);
        }

        if (!isset($data['name']['last']) || empty($data['name']['last'])) {
            throw new JamboJetValidationException('Infant last name is required', 400);
        }

        if (isset($data['dateOfBirth'])) {
            $this->validateFormats(['dob' => $data['dateOfBirth']], ['dob' => 'datetime']);
        }
    }

    /**
     * Validate fee data
     */
    private function validateFeeData(array $data): void
    {
        if (!isset($data['code'])) {
            throw new JamboJetValidationException('Fee code is required', 400);
        }

        if (isset($data['amount']) && !is_numeric($data['amount'])) {
            throw new JamboJetValidationException('Fee amount must be numeric', 400);
        }
    }

    /**
     * Validate SSR request
     */
    private function validateSsrRequest(array $request): void
    {
        if (!isset($request['keys']) || !is_array($request['keys'])) {
            throw new JamboJetValidationException('SSR keys array is required', 400);
        }

        if (empty($request['keys'])) {
            throw new JamboJetValidationException('At least one SSR key is required', 400);
        }
    }

    /**
     * Validate waive fees request
     */
    private function validateWaiveFeesRequest(array $request): void
    {
        if (!isset($request['feeKeys']) || !is_array($request['feeKeys'])) {
            throw new JamboJetValidationException('Fee keys array is required', 400);
        }

        if (empty($request['feeKeys'])) {
            throw new JamboJetValidationException('At least one fee key is required', 400);
        }
    }

    /**
     * Validate loyalty data
     */
    private function validateLoyaltyData(array $data): void
    {
        if (!isset($data['programCode'])) {
            throw new JamboJetValidationException('Loyalty program code is required', 400);
        }

        if (!isset($data['programNumber'])) {
            throw new JamboJetValidationException('Loyalty program number is required', 400);
        }
    }

    /**
     * Validate group name request
     */
    private function validateGroupNameRequest(array $request): void
    {
        if (!isset($request['groupName']) || empty(trim($request['groupName']))) {
            throw new JamboJetValidationException('Group name is required', 400);
        }

        if (strlen($request['groupName']) > 100) {
            throw new JamboJetValidationException('Group name cannot exceed 100 characters', 400);
        }
    }

    /**
     * Validate print itinerary request
     */
    private function validatePrintItineraryRequest(array $request): void
    {
        if (!isset($request['printerName'])) {
            throw new JamboJetValidationException('Printer name is required', 400);
        }
    }

    /**
     * Validate dequeue request
     */
    private function validateDequeueRequest(array $request): void
    {
        if (!isset($request['recordLocator'])) {
            throw new JamboJetValidationException('Record locator is required for dequeue operation', 400);
        }
    }

    /**
     * Validate quote request
     */
    private function validateQuoteRequest(array $request): void
    {
        if (!isset($request['keys']) || !is_array($request['keys'])) {
            throw new JamboJetValidationException('Journey keys array is required', 400);
        }

        if (empty($request['keys'])) {
            throw new JamboJetValidationException('At least one journey key is required', 400);
        }
    }

    /**
     * Validate notification data
     */
    private function validateNotificationData(array $data): void
    {
        if (!isset($data['notificationTypeCode'])) {
            throw new JamboJetValidationException('Notification type code is required', 400);
        }
    }

    /**
     * Validate email data
     */
    private function validateEmailData(array $data): void
    {
        if (!isset($data['emailAddress'])) {
            throw new JamboJetValidationException('Email address is required', 400);
        }

        $this->validateFormats(['email' => $data['emailAddress']], ['email' => 'email']);
    }

    /**
     * Validate comments array
     */
    private function validateCommentsArray(array $comments): void
    {
        if (!is_array($comments) || empty($comments)) {
            throw new JamboJetValidationException('Comments array cannot be empty', 400);
        }

        foreach ($comments as $index => $comment) {
            if (!isset($comment['commentText']) || empty(trim($comment['commentText']))) {
                throw new JamboJetValidationException("Comment {$index}: commentText is required", 400);
            }
        }
    }

    /**
     * Validate search criteria
     */
    private function validateSearchCriteria(array $criteria): void
    {
        if (empty($criteria)) {
            throw new JamboJetValidationException('Search criteria cannot be empty', 400);
        }

        // At least one search parameter required
        $validParams = [
            'RecordLocator',
            'LastName',
            'FirstName',
            'EmailAddress',
            'PhoneNumber',
            'CustomerNumber',
            'DepartureDate'
        ];

        $hasValidParam = false;
        foreach ($validParams as $param) {
            if (isset($criteria[$param])) {
                $hasValidParam = true;
                break;
            }
        }

        if (!$hasValidParam) {
            throw new JamboJetValidationException('At least one search parameter is required', 400);
        }
    }

    /**
     * Validate contact data
     */
    private function validateContactData(array $data): void
    {
        if (!isset($data['name'])) {
            throw new JamboJetValidationException('Contact name is required', 400);
        }

        if (!isset($data['name']['first']) || empty($data['name']['first'])) {
            throw new JamboJetValidationException('Contact first name is required', 400);
        }

        if (!isset($data['name']['last']) || empty($data['name']['last'])) {
            throw new JamboJetValidationException('Contact last name is required', 400);
        }
    }

    /**
     * Validate patch data (generic)
     */
    private function validatePatchData(array $data): void
    {
        if (empty($data)) {
            throw new JamboJetValidationException('Patch data cannot be empty', 400);
        }
    }

    /**
     * Validate passenger data
     */
    private function validatePassengerData(array $data): void
    {
        if (!isset($data['name'])) {
            throw new JamboJetValidationException('Passenger name is required', 400);
        }

        if (!isset($data['name']['first']) || empty($data['name']['first'])) {
            throw new JamboJetValidationException('Passenger first name is required', 400);
        }

        if (!isset($data['name']['last']) || empty($data['name']['last'])) {
            throw new JamboJetValidationException('Passenger last name is required', 400);
        }

        if (!isset($data['passengerTypeCode'])) {
            throw new JamboJetValidationException('Passenger type code is required', 400);
        }
    }

    /**
     * Validate resell SSR request
     */
    private function validateResellSsrRequest(array $request): void
    {
        if (!isset($request['journeyKey'])) {
            throw new JamboJetValidationException('Journey key is required for SSR resell', 400);
        }
    }

    /**
     * Validate SSR availability request
     */
    private function validateSsrAvailabilityRequest(array $request): void
    {
        // Optional - can be empty for full availability
        if (isset($request['journeyKeys']) && !is_array($request['journeyKeys'])) {
            throw new JamboJetValidationException('Journey keys must be an array', 400);
        }
    }

    /**
     * Validate channel type
     */
    private function validateChannelType(?int $channelType): void
    {
        if ($channelType !== null && ($channelType < 0 || $channelType > 5)) {
            throw new JamboJetValidationException('Channel type must be between 0-5', 400);
        }
    }

    /**
     * Validate system code
     */
    private function validateSystemCodeValue(string $systemCode): void
    {
        if (empty(trim($systemCode))) {
            throw new JamboJetValidationException('System code cannot be empty', 400);
        }

        if (strlen($systemCode) > 10) {
            throw new JamboJetValidationException('System code cannot exceed 10 characters', 400);
        }
    }

    /**
     * Validate leg key
     */
    private function validateLegKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Leg key is required');
        }

        // Leg key format: CARRIER~FLIGHTNUM~ORIGIN~DATETIME~DEST
        // Example: JM~101~MBA~20251201~1200~NBO
        if (!preg_match('/^[A-Z0-9]+~/', $key)) {
            throw new JamboJetValidationException(
                'Invalid leg key format. Expected format: CARRIER~FLIGHTNUM~ORIGIN~...'
            );
        }
    }

    /**
     * Validate segment key
     */
    private function validateSegmentKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Segment key is required');
        }

        // Segment keys are similar to leg keys
        if (!preg_match('/^[A-Z0-9]+~/', $key)) {
            throw new JamboJetValidationException(
                'Invalid segment key format'
            );
        }
    }

    /**
     * Validate passenger key
     */
    private function validatePassengerKey(string $key): void
    {
        if (empty(trim($key))) {
            throw new JamboJetValidationException('Passenger key is required');
        }
    }

    /**
     * Validate bag tag print data
     */
    private function validateBagTagPrintData(array $data): void
    {
        // Bag tag print requires printer information
        if (!isset($data['printerName']) || empty(trim($data['printerName']))) {
            throw new JamboJetValidationException('Printer name is required');
        }

        if (isset($data['numberOfCopies'])) {
            if (!is_int($data['numberOfCopies']) || $data['numberOfCopies'] < 1 || $data['numberOfCopies'] > 10) {
                throw new JamboJetValidationException(
                    'Number of copies must be between 1 and 10'
                );
            }
        }
    }

    /**
     * Get booking history event name (helper method)
     * 
     * @param int $event Event type code
     * @return string Event name
     */
    private function getBookingHistoryEventName(int $event): string
    {
        $eventNames = [
            0 => 'BookingCancelled',
            1 => 'BookingCreated',
            2 => 'BookingModified',
            3 => 'BookingCommitted',
            4 => 'SeatAssigned',
            5 => 'SeatUnassigned',
            6 => 'FlightChanged',
            7 => 'FlightAdded',
            8 => 'FlightRemoved',
            13 => 'PaymentReceived',
            14 => 'PaymentVoided',
            22 => 'BaggageAdded',
            23 => 'BaggageRemoved',
            24 => 'BaggageCheckedIn',
            25 => 'BaggageTagPrinted',
            26 => 'PassengerBoarded',
            27 => 'PassengerUnboarded',
            28 => 'RefundProcessed',
            29 => 'RefundVoided',
            34 => 'ScheduleChanged',
            61 => 'BaggageWeightChanged',
            62 => 'BaggagePriorityChanged',
            66 => 'HoldDateExtended',
            67 => 'HoldDateReduced',
            68 => 'HoldDateRemoved',
            // ... 82 total event types
        ];

        return $eventNames[$event] ?? "Event_{$event}";
    }


    /**
     * Validate add baggage request
     * 
     * @param array $data Add baggage request
     * @throws JamboJetValidationException
     */
    private function validateAddBaggageRequest(array $data): void
    {
        $this->validateRequired($data, [
            'recordLocator',
            'journeyKey',
            'passengerKey',
            'baggageInformation'
        ]);

        // Validate record locator
        $this->validateStringLengths(
            ['recordLocator' => $data['recordLocator']],
            ['recordLocator' => ['max' => 12]]
        );

        // Validate baggage information
        $bagInfo = $data['baggageInformation'];

        if (!isset($bagInfo['type'])) {
            throw new JamboJetValidationException(
                'Baggage type is required',
                400
            );
        }

        // Validate weight if present
        if (isset($bagInfo['weight'])) {
            if ($bagInfo['weight'] <= 0) {
                throw new JamboJetValidationException(
                    'Baggage weight must be positive',
                    400
                );
            }

            // Validate weight type (1=Kg, 2=Lbs)
            if (isset($bagInfo['weightType'])) {
                if (!in_array($bagInfo['weightType'], [1, 2])) {
                    throw new JamboJetValidationException(
                        'Weight type must be 1 (Kg) or 2 (Lbs)',
                        400
                    );
                }

                // Check max weight based on type
                $maxWeight = $bagInfo['weightType'] === 1 ? 50 : 110;
                if ($bagInfo['weight'] > $maxWeight) {
                    throw new JamboJetValidationException(
                        "Weight exceeds maximum of {$maxWeight}",
                        400
                    );
                }
            }
        }

        // Validate OS tag if manual tag
        if (isset($bagInfo['manualBagTag']) && $bagInfo['manualBagTag']) {
            if (empty($bagInfo['osTag'])) {
                throw new JamboJetValidationException(
                    'OS tag is required for manual bag tag',
                    400
                );
            }
        }
    }

    /**
     * Validate inventory queue request
     * 
     * @param array $data Inventory queue request
     * @throws JamboJetValidationException
     */
    private function validateInventoryQueueRequest(array $data): void
    {
        $this->validateRequired($data, [
            'legKey',
            'bookingQueueCodes',
            'comments'
        ]);

        // Validate booking queue codes
        if (!is_array($data['bookingQueueCodes']) || empty($data['bookingQueueCodes'])) {
            throw new JamboJetValidationException(
                'At least one booking queue code is required',
                400
            );
        }

        foreach ($data['bookingQueueCodes'] as $code) {
            if (empty($code)) {
                throw new JamboJetValidationException(
                    'Booking queue codes cannot be empty',
                    400
                );
            }
        }

        // Validate comments
        if (!is_array($data['comments']) || empty($data['comments'])) {
            throw new JamboJetValidationException(
                'At least one comment is required',
                400
            );
        }

        foreach ($data['comments'] as $index => $comment) {
            $this->validateComment($comment, $index);
        }
    }

    /**
     * Validate promotion request
     * 
     * @param array $data Promotion request
     * @throws JamboJetValidationException
     */
    private function validatePromotionRequest(array $data): void
    {
        $this->validateRequired($data, ['promotionCode']);

        // Max 8 characters
        if (strlen($data['promotionCode']) > 8) {
            throw new JamboJetValidationException(
                'Promotion code cannot exceed 8 characters',
                400
            );
        }

        // Alphanumeric only
        if (!preg_match('/^[A-Z0-9]+$/', $data['promotionCode'])) {
            throw new JamboJetValidationException(
                'Promotion code must be alphanumeric',
                400
            );
        }

        // Validate organization code if present
        if (isset($data['organizationCode'])) {
            if (strlen($data['organizationCode']) > 10) {
                throw new JamboJetValidationException(
                    'Organization code cannot exceed 10 characters',
                    400
                );
            }
        }
    }

    /**
     * Validate remove baggage by leg key request
     * 
     * @param array $data Remove by leg key request
     * @throws JamboJetValidationException
     */
    private function validateRemoveBaggageByLegKeyRequest(array $data): void
    {
        $this->validateRequired($data, [
            'recordLocator',
            'passengerKey',
            'legKey',
            'baggageKey'
        ]);

        // Validate record locator
        $this->validateStringLengths(
            ['recordLocator' => $data['recordLocator']],
            ['recordLocator' => ['max' => 12]]
        );
    }

    /**
     * Validate baggage check-in request
     * 
     * @param array $data Check-in request
     * @throws JamboJetValidationException
     */
    private function validateBaggageCheckInRequest(array $data): void
    {
        $this->validateRequired($data, [
            'recordLocator',
            'passengerKey',
            'baggageKey'
        ]);

        // Either journeyKey or segmentKey required (not both)
        if (!isset($data['journeyKey']) && !isset($data['segmentKey'])) {
            throw new JamboJetValidationException(
                'Either journeyKey or segmentKey is required',
                400
            );
        }

        // Validate record locator
        $this->validateStringLengths(
            ['recordLocator' => $data['recordLocator']],
            ['recordLocator' => ['max' => 12]]
        );

        // Validate weight if changing
        if (isset($data['weight'])) {
            if ($data['weight'] <= 0) {
                throw new JamboJetValidationException(
                    'Baggage weight must be positive',
                    400
                );
            }
        }

        // If changing type code, weight must also change
        if (isset($data['baggageTypeCode']) && !isset($data['weight'])) {
            throw new JamboJetValidationException(
                'Weight must be provided when changing baggage type code',
                400
            );
        }

        // Validate baggage count
        if (isset($data['baggageCount'])) {
            if ($data['baggageCount'] < 1) {
                throw new JamboJetValidationException(
                    'Baggage count must be at least 1',
                    400
                );
            }
        }
    }

    /**
     * Get channel type name
     * 
     * @param int $type Channel type code
     * @return string Channel name
     */
    public function getChannelTypeName(int $type): string
    {
        $types = [
            0 => 'Direct',
            1 => 'Web',
            2 => 'API',
            3 => 'Digital API',
            4 => 'Digital Web',
            5 => 'NDC'
        ];

        return $types[$type] ?? 'Unknown';
    }

    /**
     * Build queue request
     * 
     * @param string $code Queue code
     * @param string $note Queue note
     * @param array $options Additional options
     * @return array Queue request
     */
    public function buildQueueRequest(string $code, string $note, array $options = []): array
    {
        return [
            'code' => $code,
            'subCode' => $options['subCode'] ?? null,
            'note' => $note,
            'passengerKey' => $options['passengerKey'] ?? null,
            'watchListKey' => $options['watchListKey'] ?? null
        ];
    }

    /**
     * Build add baggage request
     * 
     * @param array $params Baggage parameters
     * @return array Formatted add baggage request
     */
    public function buildAddBaggageRequest(array $params): array
    {
        return [
            'recordLocator' => $params['recordLocator'],
            'journeyKey' => $params['journeyKey'],
            'passengerKey' => $params['passengerKey'],
            'iataIdentifier' => $params['iataIdentifier'] ?? null,
            'nonStandard' => $params['nonStandard'] ?? false,
            'allowBaggageOnNonHosted' => $params['allowBaggageOnNonHosted'] ?? true,
            'baggageInformation' => [
                'type' => $params['baggageType'],
                'manualBagTag' => $params['manualBagTag'] ?? false,
                'osTag' => $params['osTag'] ?? null,
                'weight' => $params['weight'] ?? null,
                'weightType' => $params['weightType'] ?? 1 // 1=Kg, 2=Lbs
            ]
        ];
    }

    /**
     * Build baggage check-in request
     * 
     * @param array $params Check-in parameters
     * @return array Formatted check-in request
     */
    public function buildBaggageCheckInRequest(array $params): array
    {
        $request = [
            'recordLocator' => $params['recordLocator'],
            'passengerKey' => $params['passengerKey'],
            'baggageKey' => $params['baggageKey'],
            'allowBaggageOnNonHosted' => $params['allowBaggageOnNonHosted'] ?? true,
            'processAsIatci' => $params['processAsIatci'] ?? false
        ];

        // Either journey or segment key (journey takes precedence)
        if (isset($params['journeyKey'])) {
            $request['journeyKey'] = $params['journeyKey'];
            $request['segmentKey'] = null;
        } elseif (isset($params['segmentKey'])) {
            $request['segmentKey'] = $params['segmentKey'];
            $request['journeyKey'] = null;
        }

        // Optional fields
        if (isset($params['weight'])) {
            $request['weight'] = $params['weight'];
        }

        if (isset($params['baggageCount'])) {
            $request['baggageCount'] = $params['baggageCount'];
        }

        if (isset($params['baggageTypeCode'])) {
            $request['baggageTypeCode'] = $params['baggageTypeCode'];
        }

        return $request;
    }

    /**
     * Get baggage status name
     * 
     * @param int $status Baggage status code
     * @return string Status name
     */
    public function getBaggageStatusName(int $status): string
    {
        $statuses = [
            0 => 'Added',
            1 => 'AddedPrinted',
            2 => 'CheckedIn',
            3 => 'Removed'
        ];

        return $statuses[$status] ?? 'Unknown';
    }

    /**
     * Get weight type name
     * 
     * @param int $type Weight type code
     * @return string Weight type name
     */
    public function getWeightTypeName(int $type): string
    {
        return $type === 1 ? 'Kilograms' : 'Pounds';
    }

    /**
     * Validate SSR by keys request
     * 
     * @param array $data SSR by keys request
     * @throws JamboJetValidationException
     */
    private function validateSsrByKeysRequest(array $data): void
    {
        $this->validateRequired($data, ['keys']);

        if (!is_array($data['keys']) || empty($data['keys'])) {
            throw new JamboJetValidationException(
                'At least one SSR key is required',
                400
            );
        }

        foreach ($data['keys'] as $index => $key) {
            if (!isset($key['ssrKey']) || empty($key['ssrKey'])) {
                throw new JamboJetValidationException(
                    "SSR key {$index} is required",
                    400
                );
            }

            // Validate count if present
            if (isset($key['count']) && $key['count'] < 1) {
                throw new JamboJetValidationException(
                    "SSR count {$index} must be at least 1",
                    400
                );
            }

            // Validate note length
            if (isset($key['note']) && strlen($key['note']) > 255) {
                throw new JamboJetValidationException(
                    "SSR note {$index} cannot exceed 255 characters",
                    400
                );
            }

            // Validate passenger number
            if (isset($key['passengerNumber']) && $key['passengerNumber'] < 0) {
                throw new JamboJetValidationException(
                    "SSR passenger number {$index} cannot be negative",
                    400
                );
            }

            // Must have journey, segment, or leg key
            if (!isset($key['journeyKey']) && !isset($key['segmentKey']) && !isset($key['legKey'])) {
                throw new JamboJetValidationException(
                    "SSR {$index}: journeyKey, segmentKey, or legKey is required",
                    400
                );
            }
        }

        // Validate currency code if present
        if (isset($data['currencyCode'])) {
            $this->validateFormats(
                ['currencyCode' => $data['currencyCode']],
                ['currencyCode' => 'currency_code']
            );
        }

        // Validate fee pricing mode (0=Currency, 1=Points)
        if (isset($data['feePricingMode'])) {
            if (!in_array($data['feePricingMode'], [0, 1])) {
                throw new JamboJetValidationException(
                    'Fee pricing mode must be 0 (Currency) or 1 (Points)',
                    400
                );
            }
        }
    }

    /**
     * Validate single SSR request
     * 
     * @param array $data Single SSR request
     * @throws JamboJetValidationException
     */
    private function validateSingleSsrRequest(array $data): void
    {
        // Must have journey, segment, or leg key
        if (!isset($data['journeyKey']) && !isset($data['segmentKey']) && !isset($data['legKey'])) {
            throw new JamboJetValidationException(
                'journeyKey, segmentKey, or legKey is required',
                400
            );
        }

        // Validate count
        if (isset($data['count']) && $data['count'] < 1) {
            throw new JamboJetValidationException(
                'SSR count must be at least 1',
                400
            );
        }

        // Validate note
        if (isset($data['note']) && strlen($data['note']) > 255) {
            throw new JamboJetValidationException(
                'SSR note cannot exceed 255 characters',
                400
            );
        }
    }

    /**
     * Validate manual SSR criteria
     * 
     * @param array $data Manual SSR criteria
     * @throws JamboJetValidationException
     */
    private function validateManualSsrCriteria(array $data): void
    {
        $this->validateRequired($data, [
            'ssrCode',
            'passengerKey'
        ]);

        // Validate SSR code (4 letter code)
        if (!preg_match('/^[A-Z]{4}$/', $data['ssrCode'])) {
            throw new JamboJetValidationException(
                'SSR code must be 4 uppercase letters',
                400
            );
        }

        // Must have journey, segment, or leg key
        if (!isset($data['journeyKey']) && !isset($data['segmentKey']) && !isset($data['legKey'])) {
            throw new JamboJetValidationException(
                'journeyKey, segmentKey, or legKey is required',
                400
            );
        }
    }

    /**
     * Validate manual SSR request
     * 
     * @param array $data Manual SSR request
     * @throws JamboJetValidationException
     */
    private function validateManualSsrRequest(array $data): void
    {
        $this->validateRequired($data, ['ssrsByType']);

        if (!is_array($data['ssrsByType']) || empty($data['ssrsByType'])) {
            throw new JamboJetValidationException(
                'At least one SSR type is required',
                400
            );
        }

        foreach ($data['ssrsByType'] as $index => $ssrType) {
            // Validate type (1=Leg, 2=Segment, 3=Journey)
            if (!isset($ssrType['type'])) {
                throw new JamboJetValidationException(
                    "SSR type {$index}: type is required",
                    400
                );
            }

            if (!in_array($ssrType['type'], [1, 2, 3])) {
                throw new JamboJetValidationException(
                    "SSR type {$index}: type must be 1 (Leg), 2 (Segment), or 3 (Journey)",
                    400
                );
            }

            // Validate market
            if (!isset($ssrType['market'])) {
                throw new JamboJetValidationException(
                    "SSR type {$index}: market is required",
                    400
                );
            }

            // Validate items
            if (!isset($ssrType['items']) || !is_array($ssrType['items']) || empty($ssrType['items'])) {
                throw new JamboJetValidationException(
                    "SSR type {$index}: at least one item is required",
                    400
                );
            }

            foreach ($ssrType['items'] as $itemIndex => $item) {
                if (!isset($item['ssrCode'])) {
                    throw new JamboJetValidationException(
                        "SSR type {$index}, item {$itemIndex}: ssrCode is required",
                        400
                    );
                }

                if (!isset($item['passengerKey'])) {
                    throw new JamboJetValidationException(
                        "SSR type {$index}, item {$itemIndex}: passengerKey is required",
                        400
                    );
                }

                // Validate SSR code format
                if (!preg_match('/^[A-Z]{4}$/', $item['ssrCode'])) {
                    throw new JamboJetValidationException(
                        "SSR type {$index}, item {$itemIndex}: ssrCode must be 4 uppercase letters",
                        400
                    );
                }
            }
        }
    }


    /**
     * Build complete contact object from parts
     * Helper method to construct properly formatted contact
     * 
     * @param array $params Contact parameters
     * @return array Formatted contact object
     */
    public function buildContactObject(array $params): array
    {
        $contact = [];

        // Contact type code (required for creation)
        if (isset($params['contactTypeCode'])) {
            $contact['contactTypeCode'] = $params['contactTypeCode'];
        }

        // Name
        if (isset($params['name'])) {
            $contact['name'] = [
                'first' => $params['name']['first'] ?? null,
                'middle' => $params['name']['middle'] ?? null,
                'last' => $params['name']['last'] ?? null,
                'title' => $params['name']['title'] ?? null,
                'suffix' => $params['name']['suffix'] ?? null
            ];
        }

        // Email
        if (isset($params['emailAddress'])) {
            $contact['emailAddress'] = $params['emailAddress'];
        }

        // Phone numbers
        if (isset($params['phoneNumbers']) && is_array($params['phoneNumbers'])) {
            $contact['phoneNumbers'] = $params['phoneNumbers'];
        }

        // Address
        if (isset($params['address'])) {
            $contact['address'] = [
                'lineOne' => $params['address']['lineOne'] ?? null,
                'lineTwo' => $params['address']['lineTwo'] ?? null,
                'lineThree' => $params['address']['lineThree'] ?? null,
                'city' => $params['address']['city'] ?? null,
                'postalCode' => $params['address']['postalCode'] ?? null,
                'countryCode' => $params['address']['countryCode'] ?? null,
                'provinceState' => $params['address']['provinceState'] ?? null
            ];
        }

        // Customer number
        if (isset($params['customerNumber'])) {
            $contact['customerNumber'] = $params['customerNumber'];
        }

        // Source organization
        if (isset($params['sourceOrganization'])) {
            $contact['sourceOrganization'] = $params['sourceOrganization'];
        }

        // Distribution option
        if (isset($params['distributionOption'])) {
            $contact['distributionOption'] = $params['distributionOption'];
        }

        // Notification preference
        if (isset($params['notificationPreference'])) {
            $contact['notificationPreference'] = $params['notificationPreference'];
        }

        // Company name
        if (isset($params['companyName'])) {
            $contact['companyName'] = $params['companyName'];
        }

        // Culture code
        if (isset($params['cultureCode'])) {
            $contact['cultureCode'] = $params['cultureCode'];
        }

        return $contact;
    }

    /**
     * Build SSR by keys request
     * 
     * @param array $ssrKeys Array of SSR keys to add
     * @param array $options Optional settings
     * @return array Formatted SSR request
     */
    public function buildSsrByKeysRequest(array $ssrKeys, array $options = []): array
    {
        return [
            'keys' => $ssrKeys,
            'forceWaveOnSell' => $options['forceWaveOnSell'] ?? false,
            'currencyCode' => $options['currencyCode'] ?? null,
            'feePricingMode' => $options['feePricingMode'] ?? 0 // 0=Currency
        ];
    }

    /**
     * Build single SSR key entry
     * 
     * @param string $ssrKey SSR key
     * @param string $scopeKey Journey/Segment/Leg key
     * @param string $scopeType Type: 'journey', 'segment', or 'leg'
     * @param array $options Additional options
     * @return array SSR key entry
     */
    public function buildSsrKeyEntry(
        string $ssrKey,
        string $scopeKey,
        string $scopeType = 'journey',
        array $options = []
    ): array {
        $entry = [
            'ssrKey' => $ssrKey,
            'count' => $options['count'] ?? 1,
            'note' => $options['note'] ?? null,
            'passengerNumber' => $options['passengerNumber'] ?? 0,
            'unitDesignator' => $options['unitDesignator'] ?? null // For seat SSRs
        ];

        // Add scope key based on type
        switch ($scopeType) {
            case 'journey':
                $entry['journeyKey'] = $scopeKey;
                break;
            case 'segment':
                $entry['segmentKey'] = $scopeKey;
                break;
            case 'leg':
                $entry['legKey'] = $scopeKey;
                break;
        }

        return $entry;
    }

    /**
     * Get SSR duration type name
     * 
     * @param int $type SSR duration type
     * @return string Type name
     */
    public function getSsrDurationTypeName(int $type): string
    {
        $types = [
            1 => 'Leg',
            2 => 'Segment',
            3 => 'Journey'
        ];

        return $types[$type] ?? 'Unknown';
    }

    /**
     * Get resell unit SSR option name
     * 
     * @param int $option Resell option
     * @return string Option name
     */
    public function getResellUnitSsrOptionName(int $option): string
    {
        $options = [
            0 => 'Do Not Resell',
            1 => 'Resell and Auto Assign Seats',
            2 => 'Resell Without Auto Assign'
        ];

        return $options[$option] ?? 'Unknown';
    }

    /**
     * Build phone number object
     * 
     * @param int $type Phone type (0-4)
     * @param string $number Phone number
     * @return array Phone number object
     */
    public function buildPhoneNumber(int $type, string $number): array
    {
        return [
            'type' => $type,
            'number' => $number
        ];
    }

    /**
     * Get contact type name from code
     * Helper for display purposes
     * 
     * @param string $code Contact type code
     * @return string Contact type name
     */
    public function getContactTypeName(string $code): string
    {
        $types = [
            'P' => 'Primary',
            'S' => 'Secondary',
            'B' => 'Business',
            'E' => 'Emergency',
            'A' => 'Alternate',
            'T' => 'Travel Agency',
            'C' => 'Corporate'
        ];

        return $types[strtoupper($code)] ?? 'Unknown';
    }

    /**
     * Get phone number type name
     * Helper for display purposes
     * 
     * @param int $type Phone number type
     * @return string Phone type name
     */
    public function getPhoneTypeName(int $type): string
    {
        $types = [
            0 => 'Other',
            1 => 'Home',
            2 => 'Work',
            3 => 'Mobile',
            4 => 'Fax'
        ];

        return $types[$type] ?? 'Unknown';
    }

    /**
     * Validate contact type code
     * 
     * @param string $code Contact type code
     * @throws JamboJetValidationException
     */
    private function validateContactTypeCode(string $code): void
    {
        if (strlen($code) !== 1) {
            throw new JamboJetValidationException(
                'Contact type code must be a single character',
                400
            );
        }

        // Must be alphabetic character
        if (!ctype_alpha($code)) {
            throw new JamboJetValidationException(
                'Contact type code must be an alphabetic character',
                400
            );
        }
    }

    /**
     * Validate phone number type
     * 
     * @param int $type Phone number type
     * @throws JamboJetValidationException
     */
    private function validatePhoneNumberType(int $type): void
    {
        // 0=Other, 1=Home, 2=Work, 3=Mobile, 4=Fax
        if ($type < 0 || $type > 4) {
            throw new JamboJetValidationException(
                'Phone number type must be 0-4 (Other, Home, Work, Mobile, Fax)',
                400
            );
        }
    }


    /**
     * Validate commit request
     * 
     * @param array $data Commit request data
     * @throws JamboJetValidationException
     */
    private function validateCommitRequest(array $data): void
    {
        // Validate receivedBy (max 100 chars)
        if (isset($data['receivedBy'])) {
            $this->validateStringLengths(
                ['receivedBy' => $data['receivedBy']],
                ['receivedBy' => ['max' => 100]]
            );
        }

        // Validate hold booking if present
        if (isset($data['holdBooking'])) {
            if (!isset($data['holdBooking']['expiration'])) {
                throw new JamboJetValidationException(
                    'Hold expiration date is required',
                    400
                );
            }

            $this->validateFormats(
                ['expiration' => $data['holdBooking']['expiration']],
                ['expiration' => 'datetime']
            );
        }

        // Validate comments if present
        if (isset($data['comments']) && is_array($data['comments'])) {
            foreach ($data['comments'] as $index => $comment) {
                $this->validateComment($comment, $index);
            }
        }
    }

    /**
     * Validate comment structure
     * 
     * @param array $comment Comment data
     * @param int $index Comment index for error messages
     * @throws JamboJetValidationException
     */
    private function validateComment(array $comment, int $index): void
    {
        // Validate comment type (0-7)
        if (!isset($comment['type'])) {
            throw new JamboJetValidationException(
                "Comment {$index}: type is required",
                400
            );
        }

        if (!is_int($comment['type']) || $comment['type'] < 0 || $comment['type'] > 7) {
            throw new JamboJetValidationException(
                "Comment {$index}: type must be 0-7 (General, Profile, Special, Invoice, Booking, Archive, Notification, Other)",
                400
            );
        }

        // Validate comment text (max 4000 chars)
        if (!isset($comment['text']) || empty(trim($comment['text']))) {
            throw new JamboJetValidationException(
                "Comment {$index}: text is required",
                400
            );
        }

        if (strlen($comment['text']) > 4000) {
            throw new JamboJetValidationException(
                "Comment {$index}: text cannot exceed 4000 characters",
                400
            );
        }
    }

    /**
     * Validate divide request
     * 
     * @param array $data Divide request data
     * @throws JamboJetValidationException
     */
    private function validateDivideRequest(array $data): void
    {
        // Add validation rules based on DivideRequestv2 schema
        $this->validateRequired($data, ['passengers']);

        if (!is_array($data['passengers']) || empty($data['passengers'])) {
            throw new JamboJetValidationException(
                'At least one passenger is required for divide',
                400
            );
        }

        // Validate passenger keys
        foreach ($data['passengers'] as $index => $passengerKey) {
            if (empty($passengerKey)) {
                throw new JamboJetValidationException(
                    "Passenger key {$index} cannot be empty",
                    400
                );
            }
        }
    }

    /**
     * Validate contact request
     * 
     * @param array $data Contact data
     * @throws JamboJetValidationException
     */
    private function validateContactRequest(array $data): void
    {
        // Validate name if present
        if (isset($data['name'])) {
            $nameFields = ['first', 'middle', 'last', 'title', 'suffix'];
            $nameLengths = [
                'first' => ['max' => 32],
                'middle' => ['max' => 32],
                'last' => ['max' => 32],
                'title' => ['max' => 10],
                'suffix' => ['max' => 10]
            ];

            foreach ($nameFields as $field) {
                if (isset($data['name'][$field])) {
                    $this->validateStringLengths(
                        [$field => $data['name'][$field]],
                        [$field => $nameLengths[$field]]
                    );
                }
            }
        }

        // Validate email address
        if (isset($data['emailAddress'])) {
            if (strlen($data['emailAddress']) > 266) {
                throw new JamboJetValidationException(
                    'Email address cannot exceed 266 characters',
                    400
                );
            }

            if (!filter_var($data['emailAddress'], FILTER_VALIDATE_EMAIL)) {
                throw new JamboJetValidationException(
                    'Invalid email address format',
                    400
                );
            }
        }

        // Validate phone numbers if present
        if (isset($data['phoneNumbers']) && is_array($data['phoneNumbers'])) {
            foreach ($data['phoneNumbers'] as $index => $phone) {
                $this->validatePhoneNumber($phone, $index);
            }
        }

        // Validate address if present
        if (isset($data['address'])) {
            $this->validateAddress($data['address']);
        }

        // Validate customer number (max 20 chars)
        if (isset($data['customerNumber'])) {
            $this->validateStringLengths(
                ['customerNumber' => $data['customerNumber']],
                ['customerNumber' => ['max' => 20]]
            );
        }
    }

    /**
     * Validate phone number
     * 
     * @param array $phone Phone number data
     * @param int $index Index for error messages
     * @throws JamboJetValidationException
     */
    private function validatePhoneNumber(array $phone, int $index): void
    {
        if (!isset($phone['type'])) {
            throw new JamboJetValidationException(
                "Phone number {$index}: type is required",
                400
            );
        }

        // Type: 0=Other, 1=Home, 2=Work, 3=Mobile, 4=Fax
        if (!is_int($phone['type']) || $phone['type'] < 0 || $phone['type'] > 4) {
            throw new JamboJetValidationException(
                "Phone number {$index}: type must be 0-4 (Other, Home, Work, Mobile, Fax)",
                400
            );
        }

        if (!isset($phone['number']) || empty(trim($phone['number']))) {
            throw new JamboJetValidationException(
                "Phone number {$index}: number is required",
                400
            );
        }
    }

    /**
     * Validate address
     * 
     * @param array $address Address data
     * @throws JamboJetValidationException
     */
    private function validateAddress(array $address): void
    {
        $addressLengths = [
            'lineOne' => ['max' => 100],
            'lineTwo' => ['max' => 100],
            'lineThree' => ['max' => 100],
            'city' => ['max' => 50],
            'postalCode' => ['max' => 20],
            'provinceState' => ['max' => 50]
        ];

        foreach ($addressLengths as $field => $limits) {
            if (isset($address[$field])) {
                $this->validateStringLengths(
                    [$field => $address[$field]],
                    [$field => $limits]
                );
            }
        }

        // Validate country code (2-3 chars)
        if (isset($address['countryCode'])) {
            $this->validateFormats(
                ['countryCode' => $address['countryCode']],
                ['countryCode' => 'country_code']
            );
        }
    }

    /**
     * Validate booking search criteria
     * 
     * @param array $criteria Search criteria
     * @throws JamboJetValidationException
     */
    private function validateBookingSearchCriteria(array $criteria): void
    {
        // At least one search criterion required
        $validFields = [
            'RecordLocator',
            'EmailAddress',
            'Origin',
            'FirstName',
            'LastName',
            'CustomerNumber',
            'DepartureDate'
        ];

        $hasValidField = false;
        foreach ($validFields as $field) {
            if (isset($criteria[$field]) && !empty($criteria[$field])) {
                $hasValidField = true;
                break;
            }
        }

        if (!$hasValidField) {
            throw new JamboJetValidationException(
                'At least one search criterion is required',
                400
            );
        }

        // Validate record locator length
        if (isset($criteria['RecordLocator'])) {
            $this->validateStringLengths(
                ['RecordLocator' => $criteria['RecordLocator']],
                ['RecordLocator' => ['max' => 12]]
            );
        }

        // Validate email
        if (isset($criteria['EmailAddress'])) {
            if (strlen($criteria['EmailAddress']) > 266) {
                throw new JamboJetValidationException(
                    'Email address cannot exceed 266 characters',
                    400
                );
            }
        }

        // Validate origin station code
        if (isset($criteria['Origin'])) {
            $this->validateFormats(
                ['Origin' => $criteria['Origin']],
                ['Origin' => 'airport_code']
            );
        }

        // Validate departure date format
        if (isset($criteria['DepartureDate'])) {
            $this->validateFormats(
                ['DepartureDate' => $criteria['DepartureDate']],
                ['DepartureDate' => 'datetime']
            );
        }
    }



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
            $this->validatePassengerData($passenger);
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