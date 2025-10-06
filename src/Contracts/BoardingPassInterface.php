<?php

namespace SantosDave\JamboJet\Contracts;

interface BoardingPassInterface
{
    /**
     * Get boarding passes for booking in state
     * GET /api/nsk/v3/booking/boardingpasses
     */
    public function getBoardingPasses(array $criteria = []): array;

    /**
     * Get boarding passes by segment
     * GET /api/nsk/v3/booking/boardingpasses/segment/{segmentKey}
     */
    public function getBoardingPassesBySegment(string $segmentKey, array $options = []): array;

    /**
     * Get boarding pass by passenger
     * GET /api/nsk/v3/booking/boardingpasses/passenger/{passengerKey}
     */
    public function getBoardingPassesByPassenger(string $passengerKey, array $options = []): array;

    /**
     * Get boarding pass for specific passenger on specific segment
     * GET /api/nsk/v3/booking/boardingpasses/segment/{segmentKey}/passenger/{passengerKey}
     */
    public function getBoardingPassForPassengerSegment(string $segmentKey, string $passengerKey, array $options = []): array;

    /**
     * Generate boarding pass barcode
     * POST /api/nsk/v3/booking/boardingpasses/barcode
     */
    public function generateBarcode(array $barcodeData): array;

    /**
     * Validate boarding pass eligibility
     * POST /api/nsk/v3/booking/boardingpasses/validate
     */
    public function validateEligibility(array $validationCriteria): array;
}
