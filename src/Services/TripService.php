<?php

namespace SantosDave\JamboJet\Services;

use SantosDave\JamboJet\Contracts\TripInterface;
use SantosDave\JamboJet\Exceptions\JamboJetApiException;
use SantosDave\JamboJet\Traits\HandlesApiRequests;
use SantosDave\JamboJet\Traits\ValidatesRequests;

class TripService  implements TripInterface
{
    use HandlesApiRequests, ValidatesRequests;
    // ==================== BOOKING CREATION & PASSENGERS ====================

    /**
     * Create new booking with journeys, contacts, passengers, SSRs
     * POST /api/nsk/v5/trip
     * 
     * ⚠️ CRITICAL: Must NOT be called concurrently with same session token
     */
    public function createBooking(array $bookingData): array
    {
        $this->validateBookingData($bookingData);

        try {
            return $this->post('api/nsk/v5/trip', $bookingData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create booking: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create booking with passengers only
     * POST /api/nsk/v1/trip/passengers
     * 
     * ⚠️ CRITICAL: Must NOT be called concurrently with same session token
     */
    public function createBookingWithPassengers(array $passengers): array
    {
        $this->validatePassengers($passengers);

        try {
            return $this->post('api/nsk/v1/trip/passengers', ['passengers' => $passengers]);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create booking with passengers: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create passive segment booking
     * POST /api/nsk/v1/trip/passiveSegments
     * 
     * ⚠️ CRITICAL: Must NOT be called concurrently with same session token
     */
    public function createPassiveSegment(array $passiveSegmentData): array
    {
        $this->validatePassiveSegment($passiveSegmentData);

        try {
            return $this->post('api/nsk/v1/trip/passiveSegments', $passiveSegmentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create passive segment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Sell journeys and create/update booking
     * POST /api/nsk/v4/trip/sell
     * 
     * ⚠️ CRITICAL: Must NOT be called concurrently with same session token
     */
    public function sellJourneys(array $sellRequest): array
    {
        $this->validateSellRequest($sellRequest);

        try {
            return $this->post('api/nsk/v4/trip/sell', $sellRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to sell journeys: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // ==================== TRIP INFORMATION ====================

    /**
     * Search trip information (max 100 journeys)
     * POST /api/nsk/v2/trip/info
     */
    public function searchTripInfo(array $query): array
    {
        $this->validateTripInfoQuery($query);

        try {
            return $this->post('api/nsk/v2/trip/info', $query);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search trip info: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Simple trip info search with minimal criteria
     * GET /api/nsk/v2/trip/info/simple
     */
    public function getTripInfoSimple(array $params): array
    {
        $this->validateTripInfoSimpleParams($params);

        try {
            return $this->get('api/nsk/v2/trip/info/simple', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get simple trip info: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Search inventory legs by criteria
     * POST /api/nsk/v2/trip/info/legs
     */
    public function searchLegs(array $query): array
    {
        $this->validateLegQuery($query);

        try {
            return $this->post('api/nsk/v2/trip/info/legs', $query);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search legs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Simple leg search
     * GET /api/nsk/v2/trip/info/legs/simple
     */
    public function getLegsSimple(array $params): array
    {
        $this->validateLegSimpleParams($params);

        try {
            return $this->get('api/nsk/v2/trip/info/legs/simple', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get simple legs: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get cabin details and leg cross references
     * GET /api/nsk/v1/trip/info/{legKey}/details
     */
    public function getLegDetails(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v1/trip/info/{$legKey}/details");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get leg details: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // ==================== TRIP STATUS MANAGEMENT ====================

    /**
     * Get trip status for leg
     * GET /api/nsk/v2/trip/info/{legKey}/status
     */
    public function getLegStatus(string $legKey): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->get("api/nsk/v2/trip/info/{$legKey}/status");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get leg status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update trip status (FLIFO - Flight Following)
     * PATCH /api/nsk/v1/trip/info/{legKey}/status
     * 
     * FLIFO Requirements:
     * - Flight must be within 3 days of leaving
     * - Requires FLIFO permissions
     * - Empty string clears value, null retains original
     */
    public function updateLegStatus(string $legKey, array $statusData): array
    {
        $this->validateLegKey($legKey);
        $this->validateStatusData($statusData);

        try {
            return $this->patch("api/nsk/v1/trip/info/{$legKey}/status", $statusData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update leg status: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Cancel leg status
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/cancel
     */
    public function cancelLeg(string $legKey, array $cancelData = []): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->put(
                "api/nsk/v1/trip/info/{$legKey}/status/operationDetails/status/cancel",
                $cancelData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to cancel leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Close leg status
     * PUT /api/nsk/v2/trip/info/{legKey}/status/operationDetails/status/closeLeg
     * 
     * Close Requirements:
     * - PNRGOV messages sent
     * - APIS messages sent
     * - Balance checks passed
     * - Exit row requirements met
     */
    public function closeLeg(string $legKey, array $closeData): array
    {
        $this->validateLegKey($legKey);
        $this->validateCloseData($closeData);

        try {
            return $this->put(
                "api/nsk/v2/trip/info/{$legKey}/status/operationDetails/status/closeLeg",
                $closeData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to close leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Set leg to closed pending
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/closePendingLeg
     */
    public function setLegClosePending(string $legKey, array $pendingData = []): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->put(
                "api/nsk/v1/trip/info/{$legKey}/status/operationDetails/status/closePendingLeg",
                $pendingData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to set leg to close pending: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Set leg to mishap (secure passenger data)
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/mishap
     * 
     * ⚠️ WARNING: Mishap secures and locks ALL passenger data
     */
    public function setLegMishap(string $legKey, array $mishapData = []): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->put(
                "api/nsk/v1/trip/info/{$legKey}/status/operationDetails/status/mishap",
                $mishapData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to set leg to mishap: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Open leg status
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/openLeg
     */
    public function openLeg(string $legKey, array $openData): array
    {
        $this->validateLegKey($legKey);
        $this->validateOpenData($openData);

        try {
            return $this->put(
                "api/nsk/v1/trip/info/{$legKey}/status/operationDetails/status/openLeg",
                $openData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to open leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Restore leg to normal
     * PUT /api/nsk/v1/trip/info/{legKey}/status/operationDetails/status/restore
     */
    public function restoreLeg(string $legKey, array $restoreData = []): array
    {
        $this->validateLegKey($legKey);

        try {
            return $this->put(
                "api/nsk/v1/trip/info/{$legKey}/status/operationDetails/status/restore",
                $restoreData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to restore leg: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Restore from mishap
     * POST /api/nsk/v1/trip/info/status/operationDetails/status/mishapRestore
     */
    public function restoreLegFromMishap(array $restoreData): array
    {
        $this->validateMishapRestoreData($restoreData);

        try {
            return $this->post(
                'api/nsk/v1/trip/info/status/operationDetails/status/mishapRestore',
                $restoreData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to restore from mishap: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // ==================== COMMENTS & DELAYS ====================

    /**
     * Create operation comment
     * POST /api/nsk/v1/trip/info/{legKey}/status/comments
     * 
     * Requires FLIFO permissions
     * Flight must be within 3 days of departure
     */
    public function createComment(string $legKey, array $commentData): array
    {
        $this->validateLegKey($legKey);
        $this->validateCommentData($commentData);

        try {
            return $this->post("api/nsk/v1/trip/info/{$legKey}/status/comments", $commentData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update operation comment
     * PUT /api/nsk/v1/trip/info/{legKey}/status/comments/{commentKey}
     * 
     * Requires FLIFO permissions
     */
    public function updateComment(string $legKey, string $commentKey, array $commentData): array
    {
        $this->validateLegKey($legKey);
        $this->validateCommentKey($commentKey);
        $this->validateCommentData($commentData);

        try {
            return $this->put(
                "api/nsk/v1/trip/info/{$legKey}/status/comments/{$commentKey}",
                $commentData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete operation comment
     * DELETE /api/nsk/v1/trip/info/{legKey}/status/comments/{commentKey}
     * 
     * Requires FLIFO permissions
     */
    public function deleteComment(string $legKey, string $commentKey): array
    {
        $this->validateLegKey($legKey);
        $this->validateCommentKey($commentKey);

        try {
            return $this->delete("api/nsk/v1/trip/info/{$legKey}/status/comments/{$commentKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete comment: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create operation delay
     * POST /api/nsk/v1/trip/info/{legKey}/status/delays
     * 
     * Requires FLIFO permissions
     * Flight must be within 3 days of departure
     */
    public function createDelay(string $legKey, array $delayData): array
    {
        $this->validateLegKey($legKey);
        $this->validateDelayData($delayData);

        try {
            return $this->post("api/nsk/v1/trip/info/{$legKey}/status/delays", $delayData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create delay: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Update operation delay
     * PATCH /api/nsk/v1/trip/info/{legKey}/status/delays/{delayKey}
     * 
     * Requires FLIFO permissions
     */
    public function updateDelay(string $legKey, string $delayKey, array $delayData): array
    {
        $this->validateLegKey($legKey);
        $this->validateDelayKey($delayKey);
        $this->validateDelayData($delayData);

        try {
            return $this->patch(
                "api/nsk/v1/trip/info/{$legKey}/status/delays/{$delayKey}",
                $delayData
            );
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to update delay: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Delete operation delay
     * DELETE /api/nsk/v1/trip/info/{legKey}/status/delays/{delayKey}
     * 
     * Requires FLIFO permissions
     */
    public function deleteDelay(string $legKey, string $delayKey): array
    {
        $this->validateLegKey($legKey);
        $this->validateDelayKey($delayKey);

        try {
            return $this->delete("api/nsk/v1/trip/info/{$legKey}/status/delays/{$delayKey}");
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to delete delay: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }


    // ==================== MOVES & AVAILABILITY ====================

    /**
     * Move journey on booking
     * POST /api/nsk/v2/trip/move
     * 
     * ⚠️ CRITICAL: Must NOT be called concurrently with same session token
     * 
     * Move Types:
     * 0 = None, 1 = Irop, 2 = Diversion, 4 = FlightClose
     * 5 = FlyAhead, 6 = SplitJourney, 7 = SelfServiceRebooking, 8 = HIDR
     */
    public function moveJourney(array $moveRequest): array
    {
        $this->validateMoveRequest($moveRequest);

        try {
            return $this->post('api/nsk/v2/trip/move', $moveRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to move journey: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Search move availability (full request)
     * POST /api/nsk/v3/trip/move/availability
     * 
     * Service bundle offers expected to be not populated
     */
    public function searchMoveAvailability(array $availabilityRequest): array
    {
        $this->validateMoveAvailabilityRequest($availabilityRequest);

        try {
            return $this->post('api/nsk/v3/trip/move/availability', $availabilityRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search move availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Simple move availability search
     * GET /api/nsk/v3/trip/move/availability/{journeyKey}
     */
    public function getMoveAvailabilitySimple(string $journeyKey, array $params = []): array
    {
        $this->validateJourneyKey($journeyKey);

        try {
            return $this->get("api/nsk/v3/trip/move/availability/{$journeyKey}", $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get simple move availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Self-service move availability
     * GET /api/nsk/v2/trip/move/availability/selfService
     * 
     * Defaults: BeginDate=journey departure, EndDate=BeginDate+1
     */
    public function getMoveAvailabilitySelfService(array $params): array
    {
        $this->validateSelfServiceParams($params);

        try {
            return $this->get('api/nsk/v2/trip/move/availability/selfService', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get self-service move availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * IROP move (agent-only)
     * POST /api/nsk/v1/trip/move/irop
     * 
     * Requires agent permissions
     */
    public function moveIrop(array $iropRequest): array
    {
        $this->validateIropRequest($iropRequest);

        try {
            return $this->post('api/nsk/v1/trip/move/irop', $iropRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to execute IROP move: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Rebook availability search
     * POST /api/nsk/v5/trip/rebook/availability
     * 
     * Loyalty Filters:
     * 0 = MonetaryOnly, 1 = PointsOnly
     * 2 = PointsAndMonetary, 3 = PreserveCurrent
     * 
     * Requires booking in state
     * Service bundle offers expected not populated
     */
    public function searchRebookAvailability(array $rebookRequest): array
    {
        $this->validateRebookRequest($rebookRequest);

        try {
            return $this->post('api/nsk/v5/trip/rebook/availability', $rebookRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search rebook availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Simple rebook search
     * GET /api/nsk/v4/trip/rebook/availability/simple
     * 
     * Requires booking in state
     */
    public function getRebookAvailabilitySimple(array $params): array
    {
        $this->validateRebookSimpleParams($params);

        try {
            return $this->get('api/nsk/v4/trip/rebook/availability/simple', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get simple rebook availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Same-day standby availability
     * POST /api/nsk/v1/trip/standby/availability
     * 
     * Standby is same-day only
     * NoPricing option supported (for Move flows)
     */
    public function searchStandbyAvailability(array $standbyRequest): array
    {
        $this->validateStandbyRequest($standbyRequest);

        try {
            return $this->post('api/nsk/v1/trip/standby/availability', $standbyRequest);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to search standby availability: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // ==================== SCHEDULE MANAGEMENT ====================

    /**
     * Get flight schedule for market
     * GET /api/nsk/v1/trip/schedule
     * 
     * Flight Types:
     * 0 = None, 1 = NonStop, 2 = Through
     * 3 = Direct, 4 = Connect, 5 = All
     */
    public function getSchedule(array $params): array
    {
        $this->validateScheduleParams($params);

        try {
            return $this->get('api/nsk/v1/trip/schedule', $params);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to get schedule: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Create ad hoc flight
     * POST /api/nsk/v1/trip/schedule/adHoc
     * 
     * Creates custom/charter flight for special operations
     */
    public function createAdHocFlight(array $adHocData): array
    {
        $this->validateAdHocData($adHocData);

        try {
            return $this->post('api/nsk/v1/trip/schedule/adHoc', $adHocData);
        } catch (\Exception $e) {
            throw new JamboJetApiException(
                'Failed to create ad hoc flight: ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    // ==================== VALIDATION METHODS ====================

    /**
     * Validate booking data for creation
     */
    private function validateBookingData(array $data): void
    {
        // Validate passengers array exists
        if (!isset($data['passengers']) || !is_array($data['passengers'])) {
            throw new \InvalidArgumentException('Passengers array is required');
        }

        if (empty($data['passengers'])) {
            throw new \InvalidArgumentException('At least one passenger is required');
        }

        foreach ($data['passengers'] as $index => $bookingPassenger) {
            // Validate that 'passenger' wrapper exists (BookingPassengerRequest requirement)
            if (!isset($bookingPassenger['passenger']) || !is_array($bookingPassenger['passenger'])) {
                throw new \InvalidArgumentException("Passengers[{$index}]: 'passenger' object wrapper is required");
            }

            $passenger = $bookingPassenger['passenger'];

            // Validate passengerTypeCode
            if (!isset($passenger['passengerTypeCode']) || empty(trim($passenger['passengerTypeCode']))) {
                throw new \InvalidArgumentException("Passenger {$index}: passengerTypeCode is required");
            }

            // Validate name object
            if (!isset($passenger['name']) || !is_array($passenger['name'])) {
                throw new \InvalidArgumentException("Passenger {$index}: name object is required");
            }

            if (!isset($passenger['name']['first']) || empty(trim($passenger['name']['first']))) {
                throw new \InvalidArgumentException("Passenger {$index}: first name is required");
            }

            if (!isset($passenger['name']['last']) || empty(trim($passenger['name']['last']))) {
                throw new \InvalidArgumentException("Passenger {$index}: last name is required");
            }

            // Validate info object
            if (!isset($passenger['info']) || !is_array($passenger['info'])) {
                throw new \InvalidArgumentException("Passenger {$index}: info object is required");
            }

            $requiredInfoFields = ['nationality', 'residentCountry', 'gender', 'dateOfBirth'];
            foreach ($requiredInfoFields as $field) {
                if (!isset($passenger['info'][$field])) {
                    throw new \InvalidArgumentException("Passenger {$index}: info.{$field} is required");
                }
            }

            // Validate date of birth format
            if (!$this->isValidDate($passenger['info']['dateOfBirth'])) {
                throw new \InvalidArgumentException("Passenger {$index}: Invalid dateOfBirth format (expected YYYY-MM-DD)");
            }

            // Validate travel documents if provided
            if (isset($passenger['travelDocuments'])) {
                if (!is_array($passenger['travelDocuments'])) {
                    throw new \InvalidArgumentException("Passenger {$index}: travelDocuments must be an array");
                }

                foreach ($passenger['travelDocuments'] as $docIndex => $document) {
                    if (!isset($document['number']) || empty(trim($document['number']))) {
                        throw new \InvalidArgumentException("Passenger {$index}, Document {$docIndex}: document number is required");
                    }

                    if (!isset($document['documentTypeCode']) || empty(trim($document['documentTypeCode']))) {
                        throw new \InvalidArgumentException("Passenger {$index}, Document {$docIndex}: documentTypeCode is required");
                    }

                    if (isset($document['dateOfBirth']) && !$this->isValidDate($document['dateOfBirth'])) {
                        throw new \InvalidArgumentException("Passenger {$index}, Document {$docIndex}: Invalid dateOfBirth format");
                    }

                    if (isset($document['expirationDate']) && !$this->isValidDate($document['expirationDate'])) {
                        throw new \InvalidArgumentException("Passenger {$index}, Document {$docIndex}: Invalid expirationDate format");
                    }
                }
            }

            // Validate SSRs if provided (optional field in BookingPassengerRequest)
            if (isset($bookingPassenger['ssrs'])) {
                if (!is_array($bookingPassenger['ssrs'])) {
                    throw new \InvalidArgumentException("Passenger {$index}: ssrs must be an array");
                }

                foreach ($bookingPassenger['ssrs'] as $ssrIndex => $ssr) {
                    if (!isset($ssr['ssrCode']) || empty(trim($ssr['ssrCode']))) {
                        throw new \InvalidArgumentException("Passenger {$index}, SSR {$ssrIndex}: ssrCode is required");
                    }
                }
            }

            // Validate discountCode if provided (optional field in BookingPassengerRequest)
            if (isset($bookingPassenger['discountCode']) && strlen($bookingPassenger['discountCode']) > 4) {
                throw new \InvalidArgumentException("Passenger {$index}: discountCode must be 4 characters or less");
            }
        }

        // Validate journeys
        if (!isset($data['journeys']) || !is_array($data['journeys'])) {
            throw new \InvalidArgumentException('Journeys object is required');
        }

        if (!isset($data['journeys']['keys']) || !is_array($data['journeys']['keys'])) {
            throw new \InvalidArgumentException('Journey keys array is required');
        }

        if (empty($data['journeys']['keys'])) {
            throw new \InvalidArgumentException('At least one journey key is required');
        }

        foreach ($data['journeys']['keys'] as $index => $journey) {
            if (!isset($journey['journeyKey']) || empty(trim($journey['journeyKey']))) {
                throw new \InvalidArgumentException("Journey {$index}: journeyKey is required");
            }

            if (!isset($journey['fareAvailabilityKey']) || empty(trim($journey['fareAvailabilityKey']))) {
                throw new \InvalidArgumentException("Journey {$index}: fareAvailabilityKey is required");
            }
        }

        // Validate contact
        if (!isset($data['contact']) || !is_array($data['contact'])) {
            throw new \InvalidArgumentException('Contact object is required');
        }

        if (!isset($data['contact']['emailAddress']) || empty(trim($data['contact']['emailAddress']))) {
            throw new \InvalidArgumentException('Contact email address is required');
        }

        if (!filter_var($data['contact']['emailAddress'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid contact email address format');
        }

        // if (!isset($data['contact']['customerNumber']) || empty(trim($data['contact']['customerNumber']))) {
        //     throw new \InvalidArgumentException('Contact customer number is required');
        // }

        // Validate contact phone numbers if provided
        if (isset($data['contact']['phoneNumbers']) && is_array($data['contact']['phoneNumbers'])) {
            foreach ($data['contact']['phoneNumbers'] as $index => $phone) {
                if (!isset($phone['number']) || empty(trim($phone['number']))) {
                    throw new \InvalidArgumentException("Contact phone {$index}: number is required");
                }
            }
        }

        // Validate currency code
        if (!isset($data['currencyCode']) || empty(trim($data['currencyCode']))) {
            throw new \InvalidArgumentException('Currency code is required');
        }

        if (strlen($data['currencyCode']) !== 3) {
            throw new \InvalidArgumentException('Currency code must be 3 characters (ISO 4217)');
        }
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate passive segment data
     */
    private function validatePassiveSegment(array $data): void
    {
        // Validate required fields
        if (!isset($data['departureStation']) || strlen($data['departureStation']) !== 3) {
            throw new \InvalidArgumentException('Departure station code must be exactly 3 characters');
        }

        if (!isset($data['arrivalStation']) || strlen($data['arrivalStation']) !== 3) {
            throw new \InvalidArgumentException('Arrival station code must be exactly 3 characters');
        }

        if (!isset($data['departureDate'])) {
            throw new \InvalidArgumentException('Departure date is required');
        }

        // Validate carrier code if provided
        if (isset($data['carrierCode']) && (strlen($data['carrierCode']) < 2 || strlen($data['carrierCode']) > 3)) {
            throw new \InvalidArgumentException('Carrier code must be 2-3 characters');
        }

        // Validate flight number if provided
        if (isset($data['flightNumber']) && strlen($data['flightNumber']) > 4) {
            throw new \InvalidArgumentException('Flight number must be maximum 4 characters');
        }
    }

    /**
     * Validate sell request data
     */
    private function validateSellRequest(array $data): void
    {
        // Validate journey keys
        if (!isset($data['journeyKeys']) || !is_array($data['journeyKeys'])) {
            throw new \InvalidArgumentException('Journey keys array is required');
        }

        if (empty($data['journeyKeys'])) {
            throw new \InvalidArgumentException('At least one journey key is required');
        }

        // Validate currency code if provided
        if (isset($data['currencyCode']) && strlen($data['currencyCode']) !== 3) {
            throw new \InvalidArgumentException('Currency code must be exactly 3 characters');
        }

        // Validate passenger keys if provided
        if (isset($data['passengerKeys'])) {
            if (!is_array($data['passengerKeys'])) {
                throw new \InvalidArgumentException('Passenger keys must be an array');
            }
        }
    }

    /**
     * Validate rebook simple params
     */
    private function validateRebookSimpleParams(array $params): void
    {
        // Rebook requires booking in state (validated server-side)

        // Validate journey key if provided
        if (isset($params['journeyKey']) && empty($params['journeyKey'])) {
            throw new \InvalidArgumentException('Journey key cannot be empty');
        }

        // Validate loyalty filter if provided
        if (isset($params['loyaltyFilter']) && !in_array($params['loyaltyFilter'], [0, 1, 2, 3])) {
            throw new \InvalidArgumentException(
                'Invalid loyalty filter. Must be 0-3 (MonetaryOnly, PointsOnly, PointsAndMonetary, PreserveCurrent)'
            );
        }

        // Validate dates
        if (isset($params['beginDate']) && isset($params['endDate'])) {
            $begin = strtotime($params['beginDate']);
            $end = strtotime($params['endDate']);

            if ($end < $begin) {
                throw new \InvalidArgumentException('End date cannot be before begin date');
            }
        }
    }

    /**
     * Validate standby request
     */
    private function validateStandbyRequest(array $data): void
    {
        // Standby is SAME-DAY ONLY - critical validation

        // Validate journey key
        if (!isset($data['journeyKey']) || empty($data['journeyKey'])) {
            throw new \InvalidArgumentException('Journey key is required for standby');
        }

        // Validate date is same-day if provided
        if (isset($data['date'])) {
            $requestDate = date('Y-m-d', strtotime($data['date']));
            $today = date('Y-m-d');

            if ($requestDate !== $today) {
                throw new \InvalidArgumentException('Standby availability is only available for same-day flights');
            }
        }

        // Validate NoPricing flag if provided (for Move flows)
        if (isset($data['noPricing']) && !is_bool($data['noPricing'])) {
            throw new \InvalidArgumentException('noPricing must be a boolean');
        }

        // Validate passenger keys if provided
        if (isset($data['passengerKeys'])) {
            if (!is_array($data['passengerKeys'])) {
                throw new \InvalidArgumentException('Passenger keys must be an array');
            }
            if (empty($data['passengerKeys'])) {
                throw new \InvalidArgumentException('At least one passenger key is required');
            }
        }
    }

    /**
     * Validate trip info simple params
     */
    private function validateTripInfoSimpleParams(array $params): void
    {
        // Validate station codes if provided
        if (isset($params['origin']) && strlen($params['origin']) !== 3) {
            throw new \InvalidArgumentException('Origin station code must be exactly 3 characters');
        }

        if (isset($params['destination']) && strlen($params['destination']) !== 3) {
            throw new \InvalidArgumentException('Destination station code must be exactly 3 characters');
        }

        // Validate carrier code
        if (isset($params['carrierCode']) && (strlen($params['carrierCode']) < 2 || strlen($params['carrierCode']) > 3)) {
            throw new \InvalidArgumentException('Carrier code must be 2-3 characters');
        }

        // Validate identifier (flight number)
        if (isset($params['identifier']) && strlen($params['identifier']) > 4) {
            throw new \InvalidArgumentException('Flight identifier must be maximum 4 characters');
        }

        // Validate flight type if provided
        if (isset($params['flightType']) && !in_array($params['flightType'], [0, 1, 2, 3, 4, 5])) {
            throw new \InvalidArgumentException(
                'Invalid flight type. Must be 0-5 (None, NonStop, Through, Direct, Connect, All)'
            );
        }
    }

    /**
     * Validate leg query
     */
    private function validateLegQuery(array $query): void
    {
        // Validate number of journeys
        if (isset($query['numberOfJourneys'])) {
            if (!is_numeric($query['numberOfJourneys']) || $query['numberOfJourneys'] < 1) {
                throw new \InvalidArgumentException('Number of journeys must be a positive number');
            }
            if ($query['numberOfJourneys'] > 100) {
                throw new \InvalidArgumentException('Maximum 100 journeys allowed per search');
            }
        }

        // Validate station codes
        if (isset($query['origin']) && strlen($query['origin']) !== 3) {
            throw new \InvalidArgumentException('Origin must be exactly 3 characters');
        }

        if (isset($query['destination']) && strlen($query['destination']) !== 3) {
            throw new \InvalidArgumentException('Destination must be exactly 3 characters');
        }

        // Validate dates if provided
        if (isset($query['beginDate']) && isset($query['endDate'])) {
            $begin = strtotime($query['beginDate']);
            $end = strtotime($query['endDate']);

            if ($end < $begin) {
                throw new \InvalidArgumentException('End date cannot be before begin date');
            }
        }
    }

    /**
     * Validate FLIFO status data
     */
    private function validateStatusData(array $data): void
    {
        // Validate tail number if provided
        if (isset($data['tailNumber']) && strlen($data['tailNumber']) > 10) {
            throw new \InvalidArgumentException('Tail number must be maximum 10 characters');
        }

        // Validate gate if provided
        if (isset($data['gate']) && strlen($data['gate']) > 5) {
            throw new \InvalidArgumentException('Gate must be maximum 5 characters');
        }

        // Note: Empty string clears value, null retains original
        // This is a business rule, not a validation error
    }

    /**
     * Validate close leg data
     */
    private function validateCloseData(array $data): void
    {
        // Close leg has specific requirements that are validated server-side:
        // - PNRGOV messages sent
        // - APIS messages sent  
        // - Balance checks passed
        // - Exit row requirements met

        // Validate override flags if provided
        if (isset($data['overrideCheckin']) && !is_bool($data['overrideCheckin'])) {
            throw new \InvalidArgumentException('overrideCheckin must be a boolean');
        }

        if (isset($data['overrideBalance']) && !is_bool($data['overrideBalance'])) {
            throw new \InvalidArgumentException('overrideBalance must be a boolean');
        }
    }

    /**
     * Validate open leg data
     */
    private function validateOpenData(array $data): void
    {
        // Validate override flags if provided
        if (isset($data['overrideCheckin']) && !is_bool($data['overrideCheckin'])) {
            throw new \InvalidArgumentException('overrideCheckin must be a boolean');
        }

        // Open leg operation has checkin restrictions that can be overridden
    }

    /**
     * Validate mishap restore data
     */
    private function validateMishapRestoreData(array $data): void
    {
        // Validate market information if provided
        if (isset($data['marketInformation'])) {
            if (!is_array($data['marketInformation'])) {
                throw new \InvalidArgumentException('Market information must be an array');
            }

            // Validate required market info fields
            if (
                isset($data['marketInformation']['departureStation']) &&
                strlen($data['marketInformation']['departureStation']) !== 3
            ) {
                throw new \InvalidArgumentException('Departure station must be exactly 3 characters');
            }

            if (
                isset($data['marketInformation']['arrivalStation']) &&
                strlen($data['marketInformation']['arrivalStation']) !== 3
            ) {
                throw new \InvalidArgumentException('Arrival station must be exactly 3 characters');
            }
        }
    }

    /**
     * Validate self-service move parameters
     */
    private function validateSelfServiceParams(array $params): void
    {
        // Self-service has defaults:
        // BeginDate = journey departure
        // EndDate = BeginDate + 1

        // Validate journey key if provided
        if (isset($params['journeyKey']) && empty($params['journeyKey'])) {
            throw new \InvalidArgumentException('Journey key cannot be empty');
        }

        // Validate dates if explicitly provided
        if (isset($params['beginDate']) && isset($params['endDate'])) {
            $begin = strtotime($params['beginDate']);
            $end = strtotime($params['endDate']);

            if ($end < $begin) {
                throw new \InvalidArgumentException('End date cannot be before begin date');
            }
        }
    }

    /**
     * Validate IROP move request
     */
    private function validateIropRequest(array $data): void
    {
        // IROP move requires agent permissions (validated server-side)

        // Validate journey key
        if (!isset($data['journeyKey']) || empty($data['journeyKey'])) {
            throw new \InvalidArgumentException('Journey key is required for IROP move');
        }

        // Validate new journey key
        if (!isset($data['newJourneyKey']) || empty($data['newJourneyKey'])) {
            throw new \InvalidArgumentException('New journey key is required for IROP move');
        }

        // Validate passenger keys if provided
        if (isset($data['passengerKeys'])) {
            if (!is_array($data['passengerKeys'])) {
                throw new \InvalidArgumentException('Passenger keys must be an array');
            }
            if (empty($data['passengerKeys'])) {
                throw new \InvalidArgumentException('Passenger keys array cannot be empty');
            }
        }

        // Validate IROP specific fields
        if (isset($data['reason']) && strlen($data['reason']) > 500) {
            throw new \InvalidArgumentException('Reason must be maximum 500 characters');
        }
    }

    /**
     * Validate move availability request
     */
    private function validateMoveAvailabilityRequest(array $data): void
    {
        // Validate journey key
        if (!isset($data['journeyKey']) || empty($data['journeyKey'])) {
            throw new \InvalidArgumentException('Journey key is required');
        }

        // Validate dates
        if (isset($data['beginDate']) && isset($data['endDate'])) {
            $begin = strtotime($data['beginDate']);
            $end = strtotime($data['endDate']);

            if ($end < $begin) {
                throw new \InvalidArgumentException('End date cannot be before begin date');
            }
        }

        // Validate passenger move type if provided
        if (isset($data['passengerMoveType']) && !in_array($data['passengerMoveType'], [0, 1, 2, 4, 5, 6, 7, 8])) {
            throw new \InvalidArgumentException(
                'Invalid passenger move type. Must be 0-8 (None, Irop, Diversion, FlightClose, FlyAhead, SplitJourney, SelfServiceRebooking, HIDR)'
            );
        }
    }

    private function validatePassengers(array $passengers): void
    {
        if (empty($passengers)) {
            throw new \InvalidArgumentException('Passengers array cannot be empty');
        }
    }

    private function validateTripInfoQuery(array $query): void
    {
        // Max 100 journeys validation
        if (isset($query['numberOfJourneys']) && $query['numberOfJourneys'] > 100) {
            throw new \InvalidArgumentException('Maximum 100 journeys allowed per search');
        }
    }

    private function validateRebookRequest(array $data): void
    {
        // Validate rebook request
        if (isset($data['loyaltyFilter']) && !in_array($data['loyaltyFilter'], [0, 1, 2, 3])) {
            throw new \InvalidArgumentException(
                'Invalid loyalty filter. Must be 0-3 (MonetaryOnly, PointsOnly, PointsAndMonetary, PreserveCurrent)'
            );
        }
    }

    private function validateScheduleParams(array $params): void
    {
        // Validate origin/destination are 3 characters
        if (isset($params['origin']) && strlen($params['origin']) !== 3) {
            throw new \InvalidArgumentException('Origin must be exactly 3 characters');
        }

        if (isset($params['destination']) && strlen($params['destination']) !== 3) {
            throw new \InvalidArgumentException('Destination must be exactly 3 characters');
        }

        // Validate flight type enum if provided
        if (isset($params['flightType']) && !in_array($params['flightType'], [0, 1, 2, 3, 4, 5])) {
            throw new \InvalidArgumentException(
                'Invalid flight type. Must be 0-5 (None, NonStop, Through, Direct, Connect, All)'
            );
        }
    }

    private function validateAdHocData(array $data): void
    {
        // Validate ad hoc flight data
        if (!isset($data['departureStation']) || !isset($data['arrivalStation'])) {
            throw new \InvalidArgumentException('Departure and arrival stations are required');
        }
    }



    private function validateMoveRequest(array $data): void
    {
        if (!isset($data['journeyKey']) || empty($data['journeyKey'])) {
            throw new \InvalidArgumentException('Journey key is required for move');
        }
    }

    private function validateJourneyKey(string $key): void
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Journey key cannot be empty');
        }
    }


    private function validateCommentData(array $data): void
    {
        if (!isset($data['text']) || empty(trim($data['text']))) {
            throw new \InvalidArgumentException('Comment text is required');
        }
    }

    private function validateCommentKey(string $key): void
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Comment key cannot be empty');
        }
    }

    private function validateDelayData(array $data): void
    {
        if (!isset($data['delayCode']) || empty(trim($data['delayCode']))) {
            throw new \InvalidArgumentException('Delay code is required');
        }

        if (!isset($data['minutes']) || !is_numeric($data['minutes'])) {
            throw new \InvalidArgumentException('Delay minutes must be a numeric value');
        }
    }

    private function validateDelayKey(string $key): void
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Delay key cannot be empty');
        }
    }

    private function validateLegSimpleParams(array $params): void
    {
        if (isset($params['identifier']) && strlen($params['identifier']) > 4) {
            throw new \InvalidArgumentException('Flight identifier must be maximum 4 characters');
        }

        if (isset($params['origin']) && strlen($params['origin']) !== 3) {
            throw new \InvalidArgumentException('Origin station code must be exactly 3 characters');
        }

        if (isset($params['destination']) && strlen($params['destination']) !== 3) {
            throw new \InvalidArgumentException('Destination station code must be exactly 3 characters');
        }
    }

    private function validateLegKey(string $legKey): void
    {
        if (empty($legKey)) {
            throw new \InvalidArgumentException('Leg key cannot be empty');
        }
    }
}