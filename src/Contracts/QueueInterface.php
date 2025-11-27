<?php

namespace SantosDave\JamboJet\Contracts;

use SantosDave\JamboJet\Exceptions\JamboJetApiException;

/**
 * Queue Interface for JamboJet NSK API
 * 
 * Manages booking queues and travel queues for workflow automation.
 * Base path: /api/nsk/v1 and /api/nsk/v2
 * 
 * All queue operations require agent session token.
 * 
 * Queue Types:
 * - Booking Queues: Hold bookings requiring agent attention (80+ event types)
 * - Travel Queues: Workflow management for travel processing
 * 
 * @package SantosDave\JamboJet\Contracts
 */
interface QueueInterface
{
    // =================================================================
    // BOOKING QUEUE MANAGEMENT (6 methods)
    // =================================================================

    /**
     * Get list of available booking queues with filtering
     * GET /api/nsk/v2/queues/bookings
     * 
     * Known issue: Total count may be incorrect on first request.
     * 
     * @param string|null $queueName Optional queue name filter
     * @param string|null $queueCode Optional queue code filter
     * @param string|null $queueCategoryCode Optional queue category code (single char)
     * @param int|null $pageSize Optional page size (10-5000)
     * @param int|null $lastPageIndex Optional last page index for pagination
     * @return array List of queues with pagination info
     * @throws JamboJetApiException
     */
    public function getBookingQueues(
        ?string $queueName = null,
        ?string $queueCode = null,
        ?string $queueCategoryCode = null,
        ?int $pageSize = null,
        ?int $lastPageIndex = null
    ): array;

    /**
     * Empty entire booking queue (delete all items)
     * DELETE /api/nsk/v1/queues/bookings/{bookingQueueCode}/items
     * 
     * @param string $bookingQueueCode The booking queue code
     * @param string|null $subQueueCode Optional sub-queue code
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function emptyBookingQueue(string $bookingQueueCode, ?string $subQueueCode = null): array;

    /**
     * Move booking queue item to different queue
     * PUT /api/nsk/v1/queues/bookings/{bookingQueueCode}/items/{bookingQueueItemKey}
     * 
     * Returns 400 if booking already in target queue.
     * 
     * @param string $bookingQueueCode Current queue code
     * @param string $bookingQueueItemKey Queue item key
     * @param array $moveRequest Move request with target queue info
     * @return array Move result
     * @throws JamboJetApiException
     */
    public function moveBookingQueueItem(
        string $bookingQueueCode,
        string $bookingQueueItemKey,
        array $moveRequest
    ): array;

    /**
     * Remove specific item from booking queue
     * DELETE /api/nsk/v1/queues/bookings/{bookingQueueCode}/items/{bookingQueueItemKey}
     * 
     * @param string $bookingQueueCode The booking queue code
     * @param string $bookingQueueItemKey Queue item key
     * @param array $deleteRequest Delete request data
     * @return array Deletion result
     * @throws JamboJetApiException
     */
    public function deleteBookingQueueItem(
        string $bookingQueueCode,
        string $bookingQueueItemKey,
        array $deleteRequest
    ): array;

    /**
     * Get next item from booking queue (dequeue without deleting)
     * GET /api/nsk/v2/queues/bookings/{bookingQueueCode}/next
     * 
     * Retrieves next item but does NOT delete it from queue.
     * Must call releaseBookingQueueItem() to return or deleteBookingQueueItem() to remove.
     * 
     * @param string $bookingQueueCode The booking queue code
     * @param string|null $subQueueCode Optional sub-queue code
     * @param string|null $startDate Optional begin priority date (ISO 8601)
     * @param string|null $endDate Optional end priority date (ISO 8601)
     * @param string|null $password Optional queue password if password-protected
     * @return array Next queue item
     * @throws JamboJetApiException
     */
    public function getNextBookingQueueItem(
        string $bookingQueueCode,
        ?string $subQueueCode = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $password = null
    ): array;

    /**
     * Return dequeued item back to booking queue
     * POST /api/nsk/v2/queues/bookings/items/{bookingQueueItemKey}
     * 
     * Only for items dequeued via getNextBookingQueueItem().
     * Does not apply to deleted items.
     * 
     * @param string $bookingQueueItemKey Queue item key
     * @param array $releaseRequest Release request with optional password
     * @return array Release result
     * @throws JamboJetApiException
     */
    public function releaseBookingQueueItem(string $bookingQueueItemKey, array $releaseRequest): array;

    // =================================================================
    // QUEUE EVENT MANAGEMENT (1 method)
    // =================================================================

    /**
     * Get booking queues matching specific event type
     * GET /api/nsk/v1/queues/bookings/queueEvents/{queueEventType}
     * 
     * Event Types (80+ total):
     * - 1: BookingBalanceDue
     * - 2: BookingNegativeBalance
     * - 3: BookingCustomerComment
     * - 4: DeclinedPaymentInitial
     * - 5: DeclinedPaymentChange
     * - 7: ScheduleTimeChange
     * - 9: ScheduleCancellation
     * - 15: HeldBookings
     * - 20: BookingSegmentOversold
     * - 30: GroupBookings
     * ... etc (see documentation for full list)
     * 
     * Note: 0 (Default) is invalid and returns 400 error.
     * 
     * @param int $queueEventType Queue event type (1-80)
     * @return array List of queues for this event type (empty array if none match)
     * @throws JamboJetApiException
     */
    public function getQueuesByEventType(int $queueEventType): array;

    // =================================================================
    // TRAVEL QUEUE OPERATIONS (2 methods)
    // =================================================================

    /**
     * Create new travel queue entry
     * POST /api/nsk/v1/queues/travel
     * 
     * @param array $travelQueueItemRequest Travel queue item data
     * @return array Creation result
     * @throws JamboJetApiException
     */
    public function createTravelQueueItem(array $travelQueueItemRequest): array;

    /**
     * Pop next item from travel queue (deletes from queue)
     * GET /api/nsk/v1/queues/travel/{travelQueueCode}/next
     * 
     * WARNING: Unlike booking queues, this operation DELETES the item from queue.
     * 
     * @param string $travelQueueCode Travel queue code
     * @param string|null $subQueueCode Optional sub-queue code
     * @return array Next travel queue item
     * @throws JamboJetApiException
     */
    public function getNextTravelQueueItem(string $travelQueueCode, ?string $subQueueCode = null): array;
}
