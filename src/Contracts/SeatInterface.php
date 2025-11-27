<?php

namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;

interface SeatInterface
{
    /**
     * Get seat availability for booking in state
     * GET /api/nsk/v1/booking/seat/availability
     */
    public function getSeatAvailability(array $criteria = []): array;

    /**
     * Assign seats to passengers in booking
     * POST /api/nsk/v1/booking/seat/assignment
     */
    public function assignSeats(array $seatAssignments): array;

    /**
     * Remove seat assignment
     * DELETE /api/nsk/v1/booking/seat/assignment/{seatAssignmentKey}
     */
    public function removeSeatAssignment(string $seatAssignmentKey): array;

    /**
     * Get current seat assignments for booking
     * GET /api/nsk/v1/booking/seat/assignments
     */
    public function getSeatAssignments(): array;

    /**
     * Update seat assignment
     * PUT /api/nsk/v1/booking/seat/assignment/{seatAssignmentKey}
     */
    public function updateSeatAssignment(string $seatAssignmentKey, array $updateData): array;

    /**
     * Get seat map for flight segment
     * GET /api/nsk/v1/booking/seat/map/{segmentKey}
     */
    public function getSeatMap(string $segmentKey, array $options = []): array;

    /**
     * Get seat pricing information
     * GET /api/nsk/v1/booking/seat/pricing
     */
    public function getSeatPricing(array $pricingCriteria): array;

    /**
     * Auto-assign seats based on preferences
     * POST /api/nsk/v1/booking/seat/autoAssign
     */
    public function autoAssignSeats(array $preferences = []): array;

    /**
     * Get seat maps for a journey
     * GET /api/nsk/v4/seatmaps/{journeyKey}
     * 
     * Returns list of seat maps for all legs in the journey.
     * 
     * @param string $journeyKey Journey key
     * @param bool|null $includePropertyLookup Include seat property lookup
     * @param string|null $cultureCode Desired culture code
     * @return array List of seat map availability
     * @throws JamboJetApiException
     */
    public function getSeatMapsByJourney(
        string $journeyKey,
        ?bool $includePropertyLookup = null,
        ?string $cultureCode = null
    ): array;

    /**
     * Get seat map for a specific leg
     * GET /api/nsk/v1/seatmaps/{legKey}
     * 
     * Returns seat map for a single leg.
     * 
     * @param string $legKey Leg key
     * @param bool|null $includePropertyLookup Include seat property lookup
     * @param string|null $cultureCode Desired culture code
     * @return array Seat map availability
     * @throws JamboJetApiException
     */
    public function getSeatMapByLeg(
        string $legKey,
        ?bool $includePropertyLookup = null,
        ?string $cultureCode = null
    ): array;

    /**
     * Block seats to prevent assignment
     * POST /api/nsk/v1/booking/seat/block
     * 
     * @param array $unitKeys List of seat unit keys to block
     * @return array Result of blocking operation
     */
    public function blockSeats(array $unitKeys): array;
    /**
     * Unblock previously blocked seats
     * DELETE /api/nsk/v1/booking/seat/block
     * 
     * @param array $unitKeys List of seat unit keys to unblock
     * @return array Result of unblocking operation
     */
    public function unblockSeats(array $unitKeys): array;
}
