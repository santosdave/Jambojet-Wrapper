<?php

namespace SantosDave\JamboJet\Contracts;

interface BookingInterface
{
    /**
     * Create a new booking
     * POST /api/nsk/v#/bookings
     */
    public function create(array $bookingData): array;

    /**
     * Get booking by record locator
     * GET /api/nsk/v#/bookings/{recordLocator}
     */
    public function getByRecordLocator(string $recordLocator): array;

    /**
     * Update booking
     * PUT /api/nsk/v#/bookings/{recordLocator}
     */
    public function update(string $recordLocator, array $updateData): array;

    /**
     * Cancel booking
     * DELETE /api/nsk/v#/bookings/{recordLocator}
     */
    public function cancel(string $recordLocator, array $cancellationData = []): array;

    /**
     * Add passengers to booking
     * POST /api/nsk/v#/bookings/{recordLocator}/passengers
     */
    public function addPassengers(string $recordLocator, array $passengers): array;

    /**
     * Update passenger information
     * PUT /api/nsk/v#/bookings/{recordLocator}/passengers/{passengerKey}
     */
    public function updatePassenger(string $recordLocator, string $passengerKey, array $passengerData): array;

    /**
     * Remove passenger from booking
     * DELETE /api/nsk/v#/bookings/{recordLocator}/passengers/{passengerKey}
     */
    public function removePassenger(string $recordLocator, string $passengerKey): array;

    /**
     * Commit booking changes
     * POST /api/nsk/v#/bookings/{recordLocator}/commit
     */
    public function commit(string $recordLocator): array;

    /**
     * Get booking history
     * GET /api/nsk/v#/bookings/{recordLocator}/history
     */
    public function getHistory(string $recordLocator): array;
}
