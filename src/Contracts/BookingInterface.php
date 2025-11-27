<?php

namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

interface BookingInterface
{
    // ==================== CORE BOOKING OPERATIONS ====================

    public function create(array $bookingData): array;
    public function update(string $recordLocator, array $updateData): array;
    public function getByRecordLocator(string $recordLocator): array;
    public function cancel(string $recordLocator, array $cancellationData = []): array;
    public function commit(string $recordLocator): array;
    public function getHistory(string $recordLocator): array;

    // ==================== STATE MANAGEMENT ====================

    public function getCurrentBooking(): array;
    public function getCommitStatus(): array;


    // ==================== SEARCH & QUERY ====================

    public function searchBookings(array $criteria): array;
    public function getQuote(array $quoteRequest): array;
    public function findByNameAndRecord(string $recordLocator, string $lastName, array $options = []): array;
    public function findByEmail(string $email, array $options = []): array;
    public function findByPhone(string $phoneNumber, array $options = []): array;


    // ==================== COMMUNICATION ====================

    public function sendNotification(string $recordLocator, array $notificationData): array;
    public function sendEmail(string $recordLocator, array $emailData): array;
    public function addComments(string $recordLocator, array $comments): array;

    // ==================== COMMENTS (STATEFUL) ====================

    public function getComments(): array;
    public function deleteComment(string $commentKey): array;

    // ==================== CONTACTS (STATEFUL) ====================

    public function getContacts(): array;
    public function createContact(array $contactData): array;
    public function getContact(string $contactTypeCode): array;
    public function updateContact(string $contactTypeCode, array $contactData): array;
    public function patchContact(string $contactTypeCode, array $patchData): array;
    public function deleteContact(string $contactTypeCode): array;

    // ==================== PRIMARY CONTACTS ====================

    public function getPrimaryContact(): array;
    public function createPrimaryContact(array $contactData): array;
    public function updatePrimaryContact(array $contactData): array;
    public function patchPrimaryContact(array $patchData): array;
    public function deletePrimaryContact(): array;

    // ==================== CONTACT PHONE NUMBERS ====================

    public function getContactPhoneNumbers(string $contactTypeCode): array;
    public function addContactPhoneNumber(string $contactTypeCode, int $phoneNumberType, string $number): array;
    public function updateContactPhoneNumber(string $contactTypeCode, int $phoneNumberType, string $number): array;
    public function deleteContactPhoneNumber(string $contactTypeCode, int $phoneNumberType): array;

    // ==================== PASSENGERS (STATEFUL) ====================

    public function getPassengers(): array;
    public function addPassengers(string $recordLocator, array $passengers): array;
    public function getPassenger(string $passengerKey): array;
    public function updatePassenger(string $recordLocator, string $passengerKey, array $passengerData): array;
    public function patchPassenger(string $passengerKey, array $patchData): array;
    public function removePassenger(string $recordLocator, string $passengerKey): array;

    // ==================== PASSENGER FEES ====================

    public function getPassengerFees(string $passengerKey): array;
    public function addPassengerFee(string $passengerKey, array $feeData): array;
    public function deletePassengerFee(string $passengerKey, string $feeKey): array;

    // ==================== PASSENGER INFANTS ====================

    public function addInfant(string $passengerKey, array $infantData): array;
    public function updateInfant(string $passengerKey, array $infantData): array;
    public function removeInfant(string $passengerKey): array;


    // ==================== PASSENGER LOYALTY ====================

    public function addPassengerLoyalty(string $passengerKey, array $loyaltyData): array;
    public function deletePassengerLoyalty(string $passengerKey): array;

    // ==================== PASSENGER TRAVEL DOCUMENTS ====================

    public function getPassengerTravelDocuments(string $passengerKey): array;
    public function addPassengerTravelDocument(string $passengerKey, array $documentData): array;
    public function updatePassengerTravelDocument(string $passengerKey, string $documentKey, array $documentData): array;
    public function deletePassengerTravelDocument(string $passengerKey, string $documentKey): array;

    // ==================== JOURNEYS (STATEFUL) ====================

    public function getJourneys(): array;
    public function getJourney(string $journeyKey): array;
    public function deleteJourney(string $journeyKey): array;
    public function getJourneySegments(string $journeyKey): array;
    public function getSegment(string $journeyKey, string $segmentKey): array;

    // ==================== LEGS ====================

    public function getLeg(string $legKey): array;

    // ==================== FEES (STATEFUL) ====================

    public function getFees(): array;
    public function addFee(array $feeData): array;
    public function deleteFee(string $feeKey): array;
    public function waiveFees(array $waiveRequest): array;

    // ==================== SSRS (STATEFUL) ====================

    public function getSsrs(): array;
    public function addSsrs(array $ssrRequest): array;
    public function getSsr(string $ssrKey): array;
    public function updateSsrNote(string $ssrKey, string $note): array;
    public function deleteSsr(string $ssrKey): array;
    public function addSsrByKey(string $ssrKey, array $request): array;
    public function getSsrAvailability(array $availabilityRequest = []): array;
    public function resellSsrBundles(array $resellRequest): array;
    public function deleteSsrManual(array $ssrPassengerKey): array;
    public function addSsrsManual(array $ssrRequest): array;
    public function resellSsrs(array $resellRequest): array;

    // ==================== HISTORY (STATEFUL) ====================

    public function getItinerarySentHistory(array $params = []): array;
    public function getMessageHistory(): array;
    public function getNotificationHistory(array $params = []): array;
    public function getFlightMoveHistory(array $params = []): array;
    public function getHoldDateChangeHistory(array $params = []): array;
    public function getSeatAssignmentHistory(array $params = []): array;
    public function getSegmentChangeHistory(array $params = []): array;

    // ==================== VERIFIED TRAVEL DOCUMENTS ====================

    public function addVerifiedTravelDocument(string $segmentKey, string $passengerKey, array $documentData): array;

    // ==================== HOLD MANAGEMENT ====================

    public function getAvailableHoldDate(): array;

    // ==================== VALIDATION ====================

    public function validateBooking(): array;

    // ==================== E-TICKETS ====================

    public function validateETicketing(): array;

    // ==================== GROUP NAME ====================

    public function updateGroupName(array $groupNameRequest): array;
    public function deleteGroupName(): array;

    // ==================== LOYALTY (BOOKING LEVEL) ====================

    public function addBookingLoyalty(array $loyaltyData): array;
    public function deleteBookingLoyalty(): array;

    // ==================== QUEUES ====================

    public function dequeueBooking(string $bookingQueueKey, array $dequeueRequest): array;
    public function dequeuesBooking(string $bookingQueueKey, array $dequeueRequest): array;

    // ==================== SALES CHANNEL ====================

    public function updateSalesChannel(?int $channelType): array;

    // ==================== SYSTEM CODE ====================

    public function updateSystemCode(string $systemCode): array;
    public function deleteSystemCode(): array;

    // ==================== INDIA GST ====================

    public function getIndiaGstRequirement(): array;

    // ==================== ITINERARY ====================

    public function printItinerary(array $printRequest): array;

    // ==================== BAGGAGE ====================

    public function removeBaggage(array $removeRequest): array;


    public function sendBookingEmail(string $recordLocator, array $emailData): array;
    public function addBookingComments(string $recordLocator, array $comments): array;
    public function queueBookingsByLeg(array $queueRequest): array;
    public function dequeueBookingByKey(string $bookingQueueKey, array $dequeueRequest): array;
    public function getBookingQueueItems(string $queueCode, array $filters = []): array;

    // BOARDING OPERATIONS
    public function boardPassengerByLeg(string $recordLocator, string $legKey, string $passengerKey): array;
    public function boardAllPassengersByLeg(string $legKey): array;
    public function unboardPassenger(string $recordLocator, string $legKey, string $passengerKey): array;
    public function unboardAllPassengers(string $legKey): array;
    public function getBoardingStatus(string $recordLocator, string $legKey): array;

    // BAGGAGE OPERATIONS  
    public function addBaggage(array $baggageData): array;
    public function updateBaggage(array $baggageData): array;
    public function getBaggageDetails(string $recordLocator, string $legKey, string $passengerKey): array;
    public function getAllBaggage(string $recordLocator): array;
    public function getBaggageByLeg(string $recordLocator, string $legKey): array;

    // BOOKING MODIFICATIONS
    public function mergeBookings(array $mergeRequest): array;
    public function movePassenger(string $recordLocator, array $moveRequest): array;

    // BOOKING LOCKS
    public function lockBooking(string $recordLocator): array;
    public function unlockBooking(string $recordLocator): array;
    public function getBookingLockStatus(string $recordLocator): array;

    // BOOKING NOTES & REMARKS
    public function addBookingNote(array $noteData): array;
    public function getBookingNotes(): array;
    public function updateBookingNote(string $noteKey, array $noteData): array;
    public function deleteBookingNote(string $noteKey): array;

    // PRICING & QUOTES
    public function reprice(string $recordLocator): array;
    public function getBookingPriceBreakdown(string $recordLocator): array;
    public function applyPromotion(string $recordLocator, string $promotionCode): array;
    public function removePromotion(string $recordLocator, string $promotionCode): array;

    // SPECIAL REQUESTS
    public function addSpecialRequest(array $requestData): array;
    public function getSpecialRequests(): array;
    public function updateSpecialRequest(string $requestKey, array $requestData): array;
    public function deleteSpecialRequest(string $requestKey): array;

    // BOOKING VERSIONS & COMPARISON
    public function getBookingVersion(string $recordLocator, int $version): array;
    public function compareBookingVersions(string $recordLocator, int $version1, int $version2): array;
    public function getBookingChanges(string $recordLocator, ?\DateTime $since = null): array;

    // JOURNEY OPERATIONS (EXTENDED)
    public function deleteAllJourneys(): array;
    public function deleteJourneyWithOptions(string $journeyKey, array $cancelOptions): array;
    public function waiveJourneyPenaltyFee(string $journeyKey): array;

    // JOURNEY BUNDLES
    public function sellJourneyBundle(string $journeyKey, array $bundleRequest): array;
    public function deleteJourneyBundles(string $journeyKey, array $passengerKeys): array;

    // JOURNEY PASSENGER OPERATIONS
    public function getPassengerAddressRequirements(string $journeyKey, string $passengerKey): array;
    public function getPassengerAddressRequirementsAll(string $passengerKey): array;
    public function getJourneyTravelDocumentRequirements(string $journeyKey): array;
    public function getAllTravelDocumentRequirements(): array;

    // JOURNEY BAGGAGE OPERATIONS
    public function getPassengerBaggageByJourney(string $journeyKey, string $passengerKey): array;
    public function addPassengerBaggage(string $journeyKey, string $passengerKey, array $baggageRequest): array;
    public function getPassengerBag(string $journeyKey, string $passengerKey, string $baggageKey): array;
    public function deletePassengerBag(string $journeyKey, string $passengerKey, string $baggageKey): array;
    public function updatePassengerBag(string $journeyKey, string $passengerKey, string $baggageKey, array $updateRequest): array;
    public function checkinPassengerBag(string $journeyKey, string $passengerKey, string $baggageKey, array $checkinRequest): array;
    public function updatePassengerBaggageGroup(string $journeyKey, string $passengerKey, array $groupRequest): array;
    public function addManualBaggage(string $journeyKey, string $passengerKey, array $manualBagRequest): array;

    // JOURNEY INFANT OPERATIONS
    public function addInfantToJourney(string $journeyKey, string $passengerKey, array $infantData): array;
    public function removeInfantFromJourney(string $journeyKey, string $passengerKey): array;

    // JOURNEY FEES
    public function waivePassengerJourneyFees(string $journeyKey, string $passengerKey, string $feeType): array;

    // SEGMENT OPERATIONS
    public function deleteSegment(string $segmentKey): array;
    public function modifySegmentStatus(string $journeyKey, string $segmentKey, int $newStatus): array;
    public function getPassengerSegment(string $segmentKey, string $passengerKey): array;

    // SEGMENT INFANT OPERATIONS
    public function addInfantToSegment(string $segmentKey, string $passengerKey, array $infantData): array;
    public function removeInfantFromSegment(string $segmentKey, string $passengerKey): array;

    // SEGMENT TICKETS
    public function addTicket(string $segmentKey, string $passengerKey, array $ticketRequest): array;
    public function updateTicket(string $segmentKey, string $passengerKey, array $ticketRequest): array;

    // CLASS OF SERVICE
    public function getClassOfServiceAvailability(bool $isUpgrade, array $options = []): array;
    public function modifyClassOfService(string $classModifyKey, array $modifyRequest): array;
    public function resetClassOfService(string $segmentKey, ?bool $overSell = null): array;

    // BOARDING PASSES
    public function getBoardingPassesByJourney(string $journeyKey, array $filterRequest): array;
    public function getBoardingPassesByJourneyM2D(string $journeyKey, array $passengerFilter): array;
    public function getBoardingPassesByJourneyS2D(string $journeyKey, array $passengerFilter): array;
    public function getBoardingPassesBySegment(string $segmentKey, array $passengerFilter): array;

    // CHECK-IN OPERATIONS
    public function checkinByJourney(string $journeyKey, array $checkinRequest): array;
    public function uncheckinByJourney(string $journeyKey, array $uncheckinRequest): array;
    public function getCheckinRequirementsByJourney(string $journeyKey): array;
    public function getCheckinStatusByJourney(string $journeyKey): array;
    public function checkinBySegment(string $segmentKey, array $checkinRequest): array;
    public function uncheckinBySegment(string $segmentKey, array $uncheckinRequest): array;
    public function getCheckinRequirementsBySegment(string $segmentKey): array;
    public function getCheckinStatusBySegment(string $segmentKey): array;

    // SEAT OPERATIONS
    public function getSeatMapsForBooking(array $options = []): array;
    public function getSeatMapsByJourney(string $journeyKey, array $options = []): array;
    public function getSeatMapsBySegment(string $segmentKey, array $options = []): array;
    public function autoAssignSeats(string $primaryPassengerKey, array $autoAssignRequest): array;
    public function autoAssignSeatsByJourney(string $primaryPassengerKey, string $journeyKey, array $autoAssignRequest): array;
    public function autoAssignSeatsBySegment(string $primaryPassengerKey, string $segmentKey, array $autoAssignRequest): array;
    public function getAutoAssignSeatFeeQuoteByJourney(string $primaryPassengerKey, string $journeyKey, array $quoteRequest): array;
    public function getAutoAssignSeatFeeQuoteBySegment(string $primaryPassengerKey, string $segmentKey, array $quoteRequest): array;

    /**
     * Get passenger summary with counts and status
     * GET /api/nsk/v2/booking/passengers/{passengerKey}/summary
     */
    public function getPassengerSummary(string $passengerKey): array;

    /**
     * Add passengers to booking (v2 batch operation)
     * POST /api/nsk/v2/booking/passengers
     */
    public function addPassengersV2(array $passengers): array;

    /**
     * Update passenger (v3 with additional options)
     * PUT /api/nsk/v3/booking/passengers/{passengerKey}
     */
    public function updatePassengerV3(
        string $passengerKey,
        array $passengerData,
        bool $waiveNameChangeFees = false,
        bool $syncGender = true
    ): array;

    /**
     * Remove passenger from booking (v2)
     * DELETE /api/nsk/v2/booking/passengers/{passengerKey}
     */
    public function removePassengerV2(string $passengerKey): array;

    /**
     * Modify passenger type code (ADT, CHD, INF, etc.)
     * POST /api/nsk/v1/booking/passengers/{passengerKey}/typeCode
     */
    public function modifyPassengerTypeCode(string $passengerKey, array $typeCodeRequest): array;

// ==================== PASSENGER ADDRESSES ====================

    /**
     * Get all addresses for a passenger
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/addresses
     */
    public function getPassengerAddresses(string $passengerKey): array;

    /**
     * Add address to passenger
     * POST /api/nsk/v2/booking/passengers/{passengerKey}/addresses
     */
    public function addPassengerAddress(string $passengerKey, array $addressData): array;

    /**
     * Get specific passenger address
     * GET /api/nsk/v2/booking/passengers/{passengerKey}/addresses/{addressKey}
     */
    public function getPassengerAddress(string $passengerKey, string $addressKey): array;

    /**
     * Update passenger address
     * PUT /api/nsk/v3/booking/passengers/{passengerKey}/addresses/{addressKey}
     */
    public function updatePassengerAddress(string $passengerKey, string $addressKey, array $addressData): array;

    /**
     * Patch passenger address (partial update)
     * PATCH /api/nsk/v2/booking/passengers/{passengerKey}/addresses/{addressKey}
     */
    public function patchPassengerAddress(string $passengerKey, string $addressKey, array $patchData): array;

    /**
     * Delete passenger address
     * DELETE /api/nsk/v2/booking/passengers/{passengerKey}/addresses/{addressKey}
     */
    public function deletePassengerAddress(string $passengerKey, string $addressKey): array;

// ==================== PASSENGER TRAVEL DOCUMENTS - EXTENDED ====================

// EXISTING METHODS (keep these):
// public function getPassengerTravelDocuments(string $passengerKey): array;
// public function addPassengerTravelDocument(string $passengerKey, array $documentData): array;
// public function updatePassengerTravelDocument(string $passengerKey, string $documentKey, array $documentData): array;
// public function deletePassengerTravelDocument(string $passengerKey, string $documentKey): array;

    /**
     * Get specific travel document
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/documents/{travelDocumentKey}
     */
    public function getPassengerTravelDocument(string $passengerKey, string $documentKey): array;

    /**
     * Patch travel document (partial update)
     * PATCH /api/nsk/v2/booking/passengers/{passengerKey}/documents/{travelDocumentKey}
     */
    public function patchPassengerTravelDocument(
        string $passengerKey,
        string $documentKey,
        array $patchData,
        bool $syncGender = true
    ): array;

    /**
     * Check passenger documents (ADC - Automated Document Check)
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/documents/check
     */
    public function checkPassengerDocuments(string $passengerKey): array;

// ==================== PASSENGER BAGGAGE ====================

    /**
     * Get all baggage for a specific passenger
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/baggage
     */
    public function getPassengerBaggage(string $passengerKey): array;

    /**
     * Get baggage allowance for booking
     * GET /api/nsk/v1/booking/passengers/baggageAllowance
     */
    public function getBaggageAllowance(): array;

    /**
     * Get baggage allowance by leg
     * GET /api/nsk/v1/booking/passengers/baggageAllowance/{legKey}
     */
    public function getBaggageAllowanceByLeg(string $legKey): array;

    /**
     * Get baggage allowance for specific passenger
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/baggageAllowance
     */
    public function getPassengerBaggageAllowance(string $passengerKey): array;

    /**
     * Get baggage allowance for specific passenger and leg
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/baggageAllowance/{legKey}
     */
    public function getPassengerBaggageAllowanceByLeg(string $passengerKey, string $legKey): array;

    /**
     * Get passenger baggage groups
     * GET /api/nsk/v1/booking/passengers/baggage/group
     */
    public function getPassengerBaggageGroups(): array;


    /**
     * Get all infant travel documents
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/infant/documents
     */
    public function getInfantTravelDocuments(string $passengerKey): array;

    /**
     * Add infant travel document
     * POST /api/nsk/v2/booking/passengers/{passengerKey}/infant/documents
     */
    public function addInfantTravelDocument(
        string $passengerKey,
        array $documentData,
        bool $syncGender = true
    ): array;

    /**
     * Get specific infant travel document
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/infant/documents/{travelDocumentKey}
     */
    public function getInfantTravelDocument(string $passengerKey, string $documentKey): array;

    /**
     * Update infant travel document
     * PUT /api/nsk/v2/booking/passengers/{passengerKey}/infant/documents/{travelDocumentKey}
     */
    public function updateInfantTravelDocument(
        string $passengerKey,
        string $documentKey,
        array $documentData,
        bool $syncGender = true
    ): array;

    /**
     * Patch infant travel document (partial update)
     * PATCH /api/nsk/v2/booking/passengers/{passengerKey}/infant/documents/{travelDocumentKey}
     */
    public function patchInfantTravelDocument(
        string $passengerKey,
        string $documentKey,
        array $patchData,
        bool $syncGender = true
    ): array;

    /**
     * Delete infant travel document
     * DELETE /api/nsk/v2/booking/passengers/{passengerKey}/infant/documents/{travelDocumentKey}
     */
    public function deleteInfantTravelDocument(string $passengerKey, string $documentKey): array;

    /**
     * Check infant documents (ADC)
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/infant/documents/check
     */
    public function checkInfantDocuments(string $passengerKey): array;

    /**
     * Get infant details
     * GET /api/nsk/v2/booking/passengers/{passengerKey}/infant
     */
    public function getInfantDetails(string $passengerKey): array;

    /**
     * Update infant details (v2)
     * PUT /api/nsk/v2/booking/passengers/{passengerKey}/infant
     */
    public function updateInfantDetailsV2(string $passengerKey, array $infantData): array;

    /**
     * Patch infant details (partial update)
     * PATCH /api/nsk/v2/booking/passengers/{passengerKey}/infant
     */
    public function patchInfantDetails(string $passengerKey, array $patchData): array;

    // ==================== PASSENGER LOYALTY - EXTENDED ====================

    // EXISTING METHODS (keep these):
    // public function addPassengerLoyalty(string $passengerKey, array $loyaltyData): array;
    // public function deletePassengerLoyalty(string $passengerKey): array;

    /**
     * Get passenger loyalty program
     * GET /api/nsk/v2/booking/passengers/{passengerKey}/loyaltyProgram
     */
    public function getPassengerLoyalty(string $passengerKey): array;

    /**
     * Update passenger loyalty program
     * PUT /api/nsk/v2/booking/passengers/{passengerKey}/loyaltyProgram
     */
    public function updatePassengerLoyalty(string $passengerKey, array $loyaltyData): array;

    // ==================== PASSENGER FEES - EXTENDED ====================

    // EXISTING METHODS (keep these):
    // public function getPassengerFees(string $passengerKey): array;
    // public function addPassengerFee(string $passengerKey, array $feeData): array;
    // public function deletePassengerFee(string $passengerKey, string $feeKey): array;

    /**
     * Get specific passenger fee
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/fees/{feeKey}
     */
    public function getPassengerFee(string $passengerKey, string $feeKey): array;

    /**
     * Update passenger fee
     * PUT /api/nsk/v1/booking/passengers/{passengerKey}/fees/{feeKey}
     */
    public function updatePassengerFee(string $passengerKey, string $feeKey, array $feeData): array;

    /**
     * Patch passenger fee (partial update)
     * PATCH /api/nsk/v1/booking/passengers/{passengerKey}/fees/{feeKey}
     */
    public function patchPassengerFee(string $passengerKey, string $feeKey, array $patchData): array;

// ==================== PASSENGER PRICE BREAKDOWNS ====================

    /**
     * Get all passenger price breakdowns
     * GET /api/nsk/v1/booking/passengers/breakdown
     */
    public function getPassengerPriceBreakdowns(): array;

    /**
     * Get passenger price breakdown by type (ADT, CHD, INF)
     * GET /api/nsk/v1/booking/passengers/breakdown/byType
     */
    public function getPassengerPriceBreakdownsByType(): array;

// ==================== GROUP BOOKING OPERATIONS ====================

    /**
     * Update group booking passengers (TBA - To Be Assigned)
     * PUT /api/nsk/v1/booking/passengers/groupBooking
     */
    public function updateGroupBookingPassengers(array $groupBookingData): array;


    /**
     * Get all passenger travel notifications
     * GET /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications
     */
    public function getPassengerTravelNotifications(string $passengerAlternateKey): array;

    /**
     * Create passenger travel notification
     * POST /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications
     */
    public function addPassengerTravelNotification(string $passengerAlternateKey, array $notificationData): array;

    /**
     * Get specific passenger travel notification
     * GET /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}
     */
    public function getPassengerTravelNotification(string $passengerAlternateKey, string $notificationKey): array;

    /**
     * Update passenger travel notification
     * PUT /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}
     */
    public function updatePassengerTravelNotification(
        string $passengerAlternateKey,
        string $notificationKey,
        array $notificationData
    ): array;

    /**
     * Delete passenger travel notification
     * DELETE /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}
     */
    public function deletePassengerTravelNotification(string $passengerAlternateKey, string $notificationKey): array;

// ==================== NOTIFICATION EVENTS ====================

    /**
     * Get notification events for travel notification
     * GET /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}/events
     */
    public function getPassengerNotificationEvents(string $passengerAlternateKey, string $notificationKey): array;

    /**
     * Get specific notification event
     * GET /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}/events/{eventType}
     */
    public function getPassengerNotificationEvent(
        string $passengerAlternateKey,
        string $notificationKey,
        int $eventType
    ): array;

    /**
     * Delete notification event
     * DELETE /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}/events/{eventType}
     */
    public function deletePassengerNotificationEvent(
        string $passengerAlternateKey,
        string $notificationKey,
        int $eventType
    ): array;

    // ==================== NOTIFICATION TIMED EVENTS ====================

    /**
     * Get notification timed events
     * GET /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}/timedEvents
     */
    public function getPassengerNotificationTimedEvents(string $passengerAlternateKey, string $notificationKey): array;

    /**
     * Get specific notification timed event
     * GET /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}/timedEvents/{timedEventType}
     */
    public function getPassengerNotificationTimedEvent(
        string $passengerAlternateKey,
        string $notificationKey,
        int $timedEventType
    ): array;

    /**
     * Update notification timed event
     * PUT /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}/timedEvents/{timedEventType}
     */
    public function updatePassengerNotificationTimedEvent(
        string $passengerAlternateKey,
        string $notificationKey,
        int $timedEventType,
        array $eventData
    ): array;

    /**
     * Delete notification timed event
     * DELETE /api/nsk/v1/booking/passengers/{passengerAlternateKey}/travelNotifications/{travelNotificationKey}/timedEvents/{timedEventType}
     */
    public function deletePassengerNotificationTimedEvent(
        string $passengerAlternateKey,
        string $notificationKey,
        int $timedEventType
    ): array;

    // ==================== SSR OPERATIONS (PASSENGER-SPECIFIC) ====================

    /**
     * Get SSR price quote for passenger
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/ssrs/{ssrCode}/price
     */
    public function getPassengerSsrPriceQuote(
        string $passengerKey,
        string $ssrCode,
        ?string $collectedCurrencyCode = null
    ): array;

    // ==================== VOUCHER OPERATIONS (PASSENGER-SPECIFIC) ====================

    /**
     * Get voucher information for passenger
     * GET /api/nsk/v1/booking/passengers/{passengerKey}/voucher
     */
    public function getPassengerVoucherInfo(
        string $passengerKey,
        string $voucherCode,
        bool $overrideRestrictions = false
    ): array;

    // ==================== RECORD LOCATORS (STATEFUL) ====================
    /**
     * Add a third party record locator to the booking
     * POST /api/nsk/v1/booking/recordLocators
     * GraphQL: recordLocatorsAdd
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * @param array $recordLocatorData RecordLocatorCreateRequest with required fields:
     *   - recordCode (string, required): Record code
     *   - systemDomainCode (string, required): System domain code
     *   - owningSystemCode (string, required): Owning system code
     *   - bookingSystemCode (string, optional): Booking system code
     *   - interactionPurpose (string, optional): Interaction purpose
     *   - hostedCarrierCode (string, optional): Hosted carrier code
     * @return array Response
     */
    public function addRecordLocator(array $recordLocatorData): array;

    /**
     * Delete all record locators from the booking
     * DELETE /api/nsk/v1/booking/recordLocators
     * GraphQL: recordLocatorsDelete
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * @return array Response
     */
    public function deleteAllRecordLocators(): array;

    /**
     * Get a specific record locator by key
     * GET /api/nsk/v1/booking/recordLocators/{recordLocatorKey}
     * GraphQL: recordLocator
     * 
     * @param string $recordLocatorKey Record locator key to retrieve
     * @return array RecordLocator data
     */
    public function getRecordLocator(string $recordLocatorKey): array;

    /**
     * Replace record locator data (full update)
     * PUT /api/nsk/v1/booking/recordLocators/{recordLocatorKey}
     * GraphQL: recordLocatorsSet
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * @param string $recordLocatorKey Record locator key to update
     * @param array $recordLocatorData RecordLocatorEditRequest with fields:
     *   - recordCode (string, optional): Record code
     *   - systemDomainCode (string, optional): System domain code
     *   - owningSystemCode (string, optional): Owning system code
     *   - bookingSystemCode (string, optional): Booking system code
     *   - interactionPurpose (string, optional): Interaction purpose
     *   - hostedCarrierCode (string, optional): Hosted carrier code
     * @return array Response
     */
    public function updateRecordLocator(string $recordLocatorKey, array $recordLocatorData): array;

    /**
     * Patch record locator data (partial update)
     * PATCH /api/nsk/v1/booking/recordLocators/{recordLocatorKey}
     * GraphQL: recordLocatorsModify
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * @param string $recordLocatorKey Record locator key to update
     * @param array $patchData Delta changes (DeltaMapperOfRecordLocatorEditRequest)
     * @return array Response
     */
    public function patchRecordLocator(string $recordLocatorKey, array $patchData): array;

    /**
     * Delete a specific record locator
     * DELETE /api/nsk/v1/booking/recordLocators/{recordLocatorKey}
     * GraphQL: recordLocatorDelete
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * @param string $recordLocatorKey Record locator key to delete
     * @return array Response
     */
    public function deleteRecordLocator(string $recordLocatorKey): array;

    // ==================== BOOKING REINSTATE ====================

    /**
     * Reinstate a booking with status of hold canceled
     * PUT /api/nsk/v1/booking/reinstate
     * GraphQL: bookingReinstate
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * @return array Response
     */
    public function reinstateBooking(): array;

    /**
     * Get booking account and collections (Agent only)
     * GET /api/nsk/v1/bookings/{recordLocator}/account
     * GraphQL: bookingsAccount
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Allows agents to view all payment account transactions for a booking
     * For non-agents, use /booking/payments/bookingCredit instead
     * 
     * @param string $recordLocator Record locator
     * @return array Account information including:
     *   - accountBalance (decimal): Current account balance
     *   - collections (array): List of account collections
     *   - transactions (array): Payment transactions
     * @throws JamboJetApiException When not found (404) or other errors
     */
    public function getBookingAccount(string $recordLocator): array;

    /**
     * Create booking account
     * POST /api/nsk/v1/bookings/{recordLocator}/account
     * GraphQL: bookingsAccountAdd
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Creates a new payment account for the specified booking
     * 
     * @param string $recordLocator Record locator
     * @param array $accountData Account creation data
     * @return array Response
     */
    public function createBookingAccount(string $recordLocator, array $accountData): array;

    /**
     * Get account collection transactions with pagination
     * GET /api/nsk/v1/bookings/{recordLocator}/account/collections/{accountCollectionKey}/transactions
     * GraphQL: bookingsAccountTransactions
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Retrieves paginated transaction history for a specific account collection
     * 
     * @param string $recordLocator Record locator
     * @param string $accountCollectionKey Account collection key
     * @param array $params Query parameters:
     *   - StartDate (DateTime, optional): Starting date for search range
     *   - EndDate (DateTime, optional): End date for search range
     *   - SortByNewest (bool, optional): Sort by newest first
     *   - PageSize (int, optional): Records per page (10-5000)
     *   - LastPageKey (string, optional): Pagination cursor for next page
     * @return array PagedTransactionResponse with:
     *   - transactions (array): List of transactions
     *   - lastPageKey (string): Cursor for next page
     *   - hasMore (bool): Whether more results exist
     */
    public function getAccountTransactions(
        string $recordLocator,
        string $accountCollectionKey,
        array $params = []
    ): array;

    // ==================== QUEUE OPERATIONS (STATELESS) ====================

    /**
     * Get booking queue details
     * POST /api/nsk/v1/bookings/{recordLocator}/queues/{queueCode}
     * GraphQL: bookingsQueues
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Retrieves queue information for a specific booking
     * 
     * @param string $recordLocator Record locator
     * @param string $queueCode Queue code to query
     * @param array $queueRequest BookingQueuesRequest with optional filters
     * @return array Queue details including:
     *   - queueCode (string): Queue code
     *   - queueItems (array): Items in the queue
     *   - status (string): Queue status
     */
    public function getBookingQueues(
        string $recordLocator,
        string $queueCode,
        array $queueRequest
    ): array;

    // ==================== STATELESS SEAT OPERATIONS ====================

    /**
     * Add seat assignment (stateless operation)
     * POST /api/nsk/v1/bookings/{recordLocator}/passengers/{passengerKey}/seats/{unitKey}
     * GraphQL: bookingsSeatsAdd
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Stateless seat assignment for external integrations
     * 
     * @param string $recordLocator Record locator
     * @param string $passengerKey Passenger key
     * @param string $unitKey Unit key (seat identifier)
     * @param array $seatRequest AddUnitStatelessConfig with:
     *   - waiveFee (bool, optional): Waive seat fee if permitted
     *   - inventoryControl (int, optional): Inventory control type
     *   - collectedCurrencyCode (string, optional): Collection currency
     * @return array Response
     */
    public function addSeatStateless(
        string $recordLocator,
        string $passengerKey,
        string $unitKey,
        array $seatRequest
    ): array;

    /**
     * Delete seat assignment (stateless operation) - Agent only
     * DELETE /api/nsk/v1/bookings/{recordLocator}/passengers/{passengerKey}/seats/{unitKey}
     * GraphQL: bookingsSeatDelete, bookingsSeatsDelete
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Agent-only operation to remove seat assignments from bookings
     * 
     * @param string $recordLocator Record locator
     * @param string $passengerKey Passenger key
     * @param string $unitKey Unit key (seat identifier)
     * @param array $deleteRequest DeleteUnitStatelessConfig with options
     * @return array Response
     */
    public function deleteSeatStateless(
        string $recordLocator,
        string $passengerKey,
        string $unitKey,
        array $deleteRequest
    ): array;

    // ==================== BOARDING OPERATIONS (DCS) ====================

    /**
     * Unboard passenger by leg
     * DELETE /api/dcs/v1/boarding/{recordLocator}/legs/{legKey}/passengers/{passengerKey}
     * GraphQL: unboardByLeg
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Removes a passenger from boarded status for a specific leg
     * Part of Departure Control System (DCS) operations
     * 
     * @param string $recordLocator Record locator
     * @param string $legKey Leg key
     * @param string $passengerKey Passenger key
     * @return array Response
     */
    public function unboardPassengerByLeg(
        string $recordLocator,
        string $legKey,
        string $passengerKey
    ): array;

    /**
     * Board passenger by segment
     * POST /api/dcs/v2/boarding/{recordLocator}/segments/{segmentKey}/passengers/{passengerKey}
     * GraphQL: boardBySegment
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Boards a passenger on a specific segment
     * WARNING: Do NOT board change-of-gauge flights using this endpoint
     * If attempted, only the first leg will be boarded
     * 
     * @param string $recordLocator Record locator
     * @param string $segmentKey Segment key
     * @param string $passengerKey Passenger key
     * @return array Response
     */
    public function boardPassengerBySegment(
        string $recordLocator,
        string $segmentKey,
        string $passengerKey
    ): array;

    /**
     * Get all record locators for booking in state
     * GET /api/nsk/v1/booking/recordLocators
     * GraphQL: recordLocators
     * 
     * Retrieves all third-party record locators associated with the
     * current booking in session state.
     * 
     * @return array Array of RecordLocator objects with keys:
     *   - recordLocatorKey (string): Unique key for the record locator
     *   - recordCode (string): The actual record locator code
     *   - systemDomainCode (string): System domain code (3 chars)
     *   - owningSystemCode (string): Owning system code
     *   - bookingSystemCode (string): Booking system code
     *   - interactionPurpose (string): Purpose of interaction
     *   - hostedCarrierCode (string): Hosted carrier code
     * @throws JamboJetApiException
     */
    public function getAllRecordLocators(): array;

    /**
     * Get booking by record locator (stateless)
     * GET /api/nsk/v1/bookings/{recordLocator}
     * GraphQL: bookingsByRecordLocator
     * 
     * Retrieves a booking WITHOUT loading it into session state.
     * This is a stateless operation that returns complete booking data
     * including passengers, journeys, payments, and all associated data.
     * 
     * USE CASES:
     * - Quick booking lookups without session management
     * - Read-only booking information retrieval
     * - Reporting and analytics operations
     * - Customer service inquiries
     * 
     * @param string $recordLocator Booking record locator (6-12 chars)
     * @return array Complete Booking object with all data:
     *   - bookingKey (string): Booking key
     *   - recordLocator (string): Record locator
     *   - currencyCode (string): Currency code
     *   - info (object): Booking status, dates, type
     *   - passengers (array): All passengers on booking
     *   - journeys (array): All journey segments
     *   - payments (array): All applied payments
     *   - contacts (array): Contact information
     *   - comments (array): Booking comments
     *   - history (array): Booking history
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function getBookingByRecordLocator(string $recordLocator): array;

    /**
     * Search/find bookings by criteria (stateless)
     * POST /api/nsk/v2/bookings/findBookings
     * GraphQL: findBookings
     * 
     * Advanced booking search with multiple filter criteria.
     * Returns paginated results matching the search parameters.
     * 
     * SEARCH CRITERIA (all optional, combine as needed):
     * - recordLocator (string): Exact record locator match
     * - lastName (string): Passenger last name
     * - firstName (string): Passenger first name
     * - emailAddress (string): Contact email
     * - phoneNumber (string): Contact phone
     * - departureStation (string): Origin station code (3 chars)
     * - arrivalStation (string): Destination station code (3 chars)
     * - departureDate (string): Flight date (ISO 8601)
     * - bookingDateFrom (string): Booking created after date
     * - bookingDateTo (string): Booking created before date
     * - ticketNumber (string): Ticket number
     * - organizationCode (string): Organization code (e.g., travel agency)
     * - organizationGroupCode (string): Organization group code
     * - searchArchive (bool): Include archived bookings
     * 
     * PAGINATION:
     * - pageSize (int): Results per page (default: 20, max: 100)
     * - pageNumber (int): Page to retrieve (starts at 1)
     * 
     * @param array $searchCriteria Search filters and pagination
     * @return array Search results with:
     *   - bookings (array): Array of booking summaries
     *   - totalCount (int): Total matching bookings
     *   - pageNumber (int): Current page
     *   - pageSize (int): Results per page
     *   - totalPages (int): Total available pages
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function findBookings(array $searchCriteria): array;


    /**
     * Add hold to booking
     * POST /api/nsk/v1/bookings/{recordLocator}/hold
     * GraphQL: bookingsHoldAdd
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Places a booking on hold until a specified date/time.
     * Prevents automatic cancellation and allows time for payment.
     * 
     * USE CASES:
     * - Group bookings requiring approval
     * - Corporate bookings awaiting payment authorization
     * - Travel agency bookings pending client confirmation
     * - Special fare bookings requiring additional documentation
     * 
     * HOLD RULES:
     * - Hold date must be in the future
     * - Cannot exceed maximum hold duration (configured in system)
     * - Hold date cannot be after departure date
     * - Booking status must allow holds
     * 
     * @param string $recordLocator Booking record locator
     * @param array $holdData Hold information:
     *   - holdDate (string, required): Hold expiration date (ISO 8601)
     *   - reason (string, optional): Reason for hold
     *   - comment (string, optional): Additional comments
     * @return array Hold confirmation with:
     *   - holdDate (string): Confirmed hold date
     *   - holdStatus (string): Hold status
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function addBookingHold(string $recordLocator, array $holdData): array;

    /**
     * Update booking hold
     * PUT /api/nsk/v1/bookings/{recordLocator}/hold
     * GraphQL: bookingsHoldUpdate
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Updates the hold date for an existing booking hold.
     * 
     * USE CASES:
     * - Extend hold period for pending payments
     * - Adjust hold date based on client requests
     * - Modify hold due to business rules changes
     * 
     * @param string $recordLocator Booking record locator
     * @param array $holdData Updated hold information:
     *   - holdDate (string, required): New hold expiration date (ISO 8601)
     *   - reason (string, optional): Updated reason
     *   - comment (string, optional): Additional comments
     * @return array Updated hold confirmation
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updateBookingHold(string $recordLocator, array $holdData): array;

    /**
     * Remove booking hold
     * DELETE /api/nsk/v1/bookings/{recordLocator}/hold
     * GraphQL: bookingsHoldDelete
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Removes the hold from a booking, making it subject to
     * normal cancellation rules if unpaid.
     * 
     * USE CASES:
     * - Payment received, no longer need hold
     * - Client confirmed, ready to process
     * - Manual hold removal by agent
     * 
     * @param string $recordLocator Booking record locator
     * @return array Removal confirmation
     * @throws JamboJetApiException
     */
    public function removeBookingHold(string $recordLocator): array;

// =================================================================
//  HIGH PRIORITY OPERATIONS - ACCOUNT OPERATIONS
// =================================================================

    /**
     * Update booking account status
     * PUT /api/nsk/v1/bookings/{recordLocator}/account/status
     * GraphQL: bookingsAccountStatusUpdate
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Updates the status of a booking's payment account.
     * 
     * ACCOUNT STATUSES:
     * - 0 = Active: Account is active and can be used
     * - 1 = Suspended: Account temporarily disabled
     * - 2 = Closed: Account permanently closed
     * - 3 = Pending: Account awaiting activation
     * 
     * USE CASES:
     * - Suspend account for fraud investigation
     * - Close account after refund completion
     * - Reactivate suspended account
     * - Set pending for new accounts
     * 
     * @param string $recordLocator Booking record locator
     * @param array $statusData Status update:
     *   - status (int, required): New account status (0-3)
     *   - reason (string, optional): Reason for status change
     *   - effectiveDate (string, optional): When status takes effect
     * @return array Status update confirmation
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function updateBookingAccountStatus(string $recordLocator, array $statusData): array;

    /**
     * Get all booking transactions (v2)
     * GET /api/nsk/v2/bookings/{recordLocator}/account/transactions
     * GraphQL: bookingsAccountAllTransactionsv2
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Retrieves ALL transactions across all account collections for a booking.
     * Version 2 includes enhanced filtering and pagination.
     * 
     * TRANSACTION TYPES:
     * - 0 = Default
     * - 1 = Payment
     * - 2 = Adjustment
     * - 3 = Supplementary
     * - 4 = Transfer
     * - 5 = Spoilage
     * 
     * @param string $recordLocator Booking record locator
     * @param array $params Query parameters:
     *   - startDate (string, optional): Filter from date (ISO 8601)
     *   - endDate (string, optional): Filter to date (ISO 8601)
     *   - transactionType (int, optional): Filter by type (0-5)
     *   - pageSize (int, optional): Results per page (1-100)
     *   - pageKey (string, optional): Pagination cursor
     * @return array Paged transaction response:
     *   - totalCount (int): Total transactions
     *   - lastPageKey (string): Next page cursor
     *   - transactions (array): Transaction list
     * @throws JamboJetApiException
     */
    public function getAllBookingTransactions(string $recordLocator, array $params = []): array;

    /**
     * Get collection transactions (v2)
     * GET /api/nsk/v2/bookings/{recordLocator}/account/collection/{accountCollectionKey}/transactions
     * GraphQL: bookingsAccountTransactionsv2
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Retrieves transactions for a SPECIFIC account collection.
     * Version 2 with enhanced filtering.
     * 
     * @param string $recordLocator Booking record locator
     * @param string $accountCollectionKey Account collection key
     * @param array $params Query parameters (same as getAllBookingTransactions)
     * @return array Paged transaction response
     * @throws JamboJetApiException
     */
    public function getCollectionTransactions(
        string $recordLocator,
        string $accountCollectionKey,
        array $params = []
    ): array;

    /**
     * Add account collection to booking
     * POST /api/nsk/v1/bookings/{recordLocator}/account/collection
     * GraphQL: bookingsAccountCollectionAdd
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Creates a new account collection for the booking.
     * Collections organize transactions by type or purpose.
     * 
     * COLLECTION TYPES:
     * - Payment collections: Group related payments
     * - Refund collections: Track refund transactions
     * - Adjustment collections: Manage corrections
     * 
     * @param string $recordLocator Booking record locator
     * @param array $collectionData Collection details:
     *   - amount (float, required): Initial collection amount
     *   - currencyCode (string, required): Currency code (3 chars)
     *   - note (string, optional): Collection notes
     *   - referenceNumber (string, optional): External reference
     * @return array Created collection with collection key
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function addAccountCollection(string $recordLocator, array $collectionData): array;

// =================================================================
//  HIGH PRIORITY OPERATIONS - PAYMENT OPERATIONS
// =================================================================

    /**
     * Get all payments for booking (stateless)
     * GET /api/nsk/v1/bookings/{recordLocator}/payments
     * 
     * Retrieves all payment records for a booking WITHOUT loading
     * the booking into session state.
     * 
     * PAYMENT INFORMATION INCLUDES:
     * - Payment method and details
     * - Authorization status
     * - Payment amounts and currency
     * - Transaction dates
     * - Payment status (pending, approved, declined, refunded)
     * 
     * USE CASES:
     * - Payment verification
     * - Financial reporting
     * - Customer service inquiries
     * - Reconciliation processes
     * 
     * @param string $recordLocator Booking record locator
     * @return array All payments for booking:
     *   - payments (array): List of payment objects
     *   - totalPaid (float): Total amount paid
     *   - currency (string): Payment currency
     * @throws JamboJetApiException
     */
    public function getBookingPayments(string $recordLocator): array;

    /**
     * Get payment history for booking
     * GET /api/nsk/v1/bookings/{recordLocator}/payments/history
     * 
     * Retrieves complete payment history timeline including
     * all payment events, authorizations, and status changes.
     * 
     * HISTORY INCLUDES:
     * - Payment attempts (successful and failed)
     * - Authorization events
     * - Refund transactions
     * - Payment modifications
     * - Status changes with timestamps
     * - Agent actions and notes
     * 
     * USE CASES:
     * - Payment dispute resolution
     * - Fraud investigation
     * - Audit trails
     * - Customer service escalations
     * 
     * @param string $recordLocator Booking record locator
     * @return array Payment history timeline:
     *   - events (array): Chronological payment events
     *   - totalEvents (int): Number of events
     * @throws JamboJetApiException
     */
    public function getPaymentHistory(string $recordLocator): array;

// =================================================================
//  HIGH PRIORITY OPERATIONS - CANCELLATION
// =================================================================

    /**
     * Cancel booking
     * POST /api/nsk/v1/bookings/{recordLocator}/cancel
     * GraphQL: bookingsCancel
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Cancels a booking and processes any applicable refunds.
     * 
     * CANCELLATION RULES:
     * - Applies cancellation fees per fare rules
     * - Processes refunds to original payment methods
     * - Updates inventory availability
     * - Triggers customer notifications
     * - Creates audit trail
     * 
     * CANCELLATION TYPES:
     * - Full cancellation: All passengers and segments
     * - Partial cancellation: Specific passengers or segments
     * - Void: Within 24 hours (no penalties)
     * 
     * @param string $recordLocator Booking record locator
     * @param array $cancellationData Cancellation details:
     *   - reason (string, required): Cancellation reason
     *   - cancelledBy (string, optional): Person cancelling
     *   - refundToOriginal (bool, optional): Refund to original payment
     *   - waiveFees (bool, optional): Waive cancellation fees (requires permission)
     *   - notifyPassengers (bool, optional): Send cancellation emails
     * @return array Cancellation confirmation:
     *   - cancellationId (string): Unique cancellation ID
     *   - refundAmount (float): Amount to be refunded
     *   - cancellationFees (float): Applied cancellation fees
     *   - status (string): New booking status
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function cancelBooking(string $recordLocator, array $cancellationData): array;


    /**
     * Delete check-in by journey
     * DELETE /api/nsk/v1/bookings/checkin/{recordLocator}/journey/{journeyKey}
     * GraphQL: checkInByJourneyDelete
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Removes check-in for all passengers on a specific journey.
     * This operation checks out all passengers from the journey.
     * 
     * USE CASES:
     * - Journey cancellation after check-in
     * - Flight time changes requiring re-check-in
     * - Passenger requests to undo check-in
     * - Operational disruptions (IROP)
     * 
     * EFFECTS:
     * - Releases seat assignments
     * - Cancels boarding passes
     * - Updates passenger status to not checked-in
     * - Makes inventory available again
     * 
     * @param string $recordLocator Booking record locator
     * @param string $journeyKey Journey key (base64 encoded)
     * @return array Check-out confirmation
     * @throws JamboJetApiException
     */
    public function deleteCheckinByJourney(string $recordLocator, string $journeyKey): array;

    /**
     * Get check-in status by segment
     * GET /api/nsk/v1/bookings/checkin/{recordLocator}/segment/{segmentKey}/status
     * GraphQL: checkInStatusBySegment
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Retrieves check-in status for a specific segment.
     * 
     * CHECK-IN STATUSES:
     * - Not Eligible: Outside check-in window
     * - Eligible: Can check-in now
     * - Checked In: Already checked in
     * - Boarded: Passenger has boarded
     * - Closed: Check-in/boarding closed
     * 
     * STATUS INFORMATION INCLUDES:
     * - Check-in eligibility
     * - Check-in window (open/close times)
     * - Passenger check-in status
     * - Seat assignments
     * - Boarding pass details
     * - Gate information
     * 
     * @param string $recordLocator Booking record locator
     * @param string $segmentKey Segment key (base64 encoded)
     * @return array Check-in status details
     * @throws JamboJetApiException
     */
    public function getCheckinStatusBySegmentRecordLocator(string $recordLocator, string $segmentKey): array;


    /**
     * Split booking
     * POST /api/nsk/v2/bookings/{recordLocator}/split
     * GraphQL: bookingsSplit
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Splits a booking into multiple separate bookings.
     * Creates new booking(s) with selected passengers/segments.
     * 
     * SPLIT TYPES:
     * - Passenger Split: Separate passengers into different bookings
     * - Segment Split: Split by journey/segment
     * - Payment Split: Divide payments between bookings
     * 
     * USE CASES:
     * - Group bookings needing separate records
     * - Different payment methods per passenger
     * - Partial cancellations
     * - Individual passenger modifications
     * 
     * SPLIT RULES:
     * - Original booking remains with selected passengers
     * - New booking(s) created for split passengers
     * - Payments can be auto-divided or manually specified
     * - Contact information preserved
     * - Booking history maintained
     * 
     * @param string $recordLocator Original booking record locator
     * @param array $splitData Split configuration:
     *   - passengerKeys (array, required): Passengers to split out
     *   - autoDividePayments (bool, optional): Auto-divide payments
     *   - paymentTransfers (array, optional): Manual payment distribution
     *   - newEmail (string, optional): Email for new booking
     *   - keepOriginalEmail (bool, optional): Keep email on original
     * @return array Split result with:
     *   - originalBooking (object): Updated original booking
     *   - newBooking (object): Created booking with split passengers
     *   - paymentDistribution (array): How payments were divided
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function splitBooking(string $recordLocator, array $splitData): array;

    /**
     * Divide booking
     * POST /api/nsk/v2/bookings/{recordLocator}/divide
     * GraphQL: bookingsDivide
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Divides a booking with advanced payment transfer options.
     * More flexible than split - allows for complex scenarios.
     * 
     * DIVIDE VS SPLIT:
     * - Divide: Advanced payment controls, CRS record locators
     * - Split: Simpler passenger/segment separation
     * 
     * @param string $recordLocator Original booking record locator
     * @param array $divideData Divide configuration (DivideRequestv2):
     *   - passengerKeys (array, optional): Passengers to divide
     *   - crsRecordLocators (array, optional): CRS record locators
     *   - autoDividePayments (bool): Auto payment division
     *   - bookingPaymentTransfers (array, optional): Payment transfers
     *   - receivedBy (string, optional): User requesting divide
     *   - overrideRestrictions (bool, optional): Override restrictions
     *   - parentEmail (string, optional): Parent booking email
     *   - childEmail (string, optional): Child booking email
     *   - cancelSourceBooking (bool, optional): Cancel source after divide
     * @return array Divide result
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function divideBooking(string $recordLocator, array $divideData): array;

    /**
     * Modify booking
     * POST /api/nsk/v1/bookings/{recordLocator}/modify
     * GraphQL: bookingsModify
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * General booking modification endpoint.
     * Handles various modification types in a single operation.
     * 
     * MODIFICATION TYPES:
     * - Flight changes (dates, times, routes)
     * - Passenger name corrections
     * - Contact information updates
     * - Service modifications
     * - Fare changes
     * 
     * @param string $recordLocator Booking record locator
     * @param array $modificationData Modification details
     * @return array Modification result
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function modifyBooking(string $recordLocator, array $modificationData): array;

    /**
     * Add transaction to account collection
     * POST /api/nsk/v1/bookings/{recordLocator}/account/collection/{accountCollectionKey}/transactions
     * GraphQL: bookingsAccountTransactionAdd
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Creates a new transaction in a specific account collection.
     * 
     * TRANSACTION TYPES:
     * - 1 = Payment: Customer payment
     * - 2 = Adjustment: Corrections/modifications
     * - 3 = Supplementary: Additional charges
     * - 4 = Transfer: Between collections/accounts
     * 
     * @param string $recordLocator Booking record locator
     * @param string $accountCollectionKey Account collection key
     * @param array $transactionData Transaction details:
     *   - amount (float, required): Transaction amount
     *   - type (int, required): Transaction type (1-4)
     *   - note (string, optional): Transaction note
     *   - referenceNumber (string, optional): External reference
     * @return array Created transaction
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function addAccountTransaction(
        string $recordLocator,
        string $accountCollectionKey,
        array $transactionData
    ): array;

// =================================================================
// PHASE 3: MEDIUM PRIORITY OPERATIONS - COMMENTS OPERATIONS
// =================================================================

    /**
     * Get all booking comments
     * GET /api/nsk/v1/bookings/{recordLocator}/comments
     * GraphQL: bookingsComments
     * 
     * Retrieves all comments associated with a booking (stateless).
     * 
     * COMMENT TYPES:
     * - Agent comments (internal notes)
     * - System comments (automated entries)
     * - Customer comments (passenger notes)
     * - Operational comments (special handling)
     * 
     * COMMENT INFORMATION:
     * - Comment text
     * - Comment type/category
     * - Created date and user
     * - Modified date and user
     * - Visibility settings
     * 
     * @param string $recordLocator Booking record locator
     * @return array All booking comments:
     *   - comments (array): List of comment objects
     *   - totalCount (int): Number of comments
     * @throws JamboJetApiException
     */
    public function getBookingComments(string $recordLocator): array;

    /**
     * Add comment to booking
     * POST /api/nsk/v1/bookings/{recordLocator}/comments
     * GraphQL: bookingsCommentsAdd
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Adds a new comment to the booking.
     * 
     * COMMENT CATEGORIES:
     * - General: Standard booking notes
     * - Special Service: SSR related
     * - Payment: Payment related notes
     * - Operational: Operations team notes
     * - Customer Service: CS interactions
     * 
     * @param string $recordLocator Booking record locator
     * @param array $commentData Comment details:
     *   - text (string, required): Comment text (1-1000 chars)
     *   - type (string, optional): Comment type/category
     *   - isInternal (bool, optional): Internal only flag
     * @return array Created comment with comment key
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function addBookingComment(string $recordLocator, array $commentData): array;

    /**
     * Delete booking comment
     * DELETE /api/nsk/v1/bookings/{recordLocator}/comments/{commentKey}
     * GraphQL: bookingsCommentsDelete
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Removes a comment from the booking.
     * 
     * DELETION RULES:
     * - Only creator or authorized users can delete
     * - System comments cannot be deleted
     * - Deletion is logged in history
     * 
     * @param string $recordLocator Booking record locator
     * @param string $commentKey Comment key to delete
     * @return array Deletion confirmation
     * @throws JamboJetApiException
     */
    public function deleteBookingComment(string $recordLocator, string $commentKey): array;


    /**
     * Get booking history
     * GET /api/nsk/v1/bookings/{recordLocator}/history
     * GraphQL: bookingsHistory
     * 
     * Retrieves complete booking history timeline (stateless).
     * 
     * HISTORY CATEGORIES (44 types):
     * - 0 = Unknown, 1 = Baggage, 2 = BagTagPrint, 3 = BoardingPassPrint
     * - 4 = CheckIn, 5 = ClassOfServiceChange, 6 = Comment
     * - 7 = ConfirmedSegment, 8 = ContactChange, 9 = Converted
     * - 10 = CouponOverride, 11 = DividePnr, 12 = FareOverride
     * - 13 = Fee, 14 = FlightMove, 15 = GroupNameChange, 16 = Hold
     * - 17 = ItinerarySent, 18 = ManualPayment, 19 = MoveBackPnr
     * - 20 = NameChange, 21 = NameRemove, 22 = Payment, 23 = Pds
     * - 24 = Promotion, 25 = QueuePlaceRemove, 26 = RecordLocator
     * - 27 = ScheduleCancellation, 28 = ScheduleCodeShareChange
     * - 29 = ScheduleFlightDesignatorChange, 30 = ScheduleTimeChange
     * - 31 = SeatAssignment, 32 = SegmentChange, 33 = Reprice
     * - 34 = SsrChange, 35 = StandByChange, 36 = TicketNumber
     * - 37 = VerifiedTravelDocument, 38 = Apps, 39 = InhibitedOverride
     * - 40 = CustomIdChange, 41 = HoldDateChange
     * - 42 = AddedTravelDocument, 43 = ChangedTravelDocument
     * 
     * @param string $recordLocator Booking record locator
     * @return array Complete history timeline:
     *   - history (array): Chronological history events
     *   - totalEvents (int): Number of events
     * @throws JamboJetApiException
     */
    public function getBookingHistory(string $recordLocator): array;

    /**
     * Get booking history by category
     * GET /api/nsk/v1/bookings/{recordLocator}/history/{category}
     * GraphQL: bookingsHistoryByCategory
     * 
     * Retrieves filtered booking history for a specific category.
     * 
     * @param string $recordLocator Booking record locator
     * @param int $category History category (0-43, see getBookingHistory)
     * @return array Filtered history events
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function getBookingHistoryByCategory(string $recordLocator, int $category): array;

    /**
     * Get ancillary availability
     * GET /api/nsk/v1/bookings/{recordLocator}/ancillary/availability
     * GraphQL: bookingsAncillaryAvailability
     * 
     * Retrieves available ancillary services for the booking.
     * 
     * ANCILLARY TYPES:
     * - Baggage (extra bags, overweight, oversized)
     * - Meals (pre-order meals, special meals)
     * - Seats (seat selection, premium seats)
     * - Lounge access
     * - Fast track security
     * - Travel insurance
     * - Pet transport
     * - Unaccompanied minor service
     * 
     * AVAILABILITY INFO:
     * - Service codes and descriptions
     * - Pricing per passenger/segment
     * - Availability status
     * - Restrictions and rules
     * 
     * @param string $recordLocator Booking record locator
     * @return array Available ancillary services
     * @throws JamboJetApiException
     */
    public function getAncillaryAvailability(string $recordLocator): array;

    /**
     * Add ancillary service
     * POST /api/nsk/v1/bookings/{recordLocator}/ancillary
     * GraphQL: bookingsAncillaryAdd
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Adds an ancillary service to the booking.
     * 
     * @param string $recordLocator Booking record locator
     * @param array $ancillaryData Ancillary service details:
     *   - serviceCode (string, required): Ancillary service code
     *   - passengerKeys (array, required): Passengers to apply to
     *   - segmentKeys (array, optional): Specific segments
     *   - quantity (int, optional): Quantity (for countable items)
     * @return array Added ancillary with key
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function addAncillaryService(string $recordLocator, array $ancillaryData): array;

    /**
     * Merge multiple bookings
     * POST /api/nsk/v1/bookings/{recordLocator}/merge
     * GraphQL: bookingsMerge
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Merges multiple bookings into a single consolidated booking.
     * Combines passengers, journeys, and payments from source bookings.
     * 
     * MERGE SCENARIOS:
     * - Family bookings made separately
     * - Group bookings created individually
     * - Consolidate multiple PNRs
     * - Agent corrections/organization
     * 
     * MERGE PROCESS:
     * - Target booking receives merged data
     * - Source bookings are cancelled
     * - Payments are transferred
     * - Contacts are consolidated
     * - History is preserved
     * 
     * MERGE RULES:
     * - Bookings must have compatible journeys
     * - All bookings must be on same airline
     * - Payment status must allow merge
     * - No conflicting seat assignments
     * - Within same booking class rules
     * 
     * USE CASES:
     * - Combine family member bookings
     * - Consolidate group reservations
     * - Merge duplicate bookings
     * - Organize corporate travel
     * 
     * @param string $recordLocator Target booking (will receive merged data)
     * @param array $mergeData Merge configuration:
     *   - sourceRecordLocators (array, required): Bookings to merge in
     *   - consolidatePayments (bool, optional): Combine payments
     *   - primaryContact (string, optional): Primary contact email
     *   - mergeReason (string, optional): Reason for merge
     *   - preserveComments (bool, optional): Keep all comments
     * @return array Merge result with:
     *   - mergedBooking (object): Combined booking details
     *   - cancelledBookings (array): Source bookings cancelled
     *   - paymentSummary (object): Payment consolidation
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function mergeBookingsByRecordLocator(string $recordLocator, array $mergeData): array;

    /**
     * Remove ancillary service from booking
     * DELETE /api/nsk/v1/bookings/{recordLocator}/ancillary/{ancillaryKey}
     * GraphQL: bookingsAncillaryDelete
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Removes a previously added ancillary service from the booking.
     * 
     * REMOVAL SCENARIOS:
     * - Customer changes mind
     * - Wrong service added
     * - Duplicate service removal
     * - Service no longer needed
     * 
     * REMOVAL EFFECTS:
     * - Service removed from booking
     * - Charges reversed if not paid
     * - Refund processed if paid
     * - Inventory released
     * 
     * REMOVAL RULES:
     * - Service must be removable (per airline policy)
     * - Cannot remove after certain timeframes
     * - Some services non-refundable
     * - May incur cancellation fees
     * 
     * @param string $recordLocator Booking record locator
     * @param string $ancillaryKey Ancillary service key to remove
     * @return array Removal confirmation with:
     *   - removedService (object): Removed service details
     *   - refundAmount (float): Refund amount if applicable
     *   - cancellationFee (float): Fee charged if applicable
     * @throws JamboJetApiException
     */
    public function removeAncillaryService(string $recordLocator, string $ancillaryKey): array;

    /**
     * Get available history categories
     * GET /api/nsk/v1/bookings/{recordLocator}/history/categories
     * 
     * Retrieves list of all available history categories for filtering.
     * Useful for building dynamic filtering interfaces.
     * 
     * CATEGORY INFORMATION:
     * - Category ID (0-43)
     * - Category name
     * - Description
     * - Event count for this booking
     * 
     * CATEGORIES RETURNED (44 types):
     * 0-Unknown, 1-Baggage, 2-BagTagPrint, 3-BoardingPassPrint,
     * 4-CheckIn, 5-ClassOfServiceChange, 6-Comment, 7-ConfirmedSegment,
     * 8-ContactChange, 9-Converted, 10-CouponOverride, 11-DividePnr,
     * 12-FareOverride, 13-Fee, 14-FlightMove, 15-GroupNameChange,
     * 16-Hold, 17-ItinerarySent, 18-ManualPayment, 19-MoveBackPnr,
     * 20-NameChange, 21-NameRemove, 22-Payment, 23-Pds, 24-Promotion,
     * 25-QueuePlaceRemove, 26-RecordLocator, 27-ScheduleCancellation,
     * 28-ScheduleCodeShareChange, 29-ScheduleFlightDesignatorChange,
     * 30-ScheduleTimeChange, 31-SeatAssignment, 32-SegmentChange,
     * 33-Reprice, 34-SsrChange, 35-StandByChange, 36-TicketNumber,
     * 37-VerifiedTravelDocument, 38-Apps, 39-InhibitedOverride,
     * 40-CustomIdChange, 41-HoldDateChange, 42-AddedTravelDocument,
     * 43-ChangedTravelDocument
     * 
     * @param string $recordLocator Booking record locator
     * @return array Available categories with:
     *   - categories (array): List of category objects
     *   - totalCategories (int): Number of categories
     * @throws JamboJetApiException
     */
    public function getHistoryCategories(string $recordLocator): array;

    /**
     * Add transaction to account collection (v2)
     * POST /api/nsk/v2/bookings/{recordLocator}/account/collection/{accountCollectionKey}/transactions
     * GraphQL: bookingsAccountTransactionAddv2
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Version 2 of transaction creation with enhanced features.
     * 
     * V2 ENHANCEMENTS:
     * - Extended validation rules
     * - Additional transaction metadata
     * - Enhanced error responses
     * - Improved audit logging
     * 
     * NOTE: This endpoint may duplicate functionality with v1.
     * Check AccountService for existing implementation.
     * 
     * @param string $recordLocator Booking record locator
     * @param string $accountCollectionKey Account collection key
     * @param array $transactionData Transaction details (v2 format):
     *   - amount (float, required): Transaction amount
     *   - type (int, required): Transaction type (1-4)
     *   - note (string, optional): Transaction note
     *   - referenceNumber (string, optional): External reference
     *   - metadata (array, optional): Additional v2 metadata
     * @return array Created transaction
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function addAccountTransactionV2(
        string $recordLocator,
        string $accountCollectionKey,
        array $transactionData
    ): array;

    /**
     * Check if account transaction endpoint exists in AccountService
     * 
     * Helper method to verify if transaction endpoints are already
     * implemented in AccountService to avoid duplication.
     * 
     * VERIFICATION POINTS:
     * - Check AccountService for matching methods
     * - Verify endpoint paths
     * - Compare functionality
     * - Document duplicates
     * 
     * @return array Verification results:
     *   - v1Exists (bool): v1 endpoint in AccountService
     *   - v2Exists (bool): v2 endpoint in AccountService
     *   - recommendation (string): Implementation guidance
     */
    public function verifyAccountTransactionEndpoints(): array;


    /**
     * Search bookings by record locator (v2 with pagination)
     * GET /api/nsk/v2/bookings/searchByRecordLocator
     * GraphQL: searchByRecordLocatorv2
     * 
     * Enhanced version with filtering and pagination support.
     * 
     * FILTERS:
     * - pageSize (int, 10-5000): Results per page
     * - lastIndex (int): Last booking index for pagination
     * - sourceOrganization (string, max 10): Organization code
     * - organizationGroupCode (string, max 3): Organization group code
     * - searchArchive (bool): Include archived bookings
     * 
     * @param string $recordLocator Record locator to search (6-12 chars)
     * @param array $filters Optional search filters
     * @return array Paginated search results
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByRecordLocator(string $recordLocator, array $filters = []): array;

    /**
     * Search bookings by date range
     * GET /api/nsk/v2/bookings/searchByDate
     * GraphQL: searchByDatev2
     * 
     * Find bookings created within a specific date range.
     * 
     * @param string $startDateUtc Start date (ISO 8601 format)
     * @param string $endDateUtc End date (ISO 8601 format)
     * @param array $filters Optional filters (see searchByRecordLocator)
     * @return array Search results with booking summaries
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByDate(string $startDateUtc, string $endDateUtc, array $filters = []): array;

    /**
     * Search bookings by person key
     * GET /api/nsk/v1/bookings/searchByPerson
     * GraphQL: searchByPerson
     * 
     * Retrieves bookings for a specific person based on date range.
     * Person key obtained from GET /api/nsk/v2/persons endpoint.
     * 
     * OPTIONS:
     * - startDate (string): Start date for search range
     * - endDate (string): End date for search range  
     * - pageSize (int): Results per page
     * - lastIndex (int): Pagination cursor
     * - flightNumber (string): Filter by flight number
     * - departureDate (string): Flight departure date
     * - destination (string, 3 chars): Destination airport code
     * - origin (string, 3 chars): Origin airport code
     * - sourceOrganization (string): Organization code
     * - organizationGroupCode (string): Organization group code
     * 
     * @param string $personKey Unique person key (max 100 chars)
     * @param array $options Search options
     * @return array Booking list for person
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByPerson(string $personKey, array $options = []): array;

    /**
     * Search bookings by agency/organization
     * GET /api/nsk/v2/bookings/searchByAgency
     * GraphQL: searchByAgencyv2
     * 
     * Find bookings created by a specific travel agency or organization.
     * 
     * SEARCH DATA:
     * - firstName (string, max 32): Passenger first name
     * - lastName (string, max 32): Passenger last name
     * - phoneticSearch (bool): Use phonetic matching for last name
     * - filters (array): Standard search filters
     * 
     * @param string $organizationCode Agency/organization code (max 10 chars)
     * @param array $searchData Search parameters and filters
     * @return array Bookings for organization
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByAgency(string $organizationCode, array $searchData = []): array;

    /**
     * Search bookings by contact information
     * GET /api/nsk/v2/bookings/searchByContact
     * GraphQL: searchByContactv2
     * 
     * Multi-field contact search supporting various combinations.
     * 
     * CONTACT DATA (all optional, combine as needed):
     * - firstName (string, max 32): First name
     * - lastName (string, max 32): Last name
     * - recordLocator (string, max 12): Record locator
     * - phoneNumber (string, max 20): Phone number
     * - emailAddress (string, max 266): Email address
     * - sourceOrganization (string, max 10): Organization code
     * - organizationGroupCode (string, max 3): Organization group code
     * - searchArchive (bool): Include archived bookings
     * 
     * @param array $contactData Contact search criteria
     * @return array Matching bookings
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByContact(array $contactData): array;

    /**
     * Search bookings by third party record locator
     * GET /api/nsk/v3/bookings/searchByThirdPartyRecordLocator
     * GraphQL: searchByThirdPartyRecordLocatorv3
     * 
     * Find bookings using external system record locators (GDS, partner systems).
     * 
     * SEARCH PARAMS:
     * - systemCode (string, max 3, required): External system code
     * - agentId (int): Agent identifier
     * - organizationCode (string, max 10): Organization code
     * - recordLocator (string, max 12): Third party record locator
     * - pageSize (int, 10-5000): Results per page
     * - lastIndex (int): Pagination cursor
     * - sourceOrganization (string): Organization filter
     * - organizationGroupCode (string): Organization group code
     * - searchArchive (bool): Include archived bookings
     * 
     * @param array $searchParams Third party search parameters
     * @return array Matching bookings
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByThirdPartyRecordLocator(array $searchParams): array;

    /**
     * Search bookings by reference number (Rail customers)
     * GET /api/nsk/v2/bookings/searchByReferenceNumber
     * GraphQL: searchByReferenceNumberv2
     * 
     * Used by New Skies Rail customers for ticketing and check-in.
     * 
     * SEARCH PARAMS:
     * - agentId (int): Agent identifier
     * - organizationCode (string, max 10): Organization code
     * - pageSize (int): Results per page
     * - lastIndex (int): Pagination cursor
     * - sourceOrganization (string): Organization filter
     * - organizationGroupCode (string): Organization group code
     * - searchArchive (bool): Include archived bookings
     * 
     * @param int $referenceNumber Rail reference number for ticketing/check-in
     * @param array $searchParams Additional search parameters
     * @return array Matching bookings
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByReferenceNumber(int $referenceNumber, array $searchParams = []): array;

    /**
     * Search bookings by agent code
     * GET /api/nsk/v2/bookings/searchByAgentCode
     * GraphQL: searchByAgentCodev2
     * 
     * Find bookings created by a specific agent.
     * 
     * SEARCH DATA:
     * - domainCode (string, max 5): Domain code
     * - firstName (string, max 32): Passenger first name
     * - lastName (string, max 32): Passenger last name
     * - phoneticSearch (bool): Use phonetic search for last name
     * - filters (array): Standard search filters
     * 
     * @param string $agentCode Agent code/name that created booking (max 64 chars)
     * @param array $searchData Additional search criteria
     * @return array Agent's bookings
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByAgentCode(string $agentCode, array $searchData = []): array;

    /**
     * Search bookings by external payment
     * GET /api/nsk/v1/bookings/searchByExternalPayment
     * GraphQL: searchByExternalPayment
     * 
     * CONCURRENCY WARNING: Do NOT call concurrently with same session token
     * 
     * Find bookings that used a specific external payment account.
     * Only external payments should be used for searching.
     * 
     * SEARCH PARAMS:
     * - recordLocator (string): Booking record locator
     * - paymentKey (string): Specific payment key
     * - pageSize (int, 10-5000): Results per page
     * - lastIndex (int): Pagination cursor
     * 
     * @param array $searchParams External payment search parameters
     * @return array Bookings using the payment account
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByExternalPayment(array $searchParams): array;

    /**
     * Search bookings by credit card (POST as GET)
     * POST /api/nsk/v1/bookings/searchByCreditCard
     * GraphQL: searchByCreditCardv2
     * 
     * This endpoint behaves like GET but uses POST for security (card data).
     * 
     * CREDIT CARD SEARCH REQUEST:
     * - creditCardNumber (string, required): Full or partial CC number
     * - expiryDate (string, optional): Card expiry date (MMYY format)
     * - cardholderName (string, optional): Name on card
     * - filters (array, optional): Standard search filters
     * 
     * SECURITY NOTE: Sensitive payment data - handle with PCI compliance.
     * 
     * @param array $creditCardSearch Credit card search request
     * @return array Bookings using the credit card
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByCreditCard(array $creditCardSearch): array;

    /**
     * Search bookings by customer number
     * GET /api/nsk/v2/bookings/searchByCustomerNumber
     * GraphQL: searchByCustomerNumberv2
     * 
     * Find bookings by contact's customer loyalty/membership number.
     * 
     * SEARCH PARAMS:
     * - agentId (int): Agent identifier
     * - organizationCode (string, max 10): Organization code
     * - pageSize (int): Results per page
     * - lastIndex (int): Pagination cursor
     * 
     * @param string $customerNumber Customer number (1-20 chars)
     * @param array $searchParams Additional search parameters
     * @return array Customer's bookings
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByCustomerNumber(string $customerNumber, array $searchParams = []): array;

    /**
     * Search bookings by last name
     * GET /api/nsk/v2/bookings/searchByLastName
     * GraphQL: searchByLastNamev2
     * 
     * Search for bookings by passenger or contact last name.
     * 
     * SEARCH DATA:
     * - firstName (string, max 32): Passenger first name (optional)
     * - phoneticSearch (bool): Use phonetic matching for last name
     * - filters (array): Standard search filters
     * 
     * @param string $lastName Passenger last name (max 32 chars)
     * @param array $searchData Additional search criteria
     * @return array Matching bookings
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByLastName(string $lastName, array $searchData = []): array;

    /**
     * Search bookings by phone number
     * GET /api/nsk/v2/bookings/searchByPhone
     * GraphQL: searchByPhonev2
     * 
     * Find bookings by passenger or contact phone number.
     * 
     * SEARCH PARAMS:
     * - agentId (int): Agent identifier
     * - organizationCode (string, max 10): Organization code
     * - pageSize (int): Results per page
     * - lastIndex (int): Pagination cursor
     * 
     * @param string $phoneNumber Phone number (max 20 chars)
     * @param array $searchParams Additional search parameters
     * @return array Bookings with matching phone number
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByPhone(string $phoneNumber, array $searchParams = []): array;

    /**
     * Search bookings by email address
     * GET /api/nsk/v2/bookings/searchByEmail
     * GraphQL: searchByEmailv2
     * 
     * Find bookings by contact email address.
     * 
     * SEARCH PARAMS:
     * - agentId (int): Agent identifier
     * - organizationCode (string, max 10): Organization code
     * - pageSize (int): Results per page
     * - lastIndex (int): Pagination cursor
     * 
     * @param string $emailAddress Email address (max 266 chars)
     * @param array $searchParams Additional search parameters
     * @return array Bookings with matching email
     * @throws JamboJetApiException
     * @throws JamboJetValidationException
     */
    public function searchByEmail(string $emailAddress, array $searchParams = []): array;
}
