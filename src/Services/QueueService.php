<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\QueueInterface;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Exceptions\JamboJetValidationException;

/**
 * Queue Service for JamboJet NSK API
 * 
 * Manages booking queues and travel queues for workflow automation.
 * Base path: /api/nsk/v1 and /api/nsk/v2
 * 
 * All queue operations require agent session token.
 * 
 * Supported endpoints:
 * - GET /api/nsk/v2/queues/bookings
 * - DELETE /api/nsk/v1/queues/bookings/{bookingQueueCode}/items
 * - PUT /api/nsk/v1/queues/bookings/{bookingQueueCode}/items/{bookingQueueItemKey}
 * - DELETE /api/nsk/v1/queues/bookings/{bookingQueueCode}/items/{bookingQueueItemKey}
 * - GET /api/nsk/v2/queues/bookings/{bookingQueueCode}/next
 * - POST /api/nsk/v2/queues/bookings/items/{bookingQueueItemKey}
 * - GET /api/nsk/v1/queues/bookings/queueEvents/{queueEventType}
 * - POST /api/nsk/v1/queues/travel
 * - GET /api/nsk/v1/queues/travel/{travelQueueCode}/next
 * 
 * @package SantosDave\JamboJet\Services
 */
class QueueService implements QueueInterface
{
    use HandlesApiRequests, ValidatesRequests;

    // =================================================================
    // BOOKING QUEUE MANAGEMENT
    // =================================================================

    /**
     * Get list of available booking queues with filtering
     * GET /api/nsk/v2/queues/bookings
     */
    public function getBookingQueues(
        ?string $queueName = null,
        ?string $queueCode = null,
        ?string $queueCategoryCode = null,
        ?int $pageSize = null,
        ?int $lastPageIndex = null
    ): array {
        // Validate inputs
        if ($queueCategoryCode !== null && strlen($queueCategoryCode) !== 1) {
            throw new JamboJetValidationException('Queue category code must be single character', 400);
        }

        if ($pageSize !== null && ($pageSize < 10 || $pageSize > 5000)) {
            throw new JamboJetValidationException('Page size must be between 10 and 5000', 400);
        }

        // Build query parameters
        $params = array_filter([
            'QueueName' => $queueName,
            'QueueCode' => $queueCode,
            'QueueCategoryCode' => $queueCategoryCode,
            'PageSize' => $pageSize,
            'LastPageIndex' => $lastPageIndex,
        ], fn($value) => $value !== null);

        try {
            return $this->get('api/nsk/v2/queues/bookings', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get booking queues: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Empty entire booking queue (delete all items)
     * DELETE /api/nsk/v1/queues/bookings/{bookingQueueCode}/items
     */
    public function emptyBookingQueue(string $bookingQueueCode, ?string $subQueueCode = null): array
    {
        $this->validateQueueCode($bookingQueueCode);

        $params = $subQueueCode ? ['subQueueCode' => $subQueueCode] : [];

        try {
            return $this->delete("api/nsk/v1/queues/bookings/{$bookingQueueCode}/items", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to empty booking queue: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Move booking queue item to different queue
     * PUT /api/nsk/v1/queues/bookings/{bookingQueueCode}/items/{bookingQueueItemKey}
     */
    public function moveBookingQueueItem(
        string $bookingQueueCode,
        string $bookingQueueItemKey,
        array $moveRequest
    ): array {
        $this->validateQueueCode($bookingQueueCode);
        $this->validateQueueItemKey($bookingQueueItemKey);
        $this->validateMoveRequest($moveRequest);

        try {
            return $this->put(
                "api/nsk/v1/queues/bookings/{$bookingQueueCode}/items/{$bookingQueueItemKey}",
                $moveRequest
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to move booking queue item: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Remove specific item from booking queue
     * DELETE /api/nsk/v1/queues/bookings/{bookingQueueCode}/items/{bookingQueueItemKey}
     */
    public function deleteBookingQueueItem(
        string $bookingQueueCode,
        string $bookingQueueItemKey,
        array $deleteRequest
    ): array {
        $this->validateQueueCode($bookingQueueCode);
        $this->validateQueueItemKey($bookingQueueItemKey);

        try {
            return $this->delete(
                "api/nsk/v1/queues/bookings/{$bookingQueueCode}/items/{$bookingQueueItemKey}",
                [],
                $deleteRequest
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete booking queue item: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get next item from booking queue (dequeue without deleting)
     * GET /api/nsk/v2/queues/bookings/{bookingQueueCode}/next
     */
    public function getNextBookingQueueItem(
        string $bookingQueueCode,
        ?string $subQueueCode = null,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $password = null
    ): array {
        $this->validateQueueCode($bookingQueueCode);

        // Validate date formats if provided
        if ($startDate !== null && !$this->isValidDateTime($startDate)) {
            throw new JamboJetValidationException('Invalid start date format (ISO 8601 required)', 400);
        }

        if ($endDate !== null && !$this->isValidDateTime($endDate)) {
            throw new JamboJetValidationException('Invalid end date format (ISO 8601 required)', 400);
        }

        $params = array_filter([
            'SubQueueCode' => $subQueueCode,
            'StartDate' => $startDate,
            'EndDate' => $endDate,
            'Password' => $password,
        ], fn($value) => $value !== null);

        try {
            return $this->get("api/nsk/v2/queues/bookings/{$bookingQueueCode}/next", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get next booking queue item: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Return dequeued item back to booking queue
     * POST /api/nsk/v2/queues/bookings/items/{bookingQueueItemKey}
     */
    public function releaseBookingQueueItem(string $bookingQueueItemKey, array $releaseRequest): array
    {
        $this->validateQueueItemKey($bookingQueueItemKey);

        try {
            return $this->post("api/nsk/v2/queues/bookings/items/{$bookingQueueItemKey}", $releaseRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to release booking queue item: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // QUEUE EVENT MANAGEMENT
    // =================================================================

    /**
     * Get booking queues matching specific event type
     * GET /api/nsk/v1/queues/bookings/queueEvents/{queueEventType}
     */
    public function getQueuesByEventType(int $queueEventType): array
    {
        // Validate event type (0/Default is invalid, must be 1-80+)
        if ($queueEventType === 0) {
            throw new JamboJetValidationException('Queue event type 0 (Default) is invalid', 400);
        }

        if ($queueEventType < 0 || $queueEventType > 100) {
            throw new JamboJetValidationException('Queue event type must be between 1 and 80', 400);
        }

        try {
            return $this->get("api/nsk/v1/queues/bookings/queueEvents/{$queueEventType}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get queues by event type: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // TRAVEL QUEUE OPERATIONS
    // =================================================================

    /**
     * Create new travel queue entry
     * POST /api/nsk/v1/queues/travel
     */
    public function createTravelQueueItem(array $travelQueueItemRequest): array
    {
        $this->validateTravelQueueItemRequest($travelQueueItemRequest);

        try {
            return $this->post('api/nsk/v1/queues/travel', $travelQueueItemRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create travel queue item: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Pop next item from travel queue (deletes from queue)
     * GET /api/nsk/v1/queues/travel/{travelQueueCode}/next
     */
    public function getNextTravelQueueItem(string $travelQueueCode, ?string $subQueueCode = null): array
    {
        $this->validateQueueCode($travelQueueCode);

        $params = $subQueueCode ? ['subQueueCode' => $subQueueCode] : [];

        try {
            return $this->get("api/nsk/v1/queues/travel/{$travelQueueCode}/next", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get next travel queue item: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // =================================================================
    // VALIDATION HELPERS
    // =================================================================

    /**
     * Validate queue code
     */
    private function validateQueueCode(string $queueCode): void
    {
        if (empty($queueCode)) {
            throw new JamboJetValidationException('Queue code is required', 400);
        }

        // Queue codes are typically alphanumeric, max 10 chars
        if (strlen($queueCode) > 20) {
            throw new JamboJetValidationException('Invalid queue code format', 400);
        }
    }

    /**
     * Validate queue item key
     */
    private function validateQueueItemKey(string $itemKey): void
    {
        if (empty($itemKey)) {
            throw new JamboJetValidationException('Queue item key is required', 400);
        }
    }

    /**
     * Validate move request
     */
    private function validateMoveRequest(array $request): void
    {
        if (empty($request)) {
            throw new JamboJetValidationException('Move request cannot be empty', 400);
        }

        // Typically requires targetQueueCode
        if (!isset($request['targetQueueCode']) || empty($request['targetQueueCode'])) {
            throw new JamboJetValidationException('Target queue code is required', 400);
        }
    }

    /**
     * Validate travel queue item request
     */
    private function validateTravelQueueItemRequest(array $request): void
    {
        if (empty($request)) {
            throw new JamboJetValidationException('Travel queue item request cannot be empty', 400);
        }

        // Add specific validation based on travel queue requirements
        // Typically requires queueCode, recordLocator, etc.
    }

    /**
     * Helper to validate datetime format
     */
    private function isValidDateTime(string $datetime): bool
    {
        try {
            $d = new \DateTime($datetime);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
