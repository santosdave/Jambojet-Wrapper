<?php

namespace SantosDave\JamboJet\Contracts;

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
}
