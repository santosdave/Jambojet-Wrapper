<?php

namespace SantosDave\JamboJet\Contracts;

interface TripInterface
{
    // ==================== BOOKING CREATION & PASSENGERS ====================

    /**
     * Create new booking with journeys, contacts, passengers, SSRs
     * POST /api/nsk/v5/trip
     */
    public function createBooking(array $bookingData): array;

    /**
     * Create booking with passengers only
     * POST /api/nsk/v1/trip/passengers
     */
    public function createBookingWithPassengers(array $passengers): array;

    /**
     * Create passive segment booking
     * POST /api/nsk/v1/trip/passiveSegments
     */
    public function createPassiveSegment(array $passiveSegmentData): array;

    /**
     * Sell journeys and create/update booking
     * POST /api/nsk/v4/trip/sell
     */
    public function sellJourneys(array $sellRequest): array;

    // ==================== TRIP INFORMATION ====================

    /**
     * Search trip information (max 100 journeys)
     * POST /api/nsk/v2/trip/info
     */
    public function searchTripInfo(array $query): array;

    /**
     * Simple trip info search with minimal criteria
     * GET /api/nsk/v2/trip/info/simple
     */
    public function getTripInfoSimple(array $params): array;

    /**
     * Search inventory legs by criteria
     * POST /api/nsk/v2/trip/info/legs
     */
    public function searchLegs(array $query): array;

    /**
     * Simple leg search
     * GET /api/nsk/v2/trip/info/legs/simple
     */
    public function getLegsSimple(array $params): array;

    /**
     * Get cabin details and leg cross references
     * GET /api/nsk/v1/trip/info/{legKey}/details
     */
    public function getLegDetails(string $legKey): array;

    // ==================== TRIP STATUS MANAGEMENT ====================

    /**
     * Get trip status for leg
     * GET /api/nsk/v2/trip/info/{legKey}/status
     */
    public function getLegStatus(string $legKey): array;

    /**
     * Update trip status (FLIFO - Flight Following)
     * PATCH /api/nsk/v1/trip/info/{legKey}/status
     */
    public function updateLegStatus(string $legKey, array $statusData): array;

    /**
     * Cancel leg status
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/cancel
     */
    public function cancelLeg(string $legKey, array $cancelData = []): array;

    /**
     * Close leg status
     * PUT /api/nsk/v2/trip/info/{legKey}/status/operationDetails/status/closeLeg
     */
    public function closeLeg(string $legKey, array $closeData): array;

    /**
     * Set leg to closed pending
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/closePendingLeg
     */
    public function setLegClosePending(string $legKey, array $pendingData = []): array;

    /**
     * Set leg to mishap (secure passenger data)
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/mishap
     */
    public function setLegMishap(string $legKey, array $mishapData = []): array;

    /**
     * Open leg status
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/openLeg
     */
    public function openLeg(string $legKey, array $openData): array;

    /**
     * Restore leg to normal
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/restore
     */
    public function restoreLeg(string $legKey, array $restoreData = []): array;

    /**
     * Restore from mishap
     * POST /api/nsk/v1/trip/info/status/operationDetails/status/mishapRestore
     */
    public function restoreLegFromMishap(array $restoreData): array;

    // ==================== COMMENTS & DELAYS ====================

    /**
     * Create operation comment
     * POST /api/nsk/v1/trip/info/{legKey}/status/comments
     */
    public function createComment(string $legKey, array $commentData): array;

    /**
     * Update operation comment
     * PUT /api/nsk/v1/trip/info/{legKey}/status/comments/{commentKey}
     */
    public function updateComment(string $legKey, string $commentKey, array $commentData): array;

    /**
     * Delete operation comment
     * DELETE /api/nsk/v1/trip/info/{legKey}/status/comments/{commentKey}
     */
    public function deleteComment(string $legKey, string $commentKey): array;

    /**
     * Create operation delay
     * POST /api/nsk/v1/trip/info/{legKey}/status/delays
     */
    public function createDelay(string $legKey, array $delayData): array;

    /**
     * Update operation delay
     * PATCH /api/nsk/v1/trip/info/{legKey}/status/delays/{delayKey}
     */
    public function updateDelay(string $legKey, string $delayKey, array $delayData): array;

    /**
     * Delete operation delay
     * DELETE /api/nsk/v1/trip/info/{legKey}/status/delays/{delayKey}
     */
    public function deleteDelay(string $legKey, string $delayKey): array;

    // ==================== MOVES & AVAILABILITY ====================

    /**
     * Move journey on booking
     * POST /api/nsk/v2/trip/move
     */
    public function moveJourney(array $moveRequest): array;

    /**
     * Search move availability (full request)
     * POST /api/nsk/v3/trip/move/availability
     */
    public function searchMoveAvailability(array $availabilityRequest): array;

    /**
     * Simple move availability search
     * GET /api/nsk/v3/trip/move/availability/{journeyKey}
     */
    public function getMoveAvailabilitySimple(string $journeyKey, array $params = []): array;

    /**
     * Self-service move availability
     * GET /api/nsk/v2/trip/move/availability/selfService
     */
    public function getMoveAvailabilitySelfService(array $params): array;

    /**
     * IROP move (agent-only)
     * POST /api/nsk/v1/trip/move/irop
     */
    public function moveIrop(array $iropRequest): array;

    // ==================== REBOOKING & STANDBY ====================

    /**
     * Rebook availability search
     * POST /api/nsk/v5/trip/rebook/availability
     */
    public function searchRebookAvailability(array $rebookRequest): array;

    /**
     * Simple rebook search
     * GET /api/nsk/v4/trip/rebook/availability/simple
     */
    public function getRebookAvailabilitySimple(array $params): array;

    /**
     * Same-day standby availability
     * POST /api/nsk/v1/trip/standby/availability
     */
    public function searchStandbyAvailability(array $standbyRequest): array;

    // ==================== SCHEDULE MANAGEMENT ====================

    /**
     * Get flight schedule for market
     * GET /api/nsk/v1/trip/schedule
     */
    public function getSchedule(array $params): array;

    /**
     * Create ad hoc flight
     * POST /api/nsk/v1/trip/schedule/adHoc
     */
    public function createAdHocFlight(array $adHocData): array;
}
