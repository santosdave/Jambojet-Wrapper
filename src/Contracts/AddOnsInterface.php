<?php

namespace SantosDave\JamboJet\Contracts;

interface AddOnsInterface
{
    public function preserveSession(bool $preserve = true): self;
    /**
     * Add activity to booking
     * POST /api/nsk/v#/addOns/activities
     */
    public function addActivity(array $activityData): array;

    /**
     * Add insurance to booking
     * POST /api/nsk/v#/addOns/insurance
     */
    public function addInsurance(array $insuranceData): array;

    /**
     * Add lounge access
     * POST /api/nsk/v#/addOns/loungeAccess
     */
    public function addLoungeAccess(array $loungeData): array;

    /**
     * Add merchandise
     * POST /api/nsk/v#/addOns/merchandise
     */
    public function addMerchandise(array $merchandiseData): array;

    /**
     * Add pet transport
     * POST /api/nsk/v#/addOns/petTransport
     */
    public function addPetTransport(array $petData): array;

    /**
     * Add seat assignment
     * POST /api/nsk/v#/addOns/seats
     */
    public function addSeatAssignment(array $seatData): array;

    /**
     * Add service charges
     * POST /api/nsk/v#/addOns/serviceCharges
     */
    public function addServiceCharges(array $chargeData): array;

    /**
     * Add special service requests (SSR)
     * POST /api/nsk/v#/addOns/specialServiceRequests
     */
    public function addSpecialServiceRequest(array $ssrData): array;

    /**
     * Add baggage
     * POST /api/nsk/v#/addOns/bags
     */
    public function addBaggage(array $baggageData): array;
}
