<?php

namespace SantosDave\JamboJet\Contracts;

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


    // ANCILLARY OPERATIONS
    public function getAncillaryAvailability(string $recordLocator): array;
    public function addAncillaryService(string $recordLocator, array $ancillaryData): array;
    public function removeAncillaryService(string $recordLocator, string $ancillaryKey): array;

    // BOOKING MODIFICATIONS
    public function splitBooking(string $recordLocator, array $splitRequest): array;
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
}
